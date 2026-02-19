import fs from 'node:fs';
import path from 'node:path';

const themeDir = process.cwd();
const hyperuiDir = path.join(themeDir, 'hyperui', 'hyperui-main');
const componentsDir = path.join(hyperuiDir, 'public', 'components');
const collectionContentDir = path.join(hyperuiDir, 'src', 'content', 'collection');
const outputPath = path.join(themeDir, 'config', 'hyperui-manifest.json');

const INCLUDE_COLLECTIONS = ['marketing'];

function ensureDir(dirPath) {
  fs.mkdirSync(dirPath, { recursive: true });
}

function readFile(filePath) {
  return fs.readFileSync(filePath, 'utf8');
}

function listDirs(dirPath) {
  if (!fs.existsSync(dirPath)) return [];
  return fs.readdirSync(dirPath, { withFileTypes: true })
    .filter((entry) => entry.isDirectory())
    .map((entry) => entry.name);
}

function listHtmlVariants(slugDir) {
  if (!fs.existsSync(slugDir)) return [];

  return fs.readdirSync(slugDir)
    .filter((fileName) => fileName.endsWith('.html'))
    .filter((fileName) => !fileName.includes('-dark.'))
    .map((fileName) => ({
      variant: fileName.replace('.html', ''),
      fileName
    }))
    .sort((a, b) => {
      const an = Number.parseInt(a.variant, 10);
      const bn = Number.parseInt(b.variant, 10);
      if (!Number.isNaN(an) && !Number.isNaN(bn)) {
        return an - bn;
      }
      return a.variant.localeCompare(b.variant);
    });
}

function parseMdxFrontmatter(mdxPath) {
  if (!fs.existsSync(mdxPath)) return null;
  const raw = readFile(mdxPath);
  const match = raw.match(/^---\n([\s\S]*?)\n---/);
  if (!match) return null;

  const body = match[1];
  const titleMatch = body.match(/\ntitle:\s*(.+)\n/) || body.match(/^title:\s*(.+)\n/);
  const descriptionMatch = body.match(/\ndescription:\s*(.+)\n/) || body.match(/^description:\s*(.+)\n/);

  return {
    title: titleMatch ? titleMatch[1].trim().replace(/^['"]|['"]$/g, '') : '',
    description: descriptionMatch ? descriptionMatch[1].trim().replace(/^['"]|['"]$/g, '') : ''
  };
}

function buildManifest() {
  const manifest = {
    version: 1,
    generatedAt: new Date().toISOString(),
    collections: {}
  };

  for (const collection of INCLUDE_COLLECTIONS) {
    const collectionDir = path.join(componentsDir, collection);
    const slugs = listDirs(collectionDir);
    const collectionData = {};

    for (const slug of slugs) {
      const slugDir = path.join(collectionDir, slug);
      const variants = listHtmlVariants(slugDir);
      if (variants.length === 0) continue;

      const mdxPath = path.join(collectionContentDir, collection, `${slug}.mdx`);
      const frontmatter = parseMdxFrontmatter(mdxPath);

      const variantMap = {};
      for (const variant of variants) {
        variantMap[variant.variant] = {
          path: path.relative(themeDir, path.join(slugDir, variant.fileName)).replaceAll(path.sep, '/')
        };
      }

      collectionData[slug] = {
        title: frontmatter?.title || slug,
        description: frontmatter?.description || '',
        variants: variantMap
      };
    }

    manifest.collections[collection] = collectionData;
  }

  return manifest;
}

ensureDir(path.dirname(outputPath));
const manifest = buildManifest();
fs.writeFileSync(outputPath, `${JSON.stringify(manifest, null, 2)}\n`, 'utf8');
console.log(`Manifest written: ${outputPath}`);
