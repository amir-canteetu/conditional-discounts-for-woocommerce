import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import path from "path";

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: path.resolve(__dirname, "../build"),
    emptyOutDir: true,
    rollupOptions: {
      external: ["react", "react-dom"],
      output: {
        entryFileNames: "[name].js",
        assetFileNames: "[name].[ext]",
      },
    },
  },
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "../assets/admin/js"),
    },
  },
  server: {
    proxy: {
      "/wp-admin/admin-ajax.php": {
        target: "http://localhost",
        changeOrigin: true,
      },
    },
  },
});
