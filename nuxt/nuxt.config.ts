export default defineNuxtConfig({
  ssr: false,

  modules: ["@nuxt/ui"],

  css: ["~/assets/css/main.css"],

  devtools: { enabled: true },

  app: {
    baseURL: "/ffflags/admin/",
    head: {
      title: "FFFlags Admin",
    },
  },

  nitro: {
    output: {
      publicDir: "../dist",
    },
    devProxy: {
      "/ffflags-api": {
        target: "http://localhost:8000/ffflags-api",
        changeOrigin: true,
      },
    },
  },

  compatibilityDate: "2025-03-10",
});
