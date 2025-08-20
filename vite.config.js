import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'public/js',
    emptyOutDir: false,
    lib: {
      entry: 'public/js/admin-sidebar.js',
      fileName: () => 'admin-sidebar.bundle',
      formats: ['es']
    },
    rollupOptions: {
      external: [],
    }
  }
});
