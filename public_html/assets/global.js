const forms = document.querySelectorAll('form');

forms.forEach(function(form) {
    const parentDiv = form.parentElement;
    if (parentDiv.classList.contains('search')) {
        form.classList.add('flex', 'justify-around');
    }
});
