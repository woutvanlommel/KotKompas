import type { Config } from 'tailwindcss';

export default {
  content: ['./src/**/*.{html,ts}'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"area-normal"', 'ui-sans-serif', 'system-ui', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'],
        'area-light': ['"area-normal-light"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        // Accent: Pumpkin Spice (#FF6700)
        // Gebruik: Call-to-actions (knoppen), highlights, meldingen
        accent: {
          DEFAULT: '#ff6700',
          100: '#fff0e5',
          200: '#ffd1b3',
          300: '#ffb380',
          400: '#ff944d',
          500: '#ff6700',
          600: '#cc5200',
          700: '#993e00',
          800: '#662900',
          900: '#331500',
        },
        // Primary: Steel Azure (#004E98)
        // Gebruik: Navigatiebalk, headers, hoofdknoppen
        primary: {
          DEFAULT: '#004e98',
          100: '#ccdbea',
          200: '#99b8d6',
          300: '#6695c1',
          400: '#3371ad',
          500: '#004e98',
          600: '#003e7a',
          700: '#002f5b',
          800: '#001f3d',
          900: '#00101e',
        },
        // Secondary: Rich Cerulean (#3A6EA5)
        // Gebruik: Secundaire knoppen, iconen, subkoppen
        secondary: {
          DEFAULT: '#3a6ea5',
          100: '#d8e2ed',
          200: '#b0c5db',
          300: '#89a7ca',
          400: '#618bb7',
          500: '#3a6ea5',
          600: '#2e5884',
          700: '#234263',
          800: '#172c42',
          900: '#0c1621',
        },
        // Base-een: Platinum (#EBEBEB)
        // Gebruik: Pagina achtergronden, cards, grote vlakken
        'base-een': {
          DEFAULT: '#ebebeb',
          100: '#fbfbfb',
          200: '#f7f7f7',
          300: '#f3f3f3',
          400: '#efefef',
          500: '#ebebeb',
          600: '#bcbcbc',
          700: '#8d8d8d',
          800: '#5e5e5e',
          900: '#2f2f2f',
        },
        // Base-twee: Silver (#C0C0C0)
        // Gebruik: Borders (randen), dividers, inactieve elementen
        'base-twee': {
          DEFAULT: '#c0c0c0',
          100: '#f2f2f2',
          200: '#e6e6e6',
          300: '#d9d9d9',
          400: '#cccccc',
          500: '#c0c0c0',
          600: '#9a9a9a',
          700: '#737373',
          800: '#4d4d4d',
          900: '#262626',
        },
      },
    },
  },
  plugins: [],
} satisfies Config;
