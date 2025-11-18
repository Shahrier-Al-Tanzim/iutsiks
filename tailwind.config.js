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
                'siks': {
                    'primary': '#76C990',
                    'secondary': '#414141',
                    'light': '#8DD4A3',
                    'dark': '#5FB57A',
                    'darker': '#004d40',
                },
            },
        },
    },

    plugins: [forms],
};
