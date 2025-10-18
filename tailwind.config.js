/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.{html,php,js}",
    "./src/**/*.{html,php,js}",
    "./assets/**/*.{html,php,js}"
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
        },
        secondary: {
          50: '#f8fafc',
          100: '#f1f5f9',
          500: '#64748b',
          600: '#475569',
        }
      },
      animation: {
        'swirl': 'swirl 20s ease-in-out infinite',
        'gradient': 'gradient 3s ease infinite',
      },
      keyframes: {
        swirl: {
          '0%': { backgroundPosition: '0% 0%' },
          '25%': { backgroundPosition: '25% 100%' },
          '50%': { backgroundPosition: '100% 100%' },
          '75%': { backgroundPosition: '80% 0%' },
          '100%': { backgroundPosition: '0% 0%' },
        },
        gradient: {
          '0%, 100%': { backgroundPosition: '0% 50%' },
          '50%': { backgroundPosition: '100% 50%' },
        }
      }
    },
  },
  plugins: [],
}
