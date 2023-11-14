document.addEventListener('DOMContentLoaded', function() {
    const burgerButton = document.getElementById('burger-menu');
    const leftMenu = document.getElementById('left-menu');
    const topMenu = document.getElementById('top-menu');

    burgerButton.addEventListener('click', () => {
        leftMenu.classList.toggle('show-menu');

        if (leftMenu.classList.contains('show-menu')) {
            topMenu.style.marginLeft = leftMenu.offsetWidth + 'px';
        } else {
            topMenu.style.marginLeft = '0';
        }
    });
});
