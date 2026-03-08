/*
 * Vite build configuration for the theme asset pipeline. It outputs the manifest and bundled
 * front-end files consumed by the WordPress enqueue layer.
 */

import { defineConfig } from 'vite';
import path from 'node:path';

export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    origin: 'http://localhost:5173',
  },
  build: {
    manifest: true,
    outDir: 'assets',
    emptyOutDir: false,
    rollupOptions: {
      input: {
        app: path.resolve(__dirname, 'src/js/app.js'),
        blockEditor: path.resolve(__dirname, 'src/js/blocks/editor.js'),
        blockView: path.resolve(__dirname, 'src/js/blocks/view.js'),
        blocksStyle: path.resolve(__dirname, 'src/css/blocks.css'),
        editorSeo: path.resolve(__dirname, 'src/js/editor-seo.js'),
        style: path.resolve(__dirname, 'src/css/app.css'),
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: ({ name }) => {
          if (name && name.endsWith('.css')) {
            return 'css/[name][extname]';
          }

          return 'assets/[name][extname]';
        },
      },
    },
  },
});
