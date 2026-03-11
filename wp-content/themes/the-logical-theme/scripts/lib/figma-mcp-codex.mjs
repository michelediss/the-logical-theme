import path from "node:path";
import { spawn } from "node:child_process";
import readline from "node:readline";

function extractFileKey(figmaUrl) {
  const match = String(figmaUrl).match(/figma\.com\/make\/([^/?#]+)/i);
  if (!match) {
    throw new Error(`Unsupported Figma Make URL: ${figmaUrl}`);
  }

  return match[1];
}

function extractSourceRelativePath(uri, fileKey) {
  const marker = `/source/${fileKey}/`;
  const index = uri.indexOf(marker);
  if (index === -1) {
    return null;
  }

  return uri.slice(index + marker.length);
}

function extractImageRelativePath(uri, fileKey) {
  const marker = `/image/${fileKey}/`;
  const index = uri.indexOf(marker);
  if (index === -1) {
    return null;
  }

  return path.join("images", uri.slice(index + marker.length));
}

async function runCodexUntilToolResult(prompt, matcher) {
  return new Promise((resolve, reject) => {
    const child = spawn(
      "codex",
      ["exec", "--skip-git-repo-check", "--sandbox", "read-only", "--json", prompt],
      {
        env: process.env,
        stdio: ["ignore", "pipe", "pipe"],
      },
    );

    const stderrChunks = [];
    const stdoutReader = readline.createInterface({ input: child.stdout });
    const stderrReader = readline.createInterface({ input: child.stderr });
    let settled = false;

    function finish(error, result) {
      if (settled) {
        return;
      }

      settled = true;
      stdoutReader.close();
      stderrReader.close();
      child.kill("SIGTERM");

      if (error) {
        reject(error);
        return;
      }

      resolve(result);
    }

    stdoutReader.on("line", (line) => {
      let parsed;
      try {
        parsed = JSON.parse(line);
      } catch {
        return;
      }

      try {
        const matched = matcher(parsed);
        if (matched) {
          finish(null, matched);
        }
      } catch (error) {
        finish(error);
      }
    });

    stderrReader.on("line", (line) => {
      stderrChunks.push(line);
    });

    child.on("error", (error) => {
      finish(error);
    });

    child.on("close", (code) => {
      if (settled) {
        return;
      }

      const stderr = stderrChunks.join("\n").trim();
      finish(
        new Error(
          `Codex MCP wrapper exited before returning the expected tool result${code !== null ? ` (code ${code})` : ""}${stderr ? `: ${stderr}` : ""}`,
        ),
      );
    });
  });
}

function parseWrappedResourcePayload(result, uri) {
  const resource = result?.content?.[0];
  if (!resource) {
    throw new Error(`Figma MCP returned no content for ${uri}`);
  }

  if (typeof resource.text === "string") {
    try {
      const wrapped = JSON.parse(resource.text);
      const inner = wrapped?.contents?.[0];
      if (inner?.blob && typeof inner.blob === "string") {
        return {
          status: "ok",
          uri,
          mimeType: inner.mimeType || "application/octet-stream",
          encoding: "base64",
          content: inner.blob,
        };
      }

      if (typeof inner?.text === "string") {
        return {
          status: "ok",
          uri,
          mimeType: inner.mimeType || "text/plain",
          encoding: "text",
          content: inner.text,
        };
      }
    } catch {
      return {
        status: "ok",
        uri,
        mimeType: resource.mimeType || "text/plain",
        encoding: "text",
        content: resource.text,
      };
    }
  }

  if (typeof resource.blob === "string") {
    return {
      status: "ok",
      uri,
      mimeType: resource.mimeType || "application/octet-stream",
      encoding: "base64",
      content: resource.blob,
    };
  }

  return {
    status: "ok",
    uri,
    mimeType: resource.mimeType || "application/octet-stream",
    encoding: "none",
    content: "",
  };
}

export async function discoverMakeResources(page) {
  if (!page.figmaMcpUrl) {
    throw new Error(`Missing figma_mcp_url for ${page.pageId}`);
  }

  const fileKey = extractFileKey(page.figmaMcpUrl);
  const prompt = [
    "Use the configured Figma MCP server.",
    "Call figma.get_design_context for the Figma Make root.",
    "Use fileKey exactly as provided and nodeId 0:1.",
    "Do not run shell commands.",
    "Do not read individual source resources after the design context call.",
    "Stop after the first successful get_design_context result.",
    `fileKey: ${fileKey}`,
    "nodeId: 0:1",
    `Make URL: ${page.figmaMcpUrl}`,
  ].join(" ");

  const result = await runCodexUntilToolResult(prompt, (event) => {
    const item = event?.item;
    if (
      event?.type === "item.completed" &&
      item?.type === "mcp_tool_call" &&
      item?.server === "figma" &&
      item?.tool === "get_design_context"
    ) {
      return item.result;
    }

    return null;
  });

  const resources = [];
  for (const content of result?.content ?? []) {
    if (content?.type !== "resource_link" || typeof content.uri !== "string") {
      continue;
    }

    let kind = null;
    let relativePath = null;
    if (content.uri.includes(`/source/${fileKey}/`)) {
      kind = "source";
      relativePath = extractSourceRelativePath(content.uri, fileKey);
    } else if (content.uri.includes(`/image/${fileKey}/`)) {
      kind = "image";
      relativePath = extractImageRelativePath(content.uri, fileKey);
    } else if (content.mimeType?.startsWith("text/")) {
      kind = "doc";
      relativePath = content.name || path.basename(content.uri);
    }

    if (!kind || !relativePath) {
      continue;
    }

    resources.push({
      uri: content.uri,
      kind,
      relativePath,
      mimeType: content.mimeType || "application/octet-stream",
    });
  }

  return {
    fileKey,
    notes: [],
    resources,
  };
}

export async function fetchMakeResource(uri) {
  const results = await fetchMakeResources([uri]);
  return results.get(uri);
}

export async function fetchMakeResources(uris, options = {}) {
  const log = typeof options.log === "function" ? options.log : () => {};
  const uniqueUris = [...new Set(uris)];
  log(`Fetching ${uniqueUris.length} MCP resource(s)`);
  const prompt = [
    "Use the configured Figma MCP server.",
    "Read exactly these Figma MCP resource URIs with figma.read_mcp_resource.",
    "Do not perform any other tool calls.",
    "Do not run shell commands.",
    "Read every listed URI exactly once.",
    "URIs:",
    ...uniqueUris.map((uri) => `- ${uri}`),
  ].join("\n");

  const pending = new Set(uniqueUris);
  const collected = new Map();

  await runCodexUntilToolResult(prompt, (event) => {
    const item = event?.item;
    if (
      event?.type === "item.completed" &&
      item?.type === "mcp_tool_call" &&
      item?.server === "figma" &&
      item?.tool === "read_mcp_resource"
    ) {
      const currentUri = item.arguments?.uri;
      if (pending.has(currentUri)) {
        collected.set(currentUri, parseWrappedResourcePayload(item.result, currentUri));
        pending.delete(currentUri);
        log(`Fetched MCP resource ${collected.size}/${uniqueUris.length}: ${currentUri}`);
      }

      if (pending.size === 0) {
        return collected;
      }
    }

    return null;
  });

  return collected;
}

export async function fetchMakeResourceLegacy(uri) {
  const prompt = [
    "Use the configured Figma MCP server.",
    "Read exactly one Figma MCP resource URI with figma.read_mcp_resource.",
    "Do not perform any other tool calls.",
    "Do not run shell commands.",
    `URI: ${uri}`,
  ].join(" ");

  const result = await runCodexUntilToolResult(prompt, (event) => {
    const item = event?.item;
    if (
      event?.type === "item.completed" &&
      item?.type === "mcp_tool_call" &&
      item?.server === "figma" &&
      item?.tool === "read_mcp_resource"
    ) {
      return item.result;
    }

    return null;
  });
  return parseWrappedResourcePayload(result, uri);
}
