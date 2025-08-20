import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'public/js',
    emptyOutDir: false,
    lib: {
      entry: 'public/js/admin-sidebar.js',
      name: 'AdminSidebar',
      fileName: () => 'admin-sidebar.bundle.js',
      formats: ['iife']
    },
    rollupOptions: {
      external: [],
    }
  }
});
