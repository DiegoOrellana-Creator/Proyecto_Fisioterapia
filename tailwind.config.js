import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Instrument Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"Spline Sans Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                lienzo: '#EEF3F2',
                linea: '#D3DFDE',
                tinta: { DEFAULT: '#0F2A30', suave: '#4F6870' },
                petroleo: { DEFAULT: '#0E5E66', oscuro: '#0A4A51', claro: '#DCEBEA' },
                cinta: { DEFAULT: '#E5487A', claro: '#FBE3EB' },
                estado: {
                    solicitada: '#9A6A0B',
                    confirmada: '#0E5E66',
                    reprogramada: '#4B4FA8',
                    cancelada: '#9B3030',
                    atendida: '#23703F',
                    falta: '#8A3A12',
                },
            },
            borderRadius: {
                ctl: '8px',
                tarjeta: '14px',
            },
            maxWidth: {
                sitio: '72rem',
            },
        },
    },

    plugins: [forms],
};
