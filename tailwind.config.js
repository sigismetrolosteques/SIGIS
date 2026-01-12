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
                brand: {
                    white: '#FFFFFF',
                    gray: '#F9FAFB',
                    surface: '#FFFFFF',
                    border: '#E5E7EB',
                    'border-strong': '#D1D5DB',
                    muted: '#6B7280',
                    charcoal: '#111827',
                    red: '#E11D48',
                    wine: '#BE123C',
                    tint: '#FFE4E6',
                    menu: '#BE123C',
                    'menu-active': '#9F1239',
                },
                status: {
                    approved: {
                        bg: '#ECFDF5',
                        brd: '#A7F3D0',
                        text: '#065F46',
                        main: '#34D399',
                    },
                    pending: {
                        bg: '#FFF7ED',
                        brd: '#FDBA74',
                        text: '#9A3412',
                        main: '#FB923C',
                    },
                    danger: {
                        bg: '#FEF2F2',
                        brd: '#FCA5A5',
                        text: '#991B1B',
                        main: '#EF4444',
                        hover: '#DC2626',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
