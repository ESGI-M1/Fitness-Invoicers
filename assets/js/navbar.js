document.addEventListener('DOMContentLoaded', function() {
    const burgerButton = document.getElementById('burger-menu');
    const leftMenu = document.getElementById('left-menu');
    const closeButton = document.getElementById('close-menu');

    const menuState = localStorage.getItem('menuState');

    if (menuState && window.innerWidth >= 768) {
        leftMenu.classList.add(menuState);
    } else {
        if (window.innerWidth >= 768) {
            leftMenu.classList.add('max-w-full');
        } else {
            leftMenu.classList.add('max-w-0');
        }
    }

    burgerButton.addEventListener('click', () => {
        switchMenu();
    });

    closeButton.addEventListener('click', () => {
        switchMenu();
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

    function switchMenu() {
        if (leftMenu.classList.contains('max-w-full')) {
            leftMenu.classList.remove('max-w-full');
            leftMenu.classList.add('max-w-0');
                closeButton.classList.add('hidden');

        } else {
            leftMenu.classList.remove('max-w-0');
            leftMenu.classList.add('max-w-full');
            if (window.innerWidth < 768) {
                closeButton.classList.remove('hidden');
            }
        }

        const currentState = leftMenu.classList.contains('max-w-full') ? 'max-w-full' : 'max-w-0';
        localStorage.setItem('menuState', currentState);
    }


    initSystemMode();

});
