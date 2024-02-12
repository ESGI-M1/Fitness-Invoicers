document.addEventListener('DOMContentLoaded', function () {
    function fancyForm(src) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', src, false);

        xhr.onload = function () {
            let modal = document.getElementById('modal');
            modal.querySelector('.modal-content').innerHTML = xhr.responseText;

            let form = modal.querySelector('form.add-form');
            let formData = new FormData(form);
            src += '?' + new URLSearchParams(formData).toString();

            modal.modal('show');
            loadAddForm();
        };

        xhr.send();
    }

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('fancy-add')) {
            let src = event.target.dataset.src;
            fancyForm(src);
        }
    });

    // document.querySelector('.fancy-add-csv').addEventListener('click', function () {
    //     window.location.href = this.dataset.src;
    //     loadAddForm();
    // });

    loadAddForm = function () {
        prepareSubmit(function () {
            loadAddForm();
        });
        sendForm = function (form) {
            let data = {
                update_fields: 1
            };
            form.submit();
        }

        let $this = document.querySelector('form.add-form');
        Array.from($this.elements).forEach(function (input) {
            let elem = input;
            elem.addEventListener('change', function () {
                if (elem.classList.contains('refresh')) {
                    sendForm($this);
                }
            });
        });
    };
});
