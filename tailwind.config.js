import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
const colors = require('tailwindcss/colors')

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
		'./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
		 './vendor/laravel/jetstream/**/*.blade.php',
		 './storage/framework/views/*.php',
		 './resources/views/**/*.blade.php',
		 "./vendor/robsontenorio/mary/src/View/Components/**/*.php",
         './app/**/*.php',
         './resources/**/*.blade.php',
         './vendor/filament/**/*.blade.php',
	],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            typography: {
                DEFAULT: {
                    css: {
                        maxWidth: '100%',
                    },
                },
                invert: {
                    css: {
                        '--tw-prose-body': colors.gray[300],
                        '--tw-prose-headings': colors.white,
                        '--tw-prose-links': colors.blue[400],
                        '--tw-prose-links-hover': colors.blue[300],
                        '--tw-prose-underline': `color-mix(in srgb, ${colors.blue[400]} 70%, transparent)`,
                        '--tw-prose-underline-hover': colors.blue[400],
                        '--tw-prose-bold': colors.white,
                        '--tw-prose-counters': colors.gray[400],
                        '--tw-prose-bullets': colors.gray[600],
                        '--tw-prose-hr': colors.gray[700],
                        '--tw-prose-quote-borders': colors.gray[700],
                        '--tw-prose-captions': colors.gray[400],
                        '--tw-prose-code': colors.white,
                        '--tw-prose-pre-code': colors.gray[300],
                        '--tw-prose-pre-bg': 'rgb(0 0 0 / 50%)',
                        '--tw-prose-th-borders': colors.gray[600],
                        '--tw-prose-td-borders': colors.gray[700],
                    },
                },
            },
        },
    },

    plugins: [
		forms,
		typography,
		require('daisyui'),
	],
};
