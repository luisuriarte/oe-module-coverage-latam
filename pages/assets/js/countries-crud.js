/**
 * oe-module-coverage-latam — countries-crud.js
 *
 * Módulo JS para la gestión de paquetes de país (tab "countries" del dashboard):
 *  - Lista los paquetes instalados (covl_country_packs).
 *  - Modal con búsqueda sobre el catálogo (packs/*.json) e instalación/actualización.
 * Sin dependencias externas — usa fetch() y los helpers openModal/closeModal del dashboard.
 * Todas las cadenas visibles provienen de covlConfig.i18n (traducidas en PHP con xl*()).
 *
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   GPL-3.0
 */

/* global covlConfig */

'use strict';

(function () {

    // -----------------------------------------------------------------------
    // Traducción: las cadenas se inyectan traducidas desde PHP
    // -----------------------------------------------------------------------
    const t = (key) => (covlConfig.i18n && covlConfig.i18n[key] !== undefined) ? covlConfig.i18n[key] : key;

    // -----------------------------------------------------------------------
    // Estado compartido
    // -----------------------------------------------------------------------
    let state = {
        catalog: [],
        search:  '',
    };

    // -----------------------------------------------------------------------
    // Utilidades
    // -----------------------------------------------------------------------
    const apiUrl = (endpoint, params = {}) => {
        const url = new URL(covlConfig.baseApiUrl + '/' + endpoint, window.location.origin);
        Object.entries(params).forEach(([k, v]) => {
            if (v !== '' && v !== null && v !== undefined) url.searchParams.set(k, v);
        });
        return url.toString();
    };

    const csrfHeaders = () => ({
        'X-CSRF-Token': covlConfig.csrfToken,
        'Content-Type': 'application/json',
    });

    const escHtml = (str) => String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    const debounce = (fn, ms = 250) => {
        let timer;
        return () => {
            clearTimeout(timer);
            timer = setTimeout(fn, ms);
        };
    };

    // Bandera por emoji regional (ISO 3166-1 alpha-2 → regional indicator), sin dependencias CSS
    const flagEmoji = (cc) => {
        const s = String(cc || '').toUpperCase();
        if (!/^[A-Z]{2}$/.test(s)) return '🏳️';
        return [...s].map((c) => String.fromCodePoint(0x1F1E6 + (c.charCodeAt(0) - 65))).join('');
    };

    // -----------------------------------------------------------------------
    // Toast notifications
    // -----------------------------------------------------------------------
    const toast = (() => {
        let container = null;
        const ensure = () => {
            if (!container) {
                container = document.createElement('div');
                container.className = 'covl-toast-container';
                document.body.appendChild(container);
            }
            return container;
        };
        return (message, type = 'success', durationMs = 3500) => {
            const icons = { success: '✓', error: '✕', info: 'ℹ' };
            const el = document.createElement('div');
            el.className = `covl-toast ${type}`;
            el.innerHTML = `<span>${icons[type] ?? 'ℹ'}</span><span>${escHtml(message)}</span>`;
            ensure().appendChild(el);
            setTimeout(() => el.remove(), durationMs);
        };
    })();

    // -----------------------------------------------------------------------
    // Modales (usa los helpers universales del dashboard, con fallback)
    // -----------------------------------------------------------------------
    const showModal = (id) => {
        if (typeof window.openModal === 'function') { window.openModal(id); return; }
        const el = document.getElementById(id);
        if (el) { el.style.display = 'block'; el.classList.add('show'); }
    };
    const hideModal = (id) => {
        if (typeof window.closeModal === 'function') { window.closeModal(id); return; }
        const el = document.getElementById(id);
        if (el) { el.style.display = 'none'; el.classList.remove('show'); }
    };

    // -----------------------------------------------------------------------
    // Render: lista de paquetes instalados
    // -----------------------------------------------------------------------
    const renderInstalled = (rows) => {
        const tbody = document.getElementById('covl-country-tbody');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="covl-empty">
                <div class="icon">🌎</div><p>${escHtml(t('countries_empty'))}</p>
            </div></td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map((r) => {
            const code = escHtml(r.country_code);
            const loaded = r.default_rules_loaded == 1;
            const curSymbol = escHtml(r.currency_symbol ?? '');
            const curName   = escHtml(r.currency_name  ?? r.currency_code ?? '');
            return `<tr>
                <td>
                    <span class="covl-country-flag">${flagEmoji(r.country_code)}</span>
                    <strong>${code}</strong>
                </td>
                <td>${escHtml(r.name)}</td>
                <td><code>${escHtml(r.code_type_key ?? '—')}</code></td>
                <td>${escHtml(r.version)}</td>
                <td>
                    <span class="covl-currency"><strong>${curSymbol}</strong> ${curName}</span>
                </td>
                <td>
                    ${loaded
                        ? `<span class="covl-badge covl-badge-active">${escHtml(t('rules_loaded'))}</span>`
                        : `<span class="covl-badge covl-badge-inactive">${escHtml(t('rules_pending'))}</span>`}
                </td>
                <td class="text-end covl-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                            data-code="${code}"
                            title="${escHtml(t('reimport_title'))}"
                            onclick="window.__COVL_Countries.reimport('${code}')">
                        <i class="fa-solid fa-arrows-rotate me-1"></i>${escHtml(t('reimport'))}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            data-code="${code}"
                            onclick="window.__COVL_Countries.openInstall('${code}')">
                        <i class="fa-solid fa-rotate me-1"></i>${escHtml(t('update'))}
                    </button>
                </td>
            </tr>`;
        }).join('');
    };

    const loadInstalled = async () => {
        const tbody = document.getElementById('covl-country-tbody');
        if (!tbody) return;
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4">
            <div class="covl-spinner spinner-border text-primary"></div></td></tr>`;
        try {
            const res  = await fetch(apiUrl('country_packs.php', { action: 'list' }));
            const json = await res.json();
            if (json.error) throw new Error(json.error);
            renderInstalled(json.data ?? []);
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="7"><div class="covl-empty">
                <div class="icon">⚠️</div><p>${escHtml(e.message || t('error_loading'))}</p>
            </div></td></tr>`;
        }
    };

    // -----------------------------------------------------------------------
    // Catálogo: modal con búsqueda
    // -----------------------------------------------------------------------
    const loadCatalog = async () => {
        const list = document.getElementById('covl-country-list');
        if (!list) return;
        list.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>`;
        try {
            const res  = await fetch(apiUrl('country_packs.php', { action: 'catalog' }));
            const json = await res.json();
            if (json.error) throw new Error(json.error);
            state.catalog = json.data ?? [];
            renderCatalog();
        } catch (e) {
            list.innerHTML = `<div class="covl-empty"><div class="icon">⚠️</div><p>${escHtml(e.message || t('error_loading'))}</p></div>`;
        }
    };

    const renderCatalog = () => {
        const list = document.getElementById('covl-country-list');
        if (!list) return;

        const q = state.search.toLowerCase().trim();
        const items = state.catalog.filter((p) =>
            !q || p.name.toLowerCase().includes(q) || p.country_code.toLowerCase().includes(q)
        );

        if (!items.length) {
            list.innerHTML = `<div class="covl-empty"><div class="icon">🔍</div><p>${escHtml(t('no_results'))}</p></div>`;
            return;
        }

        list.innerHTML = items.map((p) => {
            const code = escHtml(p.country_code);
            const badge = p.installed
                ? `<span class="covl-badge covl-badge-active">${escHtml(t('installed'))} v${escHtml(p.installed_version)}</span>`
                : `<span class="covl-badge covl-badge-inactive">${escHtml(t('not_installed'))}</span>`;
            const rules = [
                `${p.auth_rules} ${t('auth_rules_short')}`,
                `${p.frequency_rules} ${t('freq_rules_short')}`,
                `${p.code_maps} ${t('code_maps_short')}`,
            ].join(' · ');
            return `<button type="button" class="covl-country-item ${p.installed ? 'is-installed' : ''}"
                    data-code="${escHtml(p.country_code)}"
                    onclick="window.__COVL_Countries.install('${escHtml(p.country_code)}')">
                <span class="covl-country-flag">${flagEmoji(p.country_code)}</span>
                <span class="covl-country-body">
                    <span class="covl-country-title">
                        <strong>${escHtml(p.name)}</strong> ${badge}
                    </span>
                    <span class="covl-country-meta">
                        <code>${escHtml(p.code_type?.ct_key ?? '—')}</code> · ${escHtml(rules)}
                    </span>
                    <span class="covl-country-desc">${escHtml(p.description)}</span>
                </span>
                <span class="covl-country-action">
                    <i class="fa-solid ${p.installed ? 'fa-rotate' : 'fa-plus'}"></i>
                </span>
            </button>`;
        }).join('');
    };

    // -----------------------------------------------------------------------
    // Instalación
    // -----------------------------------------------------------------------
    const install = async (code) => {
        const btn = document.getElementById('btn-country-install');
        if (btn) btn.disabled = true;
        try {
            const res = await fetch(apiUrl('country_packs.php', { action: 'install' }), {
                method: 'POST',
                headers: csrfHeaders(),
                body: JSON.stringify({ country_code: code, csrf_token: covlConfig.csrfToken }),
            });
            const json = await res.json();
            if (!res.ok || json.error) throw new Error(json.error || t('error_install'));
            toast(`${t('country_installed')}: ${code}`, 'success');
            hideModal('covlCountryModal');
            await loadInstalled();
        } catch (e) {
            toast(e.message || t('error_install'), 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    };

    // Reimporta (re-sincroniza) el paquete de un país ya instalado desde packs/*.json
    const reimport = async (code) => {
        if (!window.confirm(`${t('reimport')}: ${code}?`)) return;
        try {
            const res = await fetch(apiUrl('country_packs.php', { action: 'reimport' }), {
                method: 'POST',
                headers: csrfHeaders(),
                body: JSON.stringify({ country_code: code, csrf_token: covlConfig.csrfToken }),
            });
            const json = await res.json();
            if (!res.ok || json.error) throw new Error(json.error || t('error_reimport'));
            toast(`${t('country_reimported')}: ${code}`, 'success');
            await loadInstalled();
        } catch (e) {
            toast(e.message || t('error_reimport'), 'error');
        }
    };

    // -----------------------------------------------------------------------
    // Inicialización
    // -----------------------------------------------------------------------
    const init = () => {
        if (!document.getElementById('covl-country-tbody')) return;

        loadInstalled();

        const openBtn = document.getElementById('btn-country-open');
        if (openBtn) {
            openBtn.addEventListener('click', () => openInstall());
        }

        const search = document.getElementById('flt-country-search');
        if (search) {
            search.addEventListener('input', debounce(() => {
                state.search = search.value;
                renderCatalog();
            }));
        }
    };

    const openInstall = (preloadCode = null) => {
        state.search = '';
        const search = document.getElementById('flt-country-search');
        if (search) search.value = '';
        showModal('covlCountryModal');
        loadCatalog();
        // Soporte de actualización directa desde la tabla: pre-seleccionar país
        if (preloadCode) {
            setTimeout(() => {
                const item = document.querySelector(`#covl-country-list .covl-country-item[data-code="${CSS.escape(preloadCode)}"]`);
                if (item) item.scrollIntoView({ block: 'center' });
            }, 100);
        }
    };

    // Exponer API usada por los botones HTML
    window.__COVL_Countries = { openInstall, install, reimport };

    init();
})();
