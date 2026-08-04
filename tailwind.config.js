import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                sb: {
                    bg: {
                        dark: '#0a0a0a',
                        light: '#f5f5f4',
                    },
                    accent: {
                        DEFAULT: '#34d399',
                        dark: '#10b981',
                        light: '#059669',
                        muted: 'rgba(52, 211, 153, 0.15)',
                    },
                    glass: {
                        dark: 'rgba(255, 255, 255, 0.04)',
                        light: 'rgba(255, 255, 255, 0.72)',
                    },
                    border: {
                        dark: 'rgba(255, 255, 255, 0.08)',
                        light: 'rgba(0, 0, 0, 0.06)',
                    },
                },
            },
            boxShadow: {
                'glow-sm': '0 0 12px rgba(52, 211, 153, 0.25)',
                'glow-md': '0 0 24px rgba(52, 211, 153, 0.35)',
                'glass-light': '0 4px 24px rgba(0, 0, 0, 0.06)',
            },
            animation: {
                'fade-in': 'fadeIn 0.35s ease-out forwards',
                'pulse-glow': 'pulseGlow 1.8s ease-in-out infinite',
                'typing-dot': 'typingDot 1.4s ease-in-out infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                pulseGlow: {
                    '0%, 100%': { opacity: '0.4', boxShadow: '0 0 8px rgba(52, 211, 153, 0.2)' },
                    '50%': { opacity: '1', boxShadow: '0 0 20px rgba(52, 211, 153, 0.45)' },
                },
                typingDot: {
                    '0%, 60%, 100%': { transform: 'translateY(0)', opacity: '0.35' },
                    '30%': { transform: 'translateY(-4px)', opacity: '1' },
                },
            },
            transitionDuration: {
                theme: '500ms',
            },
        },
    },

    plugins: [forms],
};
