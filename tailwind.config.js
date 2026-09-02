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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                paper: '#FAF9F4',
                pine: {
                    50: '#EAF1FB',
                    100: '#D3E3F7',
                    500: '#1E4E8C',
                    600: '#1A4478',
                    700: '#15375F',
                },
                amber: {
                    50: '#FDF3E3',
                    100: '#FBE7C7',
                    500: '#E8A33D',
                    600: '#D6912E',
                },
                ink: '#1A1F1C',
                muted: '#6B7268',
            },
        },
    },
    plugins: [forms],
};