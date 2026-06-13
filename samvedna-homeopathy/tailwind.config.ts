import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./app/**/*.{ts,tsx}",
    "./components/**/*.{ts,tsx}",
    "./constants/**/*.{ts,tsx}",
    "./lib/**/*.{ts,tsx}"
  ],
  theme: {
    extend: {
      colors: {
        primary: "var(--color-primary)",
        "primary-light": "var(--color-primary-light)",
        "primary-dark": "var(--color-primary-dark)",
        secondary: "var(--color-secondary)",
        bg: "var(--color-bg)",
        "bg-soft": "var(--color-bg-soft)",
        accent: "var(--color-accent)",
        text: "var(--color-text)",
        "text-light": "var(--color-text-light)",
        muted: "var(--color-muted)",
        border: "var(--color-border)",
        surface: "var(--color-surface)",
        success: "var(--color-success)"
      },
      fontFamily: {
        display: ["var(--font-inter-tight)", "sans-serif"],
        body: ["var(--font-inter)", "sans-serif"]
      },
      maxWidth: {
        container: "1440px",
        content: "1280px"
      },
      borderRadius: {
        card: "8px",
        control: "6px"
      },
      boxShadow: {
        premium: "0 24px 80px rgba(17, 24, 39, 0.08)",
        glass: "0 18px 45px rgba(12, 79, 47, 0.10)"
      }
    }
  }
};

export default config;
