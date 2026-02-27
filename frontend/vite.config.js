import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    port: 3001,
    proxy: {
      // All /api requests are forwarded server-side to gift.test — no browser CORS
      '/api': {
        target: 'http://gift.test',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
