import { fileURLToPath, URL } from 'node:url'
import path from "path"

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from "@tailwindcss/vite"

export default defineConfig({
  css: {
    transformer: 'postcss',
  },
  plugins: [
    vue(),
    tailwindcss(),
  ],
  define: {
    'process.env': {},
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV ?? 'production'),
    global: 'globalThis'
  },
  build: {
    manifest: true,
    outDir: 'build/dist',
    emptyOutDir: true,
    cssMinify: 'esbuild',
    minify: 'esbuild',
    lib: {
      entry: path.resolve(__dirname, 'src/main.js'),
      name: 'VueLoginApp',
      fileName: () => 'app.[hash].js',
      formats: ['iife']
    },
    rollupOptions: {
      output: {
        inlineDynamicImports: true,
        assetFileNames: 'app.[hash].[ext]'
      }
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})