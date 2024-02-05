let overlay;

const startOverlay = () => {
    overlay = setTimeout(() => {
        const overlayOptions = {
            background: "rgba(0, 0, 0, 0.5)",
            image: "",
            fontawesomeColor: '#FFF',
            fontawesome: "fa fa-spinner",
            fontawesomeAnimation: "rotate_right"
        };

        const overlayDiv = document.createElement('div');
        overlayDiv.classList.add('loading-overlay');

        document.body.appendChild(overlayDiv);

    }, 500);
};

const stopOverlay = function () {
    clearTimeout(overlay);
    LoadingOverlay("hide");
};

const fancyConfirm = function (opts) {
    opts = Object.assign({
        type: 'green',
        title: 'Confirmation',
        message: 'Etes-vous sûr de vouloir effectuer cette opération?',
        okButton: 'Oui',
        noButton: 'Annuler',
        callback: () => {},
        adButton: null,
        intButton: null
    }, opts);

    let buttons = {};

    buttons.confirm = {
        text: opts.okButton,
        action: opts.callback
    };

    buttons.cancel = {
        text: opts.noButton,
        btnClass: 'btn-success'
    };

}


const customConfirm = function(options) {
    const {
        bgOpacity,
        theme,
        type,
        icon,
        title,
        content,
        buttons
    } = options;

};

function showAlert(close, limit) {
    function extend(target, source) {
        for (let key in source) {
            if (source.hasOwnProperty(key)) {
                target[key] = source[key];
            }
        }
        return target;
    }
    let opts = extend({
        type: 'green',
        title: 'Message',
        message: '',
        okButton: 'Ok',
        limit: limit || 2000
    }, opts || {});

    let closeIcon = false;
    if (opts.type === 'green') {
        closeIcon = true;
        if (close) {
            opts.autoClose = 'cancel|' + opts.limit;
        }
    }
    function showAlert(opts, closeIcon) {
        console.log('Alert:', opts, closeIcon);
    }
    showAlert({
        bgOpacity: 0.9,
        theme: 'supervan',
        type: opts.type,
        icon: 'fa fa-question-circle',
        title: opts.title,
        content: opts.message,
        closeIcon: closeIcon,
        autoClose: opts.autoClose,
        buttons: {
            cancel: {
                text: opts.okButton,
                keys: ['esc', 'enter']
            }
        }
    });
};

const parseResponse = function (response, status, xhr, data) {
    let ct = xhr.getResponseHeader("content-type") || "";
    let target = data.target || '.ajax-content';
    if (ct.indexOf('html') > -1) {
        var targetElement = document.querySelector(target);
        targetElement.innerHTML = response;
    } else if (ct.indexOf('json') > -1) {
        if (response.message !== undefined) {
            showAlert({ message: response.message });
        }
        if (response.redirect !== undefined) {
            window.location.replace(response.redirect);
        }
        if (response.target !== undefined) {
            target = response.target;
        }
        if (response.content !== undefined) {
            document.querySelector(target).innerHTML = response.content;
        }
        if (response.refresh !== undefined) {
            if (response.refresh === true) {
                window.location.reload();
            } else {
                const xhr = new XMLHttpRequest();
                xhr.open(response.method || 'GET', response.refresh, false);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        document.querySelector(target).innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            }
        }
    }
}

