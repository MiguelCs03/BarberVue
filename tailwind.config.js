import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Kids Theme - Bright and playful
                kids: {
                    primary: '#FF6B9D',
                    secondary: '#4ECDC4',
                    accent: '#FFE66D',
                    bg: '#FFF5F7',
                    'bg-dark': '#2C1B47',
                    text: '#2D3748',
                    'text-dark': '#F7FAFC',
                },
                // Youth Theme - Modern and vibrant
                youth: {
                    primary: '#8B5CF6',
                    secondary: '#06B6D4',
                    accent: '#F97316',
                    bg: '#F8FAFC',
                    'bg-dark': '#1E293B',
                    text: '#1E293B',
                    'text-dark': '#F1F5F9',
                },
                // Adults Theme - Professional and elegant
                adults: {
                    primary: '#0F766E',
                    secondary: '#D97706',
                    accent: '#7C3AED',
                    bg: '#FFFFFF',
                    'bg-dark': '#0F172A',
                    text: '#334155',
                    'text-dark': '#E2E8F0',
                },
            },
        },
    },

    plugins: [forms],
};
