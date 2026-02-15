import { fileURLToPath, URL } from 'node:url'
import path from "path"

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from "@tailwindcss/vite"

// Récupère l'argument --entry
const customEntry = process.env.ENTRY

// Définit une map des entrées possibles
const entries = {
  app: path.resolve(__dirname, 'src/main-login.js'),
}

// Si un ENTRY est passé → build seulement celle-là
// Sinon → build la première par défaut
const input = customEntry ? { [customEntry]: entries[customEntry] } : { login: entries.login }

export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
    tailwindcss()
  ],
  build: {
    manifests: true,
    outDir: 'build/dist',
    rollupOptions: {
      input,
      output: {
        // manualChunks: undefined, // ⛔ empêche index.js
        // format: 'iife', // 👈 crée une IIFE isolée
        // inlineDynamicImports: true,
        entryFileNames: `[name].js`,
        chunkFileNames: `[name].js`,
        assetFileNames: `[name].[ext]`
      }
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})