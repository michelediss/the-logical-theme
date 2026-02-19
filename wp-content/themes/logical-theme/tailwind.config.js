import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const tokensPath = path.join(__dirname, 'src', 'generated', 'tailwind.tokens.json');
const usedBlocksPath = path.join(__dirname, 'config', 'used-blocks.json');

let tokens = {
  colors: {},
  screens: {},
  container: {
    center: true,
    padding: '1rem'
  },
  maxWidths: {}
};

let safelist = [];

if (fs.existsSync(tokensPath)) {
  try {
    const parsed = JSON.parse(fs.readFileSync(tokensPath, 'utf8'));
    if (parsed && typeof parsed === 'object') {
      tokens = {
        ...tokens,
        ...parsed
      };
    }
  } catch (error) {
    tokens = tokens;
  }
}

if (fs.existsSync(usedBlocksPath)) {
  try {
    const parsed = JSON.parse(fs.readFileSync(usedBlocksPath, 'utf8'));
    if (parsed && typeof parsed === 'object' && Array.isArray(parsed.safelist)) {
      safelist = parsed.safelist.filter((entry) => typeof entry === 'string' && entry.trim() !== '');
    }
  } catch (error) {
    safelist = [];
  }
}

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './*.php',
    './templates/**/*.php',
    './template-parts/**/*.php',
    './src/**/*.{js,jsx}'
  ],
  safelist,
  theme: {
    screens: tokens.screens,
    container: tokens.container,
    extend: {
      colors: tokens.colors,
      maxWidth: tokens.maxWidths
    }
  },
  plugins: []
};
