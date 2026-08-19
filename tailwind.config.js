import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Colors are CSS-variable-backed (not static hex) so the theme can switch at
// runtime via the [data-theme] attribute set in partials/theme-init.blade.php.
// The rgb(var(--x) / <alpha-value>) form is required for Tailwind's opacity
// modifiers (e.g. bg-accent-500/10) to keep working with variable colors.
const withOpacity = (variable) => `rgb(var(${variable}) / <alpha-value>)`;

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
                surface: {
                    DEFAULT: withOpacity('--color-surface'),
                    raised: withOpacity('--color-surface-raised'),
                    hover: withOpacity('--color-surface-hover'),
                },
                border: {
                    DEFAULT: withOpacity('--color-border'),
                    strong: withOpacity('--color-border-strong'),
                },
                ink: {
                    DEFAULT: withOpacity('--color-ink'),
                    muted: withOpacity('--color-ink-muted'),
                    subtle: withOpacity('--color-ink-subtle'),
                },
                accent: {
                    50: withOpacity('--color-accent-50'),
                    100: withOpacity('--color-accent-100'),
                    200: withOpacity('--color-accent-200'),
                    300: withOpacity('--color-accent-300'),
                    400: withOpacity('--color-accent-400'),
                    500: withOpacity('--color-accent-500'),
                    600: withOpacity('--color-accent-600'),
                    700: withOpacity('--color-accent-700'),
                    800: withOpacity('--color-accent-800'),
                    900: withOpacity('--color-accent-900'),
                    950: withOpacity('--color-accent-950'),
                },
            },
        },
    },

    plugins: [forms],
};
