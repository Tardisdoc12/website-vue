import { fileURLToPath, URL } from 'node:url'
import path from "path"

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import tailwindcss from "@tailwindcss/vite"

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
    tailwindcss()
  ],
  build: {
    rollupOptions: {
        input: {
            app: path.resolve(__dirname,"index.html"),
            login: path.resolve(__dirname, 'src/main-login.js'),
            calendar: path.resolve(__dirname, 'src/main-calendar.js'),
            events: path.resolve(__dirname, 'src/main-events.js')
        }
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})
