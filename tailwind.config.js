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
            },
            colors: {
                // Render-inspired teal accent (replaces the default indigo scale).
                // 400 is the bright signature accent for text/icons/highlights;
                // 500/600 are deep enough to keep white text AA-contrast on buttons.
                indigo: {
                    50: '#effcfb',
                    100: '#d4f5f1',
                    200: '#a9ebe3',
                    300: '#7de2d8',
                    400: '#46e1d5',
                    500: '#0d9488',
                    600: '#0f766e',
                    700: '#115e59',
                    800: '#134e4a',
                    900: '#123f3a',
                    950: '#042f2e',
                },
            },
        },
    },

    plugins: [forms],
};
