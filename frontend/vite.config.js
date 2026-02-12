import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    port: 3000,           // Изменили порт с 5173 на 3000
    host: true,           // Доступ с любого хоста (0.0.0.0)
    strictPort: false,    // Если 3000 занят, попробует 3001, 3002 и т.д.
  },
})
