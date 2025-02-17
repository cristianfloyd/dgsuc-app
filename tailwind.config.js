import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
		'./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
		 './vendor/laravel/jetstream/**/*.blade.php',
		 './storage/framework/views/*.php',
		 './resources/views/**/*.blade.php',
		 "./vendor/robsontenorio/mary/src/View/Components/**/*.php",
         './app/**/*.php',
	],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            typography: ({ theme }) => ({
                invert: {
                    css: {
                        '--tw-prose-body': theme('colors.gray[300]'),
                        '--tw-prose-headings': theme('colors.white'),
                        '--tw-prose-links': theme('colors.blue[400]'),
                        '--tw-prose-links-hover': theme('colors.blue[300]'),
                        '--tw-prose-underline': theme('colors.blue[400/70]'),
                        '--tw-prose-underline-hover': theme('colors.blue[400]'),
                        '--tw-prose-bold': theme('colors.white'),
                        '--tw-prose-counters': theme('colors.gray[400]'),
                        '--tw-prose-bullets': theme('colors.gray[600]'),
                        '--tw-prose-hr': theme('colors.gray[700]'),
                        '--tw-prose-quote-borders': theme('colors.gray[700]'),
                        '--tw-prose-captions': theme('colors.gray[400]'),
                        '--tw-prose-code': theme('colors.white'),
                        '--tw-prose-pre-code': theme('colors.gray[300]'),
                        '--tw-prose-pre-bg': 'rgb(0 0 0 / 50%)',
                        '--tw-prose-th-borders': theme('colors.gray[600]'),
                        '--tw-prose-td-borders': theme('colors.gray[700]'),
                    },
                },
            }),
        },
    },

    plugins: [
		forms,
		typography,
		require('daisyui'),
	],
};
