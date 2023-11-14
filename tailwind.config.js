/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      transitionProperty: {
        'top-menu': 'margin-left',
        'left-menu': 'left',
      },
      transitionDuration: {
        '2s': '2s',
      },
      transitionTimingFunction: {
        'ease': 'ease',
      },
    },
  },
  plugins: [
    function ({ addUtilities }) {
      const newUtilities = {
        '.show-menu': {
          'left': '0 !important',
        },
      };
      addUtilities(newUtilities, ['responsive', 'hover']);
    },
  ],}
