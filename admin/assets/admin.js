/* ============================================================
   THE JOURNEY ARCHIVE — Admin Console behaviour
   No inline JavaScript anywhere (the Content-Security-Policy
   only allows this file and the Bootstrap bundle).
   ============================================================ */

(function () {
    'use strict';

    /* ----------------------------------------------------------
       Mobile sidebar
       ---------------------------------------------------------- */
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const toggles = document.querySelectorAll('[data-sidebar-toggle]');

    function setSidebar(open) {
        if (!sidebar) return;

        sidebar.classList.toggle('is-open', open);

        if (backdrop) {
            backdrop.hidden = !open;
        }

        toggles.forEach(function (button) {
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        if (open) {
            const firstLink = sidebar.querySelector('a, button');
            if (firstLink) firstLink.focus({ preventScroll: true });
        }
    }

    toggles.forEach(function (button) {
        button.addEventListener('click', function () {
            setSidebar(!sidebar.classList.contains('is-open'));
        });
    });

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            setSidebar(false);
        });
    }

    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.matchMedia('(max-width: 991.98px)').matches) setSidebar(false);
            });
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && sidebar && sidebar.classList.contains('is-open')) {
            setSidebar(false);
            const toggle = document.querySelector('[data-sidebar-toggle]');
            if (toggle) toggle.focus();
        }
    });

    /* ----------------------------------------------------------
       Confirmation for destructive actions
       ---------------------------------------------------------- */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
                return;
            }
            const submitter = form.querySelector('[type="submit"]');
            if (submitter) submitter.disabled = true;
        });
    });

    /* ----------------------------------------------------------
       Prevent accidental double submits
       ---------------------------------------------------------- */
    document.querySelectorAll('form[data-submit-once]').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (typeof form.checkValidity === 'function' && !form.checkValidity()) return;
            const submitter = form.querySelector('[type="submit"]');
            if (submitter) {
                submitter.disabled = true;
                submitter.dataset.originalText = submitter.innerHTML;
                submitter.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Saving…';
            }
        });
    });

    /* ----------------------------------------------------------
       Show / hide password
       ---------------------------------------------------------- */
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-toggle-password');
            const field = document.getElementById(targetId);
            if (!field) return;

            const show = field.type === 'password';
            field.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', show ? 'true' : 'false');
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');

            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-eye', !show);
                icon.classList.toggle('bi-eye-slash', show);
            }
        });
    });

    /* ----------------------------------------------------------
       Image preview for the trip form
       ---------------------------------------------------------- */
    document.querySelectorAll('[data-image-input]').forEach(function (input) {
        input.addEventListener('change', function () {
            const previewId = input.getAttribute('data-image-input');
            const preview = document.getElementById(previewId);
            if (!preview) return;

            const file = input.files && input.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                window.alert('Please choose a JPG, PNG or WebP image.');
                input.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                window.alert('That image is larger than 5 MB. Please choose a smaller file.');
                input.value = '';
                return;
            }

            const image = preview.querySelector('img');
            if (!image) return;

            const reader = new FileReader();
            reader.addEventListener('load', function () {
                image.src = String(reader.result);
                image.hidden = false;
                const empty = preview.querySelector('.image-preview__empty');
                if (empty) empty.hidden = true;
                const badge = preview.querySelector('.image-preview__badge');
                if (badge) badge.textContent = 'New image';
            });
            reader.readAsDataURL(file);
        });
    });

    /* ----------------------------------------------------------
       Character counters
       ---------------------------------------------------------- */
    document.querySelectorAll('[data-counter-for]').forEach(function (counter) {
        const field = document.getElementById(counter.getAttribute('data-counter-for'));
        const limit = Number(counter.getAttribute('data-counter-max') || 0);
        if (!field) return;

        const update = function () {
            const length = field.value.length;
            counter.textContent = limit > 0 ? length + ' / ' + limit : String(length);
        };

        field.addEventListener('input', update);
        update();
    });

    /* ----------------------------------------------------------
       Flash messages: dismiss + auto-hide
       ---------------------------------------------------------- */
    document.querySelectorAll('[data-flash]').forEach(function (flash) {
        const dismiss = flash.querySelector('[data-dismiss-flash]');

        if (dismiss) {
            dismiss.addEventListener('click', function () {
                flash.remove();
            });
        }

        window.setTimeout(function () {
            if (!document.body.contains(flash)) return;
            flash.style.transition = 'opacity .4s ease, transform .4s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-6px)';
            window.setTimeout(function () {
                flash.remove();
            }, 420);
        }, 7000);
    });
})();
