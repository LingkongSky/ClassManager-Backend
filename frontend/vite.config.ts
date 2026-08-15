import path, { resolve } from 'path'
import fs from 'fs'
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react-swc'
// import checker from 'vite-plugin-checker'
import legacy from '@vitejs/plugin-legacy'
const proxy_url: string = "http://127.0.0.1:8001";

export default defineConfig({
  build: {
    manifest: false,
    chunkSizeWarningLimit: 2000,
    minify: 'terser',
    sourcemap: false,
    rollupOptions: {
      input: {
        index: resolve(__dirname, 'index.html'),
        login: resolve(__dirname, 'login.html'),
        redirect: resolve(__dirname, 'redirect.html'),
      },
      output: {
        chunkFileNames: 'static/js/[name]-[hash].js',
        entryFileNames: 'static/js/[name]-[hash].js',
        assetFileNames: 'static/assest/[name]-[hash].[ext]',
        manualChunks: {
        //  lodash: ['lodash'],
          antd: ['antd'],
          exceljs: ['exceljs'],
          },
      },
        },
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
      }
    },
  },

  plugins: [
    react(),
    deleteMediaDir(),
    legacy({
      targets: ['defaults', 'not IE 11'],
    }),
    //checker({ typescript: true }),
    // visualizer({ open: false })
  ],
  server: {
    host: '127.0.0.1',
    proxy: {
      '/api': {
        target: proxy_url, 
        changeOrigin: true,
      },
      '/Docs': {
        target: proxy_url,
        changeOrigin: true,
      },
      '/storage': {
        target: proxy_url,
        changeOrigin: true,
      },
    },
  },
  publicDir: "public",
});


function deleteMediaDir() {
  return {
    name: 'delete-media-dir',
    writeBundle() {
      const mediaDir = path.resolve(__dirname, 'dist', 'files');
      if (fs.existsSync(mediaDir)) {
        fs.rmSync(mediaDir, { recursive: true });
      }
    }
  };
}
