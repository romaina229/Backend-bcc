// tailwind.config.js
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'bcc-primary': '#1e40af',
        'bcc-secondary': '#0f766e',
        'bcc-accent': '#f59e0b',
      },
    },
  },
  plugins: [],
}
