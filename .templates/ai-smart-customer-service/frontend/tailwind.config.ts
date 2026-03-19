import type { Config } from "tailwindcss";

export default {
  darkMode: "class",
  content: ["./src/**/*.{ts,tsx}"],
  plugins: [require("tailwindcss-animate")],
} satisfies Config;
