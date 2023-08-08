/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
    safelist: [
        {
            pattern: /bg-/,
            variants: ['dark', 'hover', 'focus', 'dark:hover'],
        },
        {
            pattern: /text-/,
            variants: ['dark', 'hover', 'focus', 'dark:hover'],
        }
    ],
}

