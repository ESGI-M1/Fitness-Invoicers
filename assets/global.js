import $ from 'jquery';

function fancyConfirm(opts) {
    opts = Object.assign({
        type: 'green',
        title: 'Confirmation',
        message: 'Etes-vous sûr de vouloir effectuer cette opération?',
        okButton: 'Oui',
        noButton: 'Annuler',
        callback: function () {
        },
        adButton: null,
        intButton: null
    }, opts);
}

document.addEventListener('DOMContentLoaded', function () {
    $('.select2').select2({
        closeOnSelect: false,
        style: 'min-width: 150px',
        dropdownCssClass: 'instant-search',
    });
    $('.search .select2').on('change', function (e) {
        $(this).closest('form').trigger('submit');
    });
    const ajaxFunctions = document.querySelectorAll('.ajax-function');

    ajaxFunctions.forEach(element => {
        element.removeEventListener('click', ajaxClickHandler);
        element.addEventListener('click', ajaxClickHandler);
        element.classList.remove('ajax-function');
    });

    function ajaxClickHandler(e) {
        e.preventDefault();
        const element = e.currentTarget;
        const f = function (value) {
            if (value) {
                const url = element.dataset.url;
                const method = element.dataset.method || 'GET';
                const xhr = new XMLHttpRequest();
                xhr.open(method, url, false);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        parseResponse(xhr.responseText, xhr.status, xhr, element.dataset);
                    }
                };
                xhr.send();
            }
        };

        if (element.classList.contains('do-confirm')) {
            fancyConfirm({
                type: 'orange',
                title: element.dataset.title || undefined,
                message: element.dataset.message || undefined,
                callback: f
            });
        } else {
            f(true);
        }
    }

    const forms = document.querySelectorAll('form');

    forms.forEach(function (form) {
        const parentDiv = form.parentElement;

        if (parentDiv.classList.contains('search')) {
            form.querySelectorAll('input').forEach(input => {
                input.addEventListener('change', function() {
                    form.submit();
                });
            });
        }
    });
});