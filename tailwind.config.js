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
                maroon: {
                    50: '#f5e6e8',
                    100: '#e8ccd1',
                    200: '#d9a3b3',
                    300: '#ca7a95',
                    400: '#bb5177',
                    500: '#8b3a56',
                    600: '#7a3350',
                    700: '#692c4a',
                    800: '#582544',
                    900: '#471e3e',
                },
            },
        },
    },

    plugins: [forms],
};