const preparePlugins = function () {
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(function (element) {
        element.tooltip({ 'html': true });
    });

    document.querySelectorAll('.tree li.treeview a').forEach(function (link) {
        link.classList.add(link.querySelector('.fa-angle-left').classList.add('rotate-duration'));
        link.addEventListener('click', function (e) {
            link.querySelector('.fa-angle-left').classList.toggle('fa-angle-rotate');
        });
    });

    document.querySelectorAll('.ajax-pagination .page-item, #planning-sortable th').forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            let src = item.querySelector('a').getAttribute('href');

            let xhr = new XMLHttpRequest();
            xhr.open('GET', src, false);

            xhr.onload = function () {
                let modal = document.getElementById('modal');
                modal.querySelector('.modal-content').innerHTML = xhr.responseText;
                modal.modal('show');

                stopOverlay();
                loadAddForm();
            };

            xhr.onerror = function () {
                console.error('Error occurred during AJAX request.');
            };

            xhr.send();
        });
    });

    document.querySelectorAll('.js-datepicker').forEach(function (element) {
        element.datetimepicker({
            format: element.dataset.format,
            locale: 'fr'
        });

        if (element.classList.contains('filter')) {
            element.addEventListener('dp.change', function (event) {
                element.form.submit();
            });
        }
    });

    document.querySelectorAll('.bootstrap-filestyle').forEach(function (element) {
        element.filestyle({
            input: false,
            buttonText: element.dataset.buttontext,
            buttonName: element.dataset.buttonname,
            iconName: element.dataset.iconname
        });
    });

    document.querySelectorAll('.select2').forEach(function (select) {
        new Select2(select, {
            closeOnSelect: false,
            width: '100%',
            dropdownCssClass: 'instant-search'
        });
    });

    document.addEventListener('select2:open', function () {
        document.querySelector('.select2-search__field').focus();
    });

    tinymce.remove();
    tinymce.init({
        selector: 'textarea.tinymce',
        statusbar: false,
        menubar: false,
        height: '300px',
        promotion: false
    });

    document.querySelectorAll('.ajax-function').forEach(function (element) {
        let f = function (value) {
            if (value) {
                startOverlay();
                let xhr = new XMLHttpRequest();
                xhr.open(element.dataset.method || 'GET', element.dataset.url, false);

                xhr.onload = function () {
                    parseResponse(xhr.responseText, xhr.statusText, xhr, element.dataset);
                    stopOverlay();
                    preparePlugins();
                    prepareSubmit();
                };

                xhr.send();
            }
        };

        if (element.classList.contains('do-confirm')) {
            if (element.classList.contains('mdp-oublie')) {
                fancyConfirm({
                    type: 'orange',
                    title: 'Réinitialisation du mot de passe',
                    message: 'Choisissez le type de compte :',
                    adButton: 'Utilisateur Interne',
                    intButton: 'Utilisateur externe',
                    noButton: 'Annuler',
                });
            } else {
                fancyConfirm({
                    type: 'orange',
                    title: element.dataset.title || undefined,
                    message: element.dataset.message || undefined,
                    callback: f
                });
            }
        } else {
            f(true);
        }
    });

    document.querySelectorAll('input[type=range]').forEach(function (rangeField) {
        let rangeValue = rangeField.nextElementSibling;
        rangeValue.innerText = rangeField.value;

        rangeField.addEventListener("input", function () {
            rangeValue.innerText = rangeField.value;
        });
    });

    document.querySelectorAll('div input[type=range]').forEach(function (rangeField) {
        rangeField.parentNode.classList.add("d-flex");
    });

    document.querySelectorAll(".typeQuota").forEach(function (element) {
        element.addEventListener('click', function () {
            let symbolElement = document.getElementById('symbol');
            let peopleIconElement = document.getElementById('peopleIcon');

            if (symbolElement.style.display === 'none') {
                symbolElement.style.display = 'inline';
                peopleIconElement.style.display = 'none';
            } else {
                symbolElement.style.display = 'none';
                peopleIconElement.style.display = 'inline';
            }
        });
    });

    document.getElementById('addGroup').addEventListener('submit', function (event) {
        let quotaDftField = document.getElementById('quotaDft');
        let quotaDftValue = quotaDftField.value;

        let symbolElement = document.getElementById('symbol');
        if (symbolElement.style.display === 'inline') {
            quotaDftValue = -Math.abs(quotaDftValue);
        } else if (symbolElement.style.display === 'none') {
            quotaDftValue = Math.abs(quotaDftValue);
        }
        quotaDftField.value = quotaDftValue;
    });

    document.querySelectorAll(".fancy").forEach(function (element) {
        element.removeEventListener('click', fancyClickHandler);
        element.addEventListener('click', fancyClickHandler);
    });

    document.querySelector('.modal-communication').addEventListener('hide.bs.modal', function () {
        location.reload();
    });

    function fancyClickHandler() {
        let src = this.dataset.src;
        startOverlay();
        let xhr = new XMLHttpRequest();
        xhr.open('GET', src, false);

        xhr.onload = function () {
            let modal = document.getElementById('modal');
            modal.querySelector('.modal-content').innerHTML = xhr.responseText;
            modal.modal('show');

            stopOverlay();
            preparePlugins();
            prepareSubmit();
        };

        xhr.send();
    }
}

function prepareSubmit(callback, prepare) {
    document.getElementById('addGroup').removeEventListener('submit', submitHandler);
    document.getElementById('addGroup').addEventListener('submit', submitHandler);

    document.querySelectorAll('.dialog-submit form, .ajax-submit form').forEach(function (form) {
        if (form.id !== 'addGroup') {
            form.removeEventListener('submit', submitHandler);
        }

        form.addEventListener('submit', function (e) {
            var clickedButton = form.querySelector(".btn[type='submit']:focus").getAttribute('name');
            form.removeEventListener('submit', submitHandler);
            let f = function (value) {
                if (value) {
                    let formData = new FormData(form);
                    formData.append(clickedButton, 1);

                    let xhr = new XMLHttpRequest();
                    xhr.open('POST', form.getAttribute('action'), true);

                    xhr.onload = function () {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            parseResponse(xhr.responseText, xhr.statusText, xhr, form.dataset);
                            stopOverlay();
                            preparePlugins();
                            prepareSubmit(callback, prepare);
                            if (typeof callback === 'function') {
                                callback();
                            }
                        }
                    };

                    xhr.onerror = function () {};

                    startOverlay();
                    if (typeof prepare === 'function') {
                        prepare();
                    }

                    xhr.send(formData);
                }
            };

            if (form.classList.contains('do-confirm') && !form.querySelector(".btn[type='submit']:focus").classList.contains('no-confirm')) {
                fancyConfirm({
                    type: 'orange',
                    title: form.dataset.title || undefined,
                    message: form.dataset.message || undefined,
                    callback: f
                });
            } else {
                f(true);
            }

            e.preventDefault();
        });
    });
}

function submitHandler(event) {
    const quotaDftField = document.getElementById('quotaDft');
    let quotaDftValue = quotaDftField.value;
    const symbolElement = document.getElementById('symbol');
    if (symbolElement.style.display === 'inline') {
        quotaDftValue = -Math.abs(quotaDftValue);
    } else if (symbolElement.style.display === 'none') {
        quotaDftValue = Math.abs(quotaDftValue);
    }
    quotaDftField.value = quotaDftValue;
}

prepareSubmit();

