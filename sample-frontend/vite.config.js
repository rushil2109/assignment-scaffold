import { defineConfig } from "vite";

export default defineConfig({
  server: {
    port: 5173,
    proxy: {
      "/public": "http://localhost:9001",
      "/mock": "http://localhost:9001",
      "/inspection": "http://localhost:9001",
    },
  },
});
