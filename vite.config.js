import { fileURLToPath, URL } from 'node:url'
import path from "path"

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from "@tailwindcss/vite"

// Récupère l'argument --entry
const customEntry = process.env.ENTRY

// Définit une map des entrées possibles
const entries = {
  app: path.resolve(__dirname, 'src/main.js'),
}

// Si un ENTRY est passé → build seulement celle-là
// Sinon → build la première par défaut
const input = customEntry ? { [customEntry]: entries[customEntry] } : { login: entries.login }

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss()
  ],
  define: {
    'process.env': {}
  },
  build: {
    manifest: true,
    outDir: 'build/dist',
    emptyOutDir: true,
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