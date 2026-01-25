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
                red: {
                    50: '#FBF4F4',
                    100: '#F7E9E9',
                    200: '#EED3D3',
                    300: '#E2B2B2',
                    400: '#D18786',
                    500: '#AB2423',
                    600: '#AB2423',
                    700: '#AB2423',
                    800: '#AB2423',
                    900: '#AB2423',
                },
                brand: {
                    white: '#FFFFFF',
                    gray: '#F9FAFB',
                    surface: '#FFFFFF',
                    border: '#E5E7EB',
                    'border-strong': '#D1D5DB',
                    muted: '#6B7280',
                    charcoal: '#111827',
                    red: '#AB2423',
                    wine: '#AB2423',
                    tint: '#F7E9E9',
                    menu: '#AB2423',
                    'menu-active': '#AB2423',
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
                        bg: '#FBF4F4',
                        brd: '#EED3D3',
                        text: '#AB2423',
                        main: '#AB2423',
                        hover: '#AB2423',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
