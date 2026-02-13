import { fileURLToPath, URL } from 'node:url'
import path from "path"

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from "@tailwindcss/vite"

export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
    tailwindcss()
  ],

  build: {
    outDir: "build/dist",
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input : path.resolve(__dirname, "src/main.js"),
      output: {
        entryFileNames: `main.js`,
        chunkFileNames: `[name].[hash].js`,
        assetFileNames: `[name].[hash].[ext]`
      }
    }
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src')
    },
  },
})
