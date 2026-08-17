/**
 * oe-module-coverage-latam — CPT4 Code Search Popover
 *
 * Muestra una lupa junto al input de código cuando el tipo es CPT4.
 * Al hacer clic abre un popover con búsqueda en vivo contra cpt_codes_es.
 *
 * Depende de: covlConfig (inyectado por dashboard.php), escHtml(), debounce()
 * @package   OpenEMR\Modules\CoverageLatam
 */

const COVL_CptSearch = (() => {
    'use strict';

    // ── Configuración de pares select → input ────────────────────────────
    const PAIRS = [
        { codetypeId: 'fld-auth-codetype', codeId: 'fld-auth-code' },
        { codetypeId: 'fld-freq-codetype', codeId: 'fld-freq-code' },
    ];

    const POPUP_WIDTH = 360;
    const SEARCH_MIN  = 2;
    const SEARCH_LIMIT = 15;

    // ── Helpers reutilizados del patrón existente ────────────────────────
    const escHtml = (str) => String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    const debounce = (fn, ms = 300) => {
        let timer;
        return () => { clearTimeout(timer); timer = setTimeout(fn, ms); };
    };

    const apiUrl = (endpoint, params = {}) => {
        const url = new URL(covlConfig.baseApiUrl + '/' + endpoint, window.location.origin);
        Object.entries(params).forEach(([k, v]) => {
            if (v !== '' && v !== null && v !== undefined) url.searchParams.set(k, v);
        });
        return url.toString();
    };

    // ── Estado ───────────────────────────────────────────────────────────
    let activePopover = null; // referencia al popover abierto actualmente

    // ── Crear markup del popover (se reutiliza) ──────────────────────────
    function createPopover(codeInput) {
        // Wrapper relativo alrededor del input de código
        let wrap = codeInput.parentElement;
        if (!wrap.classList.contains('covl-cpt-wrap')) {
            const newWrap = document.createElement('div');
            newWrap.className = 'covl-cpt-wrap';
            newWrap.style.position = 'relative';
            codeInput.parentElement.insertBefore(newWrap, codeInput);
            newWrap.appendChild(codeInput);
        }
        wrap = codeInput.parentElement;

        // Ícono de lupa
        let icon = wrap.querySelector('.covl-cpt-icon');
        if (!icon) {
            icon = document.createElement('i');
            icon.className = 'fa-solid fa-magnifying-glass covl-cpt-icon';
            Object.assign(icon.style, {
                position: 'absolute',
                right: '8px',
                top: '50%',
                transform: 'translateY(-50%)',
                cursor: 'pointer',
                color: '#6c757d',
                fontSize: '.85rem',
                zIndex: '10',
                display: 'none',
                padding: '4px',
            });
            wrap.appendChild(icon);
        }

        // Contenedor del popover
        let pop = wrap.querySelector('.covl-cpt-popover');
        if (!pop) {
            pop = document.createElement('div');
            pop.className = 'covl-cpt-popover';
            Object.assign(pop.style, {
                display: 'none',
                position: 'absolute',
                top: '100%',
                left: '0',
                width: POPUP_WIDTH + 'px',
                maxHeight: '320px',
                overflowY: 'auto',
                background: '#fff',
                border: '1px solid #dee2e6',
                borderRadius: '6px',
                boxShadow: '0 4px 16px rgba(0,0,0,.15)',
                zIndex: '9999',
                marginTop: '2px',
            });

            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'covl-cpt-search-input form-control form-control-sm';
            searchInput.placeholder = 'Buscar código o descripción...';
            Object.assign(searchInput.style, {
                margin: '6px',
                width: (POPUP_WIDTH - 12) + 'px',
            });
            pop.appendChild(searchInput);

            const resultsDiv = document.createElement('div');
            resultsDiv.className = 'covl-cpt-results';
            Object.assign(resultsDiv.style, {
                maxHeight: '260px',
                overflowY: 'auto',
            });
            pop.appendChild(resultsDiv);

            wrap.appendChild(pop);

            // Eventos del popover
            searchInput.addEventListener('input', debounce(async () => {
                const q = searchInput.value.trim();
                if (q.length < SEARCH_MIN) {
                    resultsDiv.innerHTML = '';
                    return;
                }
                await doSearch(q, resultsDiv, codeInput);
            }, 300));

            // Cerrar al hacer clic fuera
            document.addEventListener('mousedown', (e) => {
                if (activePopover && !activePopover.contains(e.target)
                    && !e.target.classList.contains('covl-cpt-icon')) {
                    closePopover();
                }
            });

            // Cerrar con Escape
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closePopover();
            });
        }

        return { icon, pop, wrap };
    }

    // ── Búsqueda ─────────────────────────────────────────────────────────
    async function doSearch(query, resultsDiv, codeInput) {
        resultsDiv.innerHTML = '<div style="padding:8px 12px;color:#888;font-size:.85rem">Buscando...</div>';

        try {
            const url = apiUrl('cpt_search.php', {
                action: 'search',
                q: query,
                limit: SEARCH_LIMIT,
            });
            const res  = await fetch(url);
            const json = await res.json();

            if (json.error) {
                resultsDiv.innerHTML = `<div style="padding:8px 12px;color:#dc3545;font-size:.85rem">${escHtml(json.error)}</div>`;
                return;
            }

            const rows = json.data ?? [];

            if (rows.length === 0) {
                resultsDiv.innerHTML = '<div style="padding:8px 12px;color:#888;font-size:.85rem">Sin resultados</div>';
                return;
            }

            resultsDiv.innerHTML = '';
            rows.forEach((row) => {
                const item = document.createElement('div');
                item.className = 'covl-cpt-result-item';
                Object.assign(item.style, {
                    padding: '6px 12px',
                    cursor: 'pointer',
                    borderBottom: '1px solid #f0f0f0',
                    fontSize: '.85rem',
                });

                const codeBold = document.createElement('strong');
                codeBold.textContent = row.code;
                codeBold.style.marginRight = '6px';

                const desc = document.createElement('span');
                desc.textContent = row.short_description ?? '';
                desc.style.color = '#555';

                item.appendChild(codeBold);
                item.appendChild(desc);

                item.addEventListener('mouseenter', () => { item.style.background = '#f0f4ff'; });
                item.addEventListener('mouseleave', () => { item.style.background = ''; });

                item.addEventListener('click', () => {
                    codeInput.value = row.code;
                    closePopover();
                    codeInput.dispatchEvent(new Event('change', { bubbles: true }));
                });

                resultsDiv.appendChild(item);
            });
        } catch (err) {
            resultsDiv.innerHTML = '<div style="padding:8px 12px;color:#dc3545;font-size:.85rem">Error de búsqueda</div>';
        }
    }

    // ── Abrir / cerrar popover ───────────────────────────────────────────
    function closePopover() {
        if (activePopover) {
            activePopover.style.display = 'none';
            activePopover = null;
        }
    }

    function togglePopover(pop, icon) {
        if (activePopover === pop) {
            closePopover();
            return;
        }
        // Cerrar cualquier otro popover abierto
        closePopover();

        pop.style.display = 'block';
        activePopover = pop;

        // Posicionar debajo del icono
        const wrapWidth = pop.parentElement.offsetWidth;
        pop.style.left = Math.max(0, wrapWidth - POPUP_WIDTH) + 'px';

        // Focus en el input de búsqueda
        const searchInput = pop.querySelector('.covl-cpt-search-input');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
            const resultsDiv = pop.querySelector('.covl-cpt-results');
            if (resultsDiv) resultsDiv.innerHTML = '';
        }
    }

    // ── Visibilidad de la lupa ───────────────────────────────────────────
    function updateIconVisibility(codetypeSelect, icon) {
        if (!icon) return;
        const isCpt4 = codetypeSelect.value === 'CPT4';
        icon.style.display = isCpt4 ? 'block' : 'none';
        if (!isCpt4) closePopover();
    }

    // ── Inicialización ───────────────────────────────────────────────────
    function init() {
        PAIRS.forEach(({ codetypeId, codeId }) => {
            const codetypeSelect = document.getElementById(codetypeId);
            const codeInput      = document.getElementById(codeId);
            if (!codetypeSelect || !codeInput) return;

            const { icon, pop } = createPopover(codeInput);

            // Estado inicial
            updateIconVisibility(codetypeSelect, icon);

            // Cambio de tipo de código
            codetypeSelect.addEventListener('change', () => {
                updateIconVisibility(codetypeSelect, icon);
            });

            // Click en la lupa
            icon.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                togglePopover(pop, icon);
            });
        });
    }

    // Arrancar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    return { init, closePopover };
})();
