/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        cyber: {
          pink: '#ff2a6d',
          blue: '#05d9e8',
          purple: '#9d4edd',
          yellow: '#d1f7ff',
          dark: '#0d0221',
          darker: '#05010d',
        }
      },
      fontFamily: {
        cyber: ['Orbitron', 'sans-serif'],
      },
      boxShadow: {
        'neon-pink': '0 0 5px #ff2a6d, 0 0 20px #ff2a6d, 0 0 40px #ff2a6d',
        'neon-blue': '0 0 5px #05d9e8, 0 0 20px #05d9e8, 0 0 40px #05d9e8',
        'neon-purple': '0 0 5px #9d4edd, 0 0 20px #9d4edd',
      },
      animation: {
        'pulse-neon': 'pulse-neon 2s ease-in-out infinite',
        'glitch': 'glitch 1s linear infinite',
        'scanline': 'scanline 6s linear infinite',
        'flicker': 'flicker 0.15s infinite',
      },
      keyframes: {
        'pulse-neon': {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0.5' },
        },
        'glitch': {
          '0%': { transform: 'translate(0)' },
          '20%': { transform: 'translate(-2px, 2px)' },
          '40%': { transform: 'translate(-2px, -2px)' },
          '60%': { transform: 'translate(2px, 2px)' },
          '80%': { transform: 'translate(2px, -2px)' },
          '100%': { transform: 'translate(0)' },
        },
        'scanline': {
          '0%': { transform: 'translateY(-100%)' },
          '100%': { transform: 'translateY(100%)' },
        },
        'flicker': {
          '0%, 19.999%, 22%, 62.999%, 64%, 64.999%, 70%, 100%': { opacity: '1' },
          '20%, 21.999%, 63%, 63.999%, 65%, 69.999%': { opacity: '0.4' },
        },
      },
      backgroundImage: {
        'cyber-grid': 'linear-gradient(rgba(5, 217, 232, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(5, 217, 232, 0.1) 1px, transparent 1px)',
      },
    },
  },
  plugins: [],
}
