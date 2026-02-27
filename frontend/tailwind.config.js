/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        // Primary — violet/purple
        primary: {
          50:  '#f5f3ff',
          100: '#ede9fe',
          200: '#ddd6fe',
          300: '#c4b5fd',
          400: '#a78bfa',
          500: '#8b5cf6',
          600: '#7c3aed',
          700: '#6d28d9',
          800: '#5b21b6',
          900: '#4c1d95',
          950: '#2e1065',
        },
        // Brand orange — kept as CTA accent
        brand: {
          DEFAULT: '#f97316',
          dark:    '#ea580c',
          light:   '#fb923c',
        },
        // Dark surface palette
        surface: {
          50:  '#f8fafc',
          100: '#f1f5f9',
          800: '#1e293b',
          900: '#0f172a',
          950: '#020617',
        },
      },
      fontFamily: {
        sans:    ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
        display: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
      },
      backgroundImage: {
        'mesh-purple': 'radial-gradient(at 0% 0%, #7c3aed 0, transparent 50%), radial-gradient(at 100% 0%, #4f46e5 0, transparent 50%), radial-gradient(at 50% 100%, #db2777 0, transparent 50%)',
        'mesh-dark':   'radial-gradient(at 0% 50%, #1e1b4b 0, transparent 50%), radial-gradient(at 100% 0%, #2e1065 0, transparent 50%), radial-gradient(at 50% 100%, #0f172a 0, transparent 60%)',
        'brand-gradient': 'linear-gradient(135deg, #8b5cf6 0%, #6d28d9 50%, #f97316 100%)',
      },
      boxShadow: {
        'glow-primary': '0 0 30px rgba(139, 92, 246, 0.25)',
        'glow-brand':   '0 0 30px rgba(249, 115, 22, 0.3)',
        'card-hover':   '0 20px 60px rgba(0,0,0,0.1)',
      },
      animation: {
        'float':       'float 6s ease-in-out infinite',
        'float-slow':  'float 9s ease-in-out infinite',
        'pulse-slow':  'pulse 4s ease-in-out infinite',
        'gradient':    'gradient 8s ease infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%':      { transform: 'translateY(-16px)' },
        },
        gradient: {
          '0%, 100%': { backgroundPosition: '0% 50%' },
          '50%':      { backgroundPosition: '100% 50%' },
        },
      },
    },
  },
  plugins: [],
}
