import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans:    ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Poppins', ...defaultTheme.fontFamily.sans],
                stats:   ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50:  '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    DEFAULT: '#1E40AF',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
                accent: {
                    light:   '#F5E6B2',
                    DEFAULT: '#D4AF37',
                    dark:    '#A88A20',
                },
                surface: '#F8F9FA',
                charcoal: '#1F2937',
                muted: '#6B7280',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.25rem',
                '4xl': '1.5rem',
            },
            boxShadow: {
                'card': '0 4px 24px 0 rgba(30, 64, 175, 0.08)',
                'card-hover': '0 8px 40px 0 rgba(30, 64, 175, 0.15)',
            },
        },
    },

    plugins: [forms],
};
