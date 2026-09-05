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
                    50: '#EAF1FB', 100: '#D3E3F7', 200: '#A8C7EF', 300: '#7DAAE6',
                    400: '#4C87D6', 500: '#1E4E8C', 600: '#1A4478', 700: '#1A4478',
                    800: '#102A49', 900: '#0B1E34',
                },
                amber: {
                    50: '#FDF3E3', 100: '#FBE7C7', 200: '#F7D9A0', 300: '#F3CB79',
                    400: '#EDB84F', 500: '#E8A33D', 600: '#D6912E', 700: '#B87A26',
                    800: '#96621F', 900: '#744B18',
                },
                terracotta: {
                    50: '#FBEEE7', 100: '#F5D9C7', 200: '#EDBCA0', 300: '#E39D78',
                    400: '#D67F57', 500: '#C3623F', 600: '#A94F30', 700: '#8A3F27',
                    800: '#6B301E', 900: '#4D2216',
                },
                prune: {
                    50: '#F4EDF2', 100: '#E4CFE0', 200: '#CBA3C7', 300: '#B078AF',
                    400: '#93589A', 500: '#74407D', 600: '#5F3468', 700: '#4A2851',
                    800: '#361D3B', 900: '#241326',
                },
                ardoise: {
                    50: '#F1F3F5', 100: '#DCE1E6', 200: '#B9C2CB', 300: '#97A3B0',
                    400: '#778496', 500: '#5C6B7D', 600: '#4A5768', 700: '#3A4453',
                    800: '#2B323D', 900: '#1D2228',
                },
                muted: {
                    DEFAULT: '#6B7268',
                    50: '#F5F5F2', 100: '#E7E7E1', 300: '#C7C8BF',
                    500: '#6B7268', 700: '#4A4F47', 900: '#2E322C',
                },
                ink: {
                    DEFAULT: '#1A1F1C',
                    700: '#33382F',
                },
            },
        },
    },
    plugins: [forms],
};