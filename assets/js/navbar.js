document.addEventListener('DOMContentLoaded', function() {
    const burgerButton = document.getElementById('burger-menu');
    const leftMenu = document.getElementById('left-menu');
    const topMenu = document.getElementById('top-menu');

    burgerButton.addEventListener('click', () => {
        leftMenu.classList.toggle('show-menu');

        if (leftMenu.classList.contains('show-menu')) {
            const percentageWidth = (leftMenu.offsetWidth / window.innerWidth) * 100;
            topMenu.style.marginLeft = percentageWidth + '%';
        } else {
            topMenu.style.marginLeft = '0';
        }
    });

});
