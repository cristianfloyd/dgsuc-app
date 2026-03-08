import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    plugins: [daisyui],
    daisyui: {
        themes: false,
        darkTheme: 'dark',
        base: true,
        styled: true,
        utils: true,
    },
};
