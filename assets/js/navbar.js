console.log("js loaded");

document.addEventListener('DOMContentLoaded', function() {
    const burgerButton = document.getElementById('burger-menu');
    const leftMenu = document.getElementById('left-menu');

    burgerButton.addEventListener('click', () => {
        leftMenu.classList.toggle('max-w-full');
        leftMenu.classList.toggle('max-w-0');
    });

    const lightMode = document.getElementById('lightMode');
    const darkMode = document.getElementById('darkMode');
    const systemMode = document.getElementById('systemMode');

    lightMode.addEventListener('click', () => {
        console.log('lightMode');
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light'
        systemMode.classList.remove('bg-primary');
    });

    darkMode.addEventListener('click', () => {
        console.log('darkMode');
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark'
        systemMode.classList.remove('bg-primary');
    });

    systemMode.addEventListener('click', () => {
        console.log('systemMode');
        localStorage.removeItem('theme');
        initSystemMode();
    });

    function initSystemMode(){

        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            console.log('darkMode loaded');
        } else {
            document.documentElement.classList.remove('dark');
            console.log('lightMode loaded');
        }
    }

    initSystemMode();

});

