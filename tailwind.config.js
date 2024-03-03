module.exports = {
    darkMode: 'class',
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
    ],
    theme: {
        extend: {
            colors: {
                primaryLighter: '#C8FAD6',
                grey: '#797979',
                primary: {
                    DEFAULT: '#3182CE', // Bleu
                    hover: '#2C5282', // Bleu foncé au survol
                },
                secondary: {
                    DEFAULT: '#38A169', // Vert
                    hover: '#276749', // Vert foncé au survol
                },
                tertiary: {
                    DEFAULT: '#E53E3E', // Rouge
                    hover: '#C53030', // Rouge foncé au survol
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
        require('@tailwindcss/forms'),
        require('@tailwindcss/aspect-ratio'),
        require('@tailwindcss/container-queries'),
    ],
};
