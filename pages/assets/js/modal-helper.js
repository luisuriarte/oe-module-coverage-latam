/**
 * oe-module-coverage-latam — Modal helpers universales
 *
 * Abre/cierra modales Bootstrap sin depender de una versión concreta:
 *   - Bootstrap 4 (jQuery .modal())
 *   - Bootstrap 5 (Modal.getOrCreateInstance / getInstance)
 *   - Fallback nativo (display block + clase .show)
 *
 * @package   OpenEMR\Modules\CoverageLatam
 */
(function () {
    function openModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        if (window.jQuery && window.jQuery(el) && typeof window.jQuery(el).modal === 'function') {
            window.jQuery(el).modal('show');
            return;
        }
        if (window.bootstrap && window.bootstrap.Modal && typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
            var modal = window.bootstrap.Modal.getOrCreateInstance(el);
            if (modal) { modal.show(); return; }
        }
        el.style.display = 'block';
        el.classList.add('show');
        document.body.classList.add('modal-open');
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        if (window.jQuery && window.jQuery(el) && typeof window.jQuery(el).modal === 'function') {
            window.jQuery(el).modal('hide');
            return;
        }
        if (window.bootstrap && window.bootstrap.Modal && typeof window.bootstrap.Modal.getInstance === 'function') {
            var modal = window.bootstrap.Modal.getInstance(el);
            if (modal) { modal.hide(); return; }
        }
        el.style.display = 'none';
        el.classList.remove('show');
        document.body.classList.remove('modal-open');
    }

    window.openModal  = openModal;
    window.closeModal = closeModal;
})();
