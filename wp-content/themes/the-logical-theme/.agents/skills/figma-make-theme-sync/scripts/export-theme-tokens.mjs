#!/usr/bin/env node

import fs from 'node:fs';
import path from 'node:path';
import { getThemeRoot } from './visual-qa-common.mjs';

function readJson(filePath) {
  return JSON.parse(fs.readFileSync(filePath, 'utf8'));
}

const themeRoot = getThemeRoot();
const themeJsonPath = path.join(themeRoot, 'theme.json');
const themeJson = readJson(themeJsonPath);
const settings = themeJson.settings ?? {};
const styles = themeJson.styles ?? {};

const payload = {
  source: 'theme.json',
  theme_json_path: themeJsonPath,
  settings: {
    color: settings.color ?? {},
    typography: settings.typography ?? {},
    spacing: settings.spacing ?? {},
    layout: settings.layout ?? {},
    custom: settings.custom ?? {},
    blocks: settings.blocks ?? {},
  },
  styles: {
    color: styles.color ?? {},
    typography: styles.typography ?? {},
    elements: styles.elements ?? {},
    blocks: styles.blocks ?? {},
  },
  template_parts: Array.isArray(themeJson.templateParts) ? themeJson.templateParts : [],
};

process.stdout.write(`${JSON.stringify(payload, null, 2)}\n`);
