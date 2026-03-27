import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import path from 'path';

const preset = require('../../../../vendor/filament/filament/tailwind.config.preset');

export default {
    presets: [preset],
    content: [
        path.resolve(__dirname, 'resources/views/**/*.blade.php'),
        path.resolve(__dirname, 'resources/js/**/*.js'),
        path.resolve(__dirname, '../../../../resources/views/**/*.blade.php'),
        path.resolve(__dirname, '../../../../storage/app/themes/**/resources/**/*.blade.php'),
        path.resolve(__dirname, '../../../../storage/framework/views/*.php'),
        path.resolve(__dirname, '../../../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php'),
        path.resolve(__dirname, '../../../../vendor/laravel/jetstream/**/*.blade.php'),
    ],
    plugins: [forms, typography],
};
