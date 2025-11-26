import {defineConfig} from 'tailwindcss';

export default defineConfig({
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/css/**/*.css',
        './resources/css/filament/admin/nord.css',
        './resources/css/filament/tenant/nord.css',
        './app/Filament/Admin/**/*.php',
        './app/Filament/Tenant/**/*.php',
        './app/Http/Controllers/**/*.php',
        './app/Models/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'Instrument Sans',
                    'ui-sans-serif',
                    'system-ui',
                    'sans-serif',
                    'Apple Color Emoji',
                    'Segoe UI Emoji',
                    'Segoe UI Symbol',
                    'Noto Color Emoji',
                ],
            },
            colors: {
                primary: '#3b82f6', // Example primary color
                nord: {
                    DEFAULT: '#2E3440',
                    light: '#D8DEE9',
                    dark: '#3B4252',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
});
