import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig(({ mode }) => ({
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    base: mode === 'production'
        ? process.env.VITE_APP_URL + '/build/'   
        : '/',
    resolve:{
    alias:{
      '@':fileURLToPath(new URL("resources/js",import.meta.url))
    }
       
    
  },



}));
