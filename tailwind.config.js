const path = require('path');

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    path.resolve(__dirname, 'config_assets_manager/templates/**/*.php'),
    path.resolve(__dirname, 'public/**/*.php'),
    path.resolve(__dirname, 'config_assets_manager/modules/**/*.php'),
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}