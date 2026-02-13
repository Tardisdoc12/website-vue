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
  login: path.resolve(__dirname, 'src/main-login.js'),
  calendar: path.resolve(__dirname, 'src/main-calendar.js'),
  connexion: path.resolve(__dirname, 'src/main-connexion.js'),
  account: path.resolve(__dirname, 'src/main-global-account.js'),
  form_adhesion: path.resolve(__dirname, 'src/main-adherent.js'),
  test: path.resolve(__dirname, 'src/main-test.js'),
  "event-page" : path.resolve(__dirname, 'src/main-event-page.js'),
  reinitialisation: path.resolve(__dirname, 'src/main-password.js'),
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
    rollupOptions: {
      input,
      output: {
        manualChunks: undefined, // ⛔ empêche index.js
        format: 'iife', // 👈 crée une IIFE isolée
        inlineDynamicImports: true,
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
