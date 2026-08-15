/**
 * oe-module-coverage-latam — providers-crud.js
 *
 * Módulo JS para el CRUD de convenios de prestadores (tab "providers" del dashboard).
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
        filters:      { limit: 50, offset: 0 },
        editingId:    null,
        insurers:     null,
        professionals: null,
        facilities:   null,
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

    const debounce = (fn, ms = 350) => {
        let timer;
        return () => {
            clearTimeout(timer);
            timer = setTimeout(fn, ms);
        };
    };

    const confirmBox = (msg) => window.confirm(msg);

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
    // Carga de catálogos (con caché)
    // -----------------------------------------------------------------------
    const loadInsurers = async () => {
        if (state.insurers) return state.insurers;
        try {
            const res = await fetch(apiUrl('insurers.php'));
            state.insurers = await res.json();
        } catch {
            state.insurers = [];
        }
        return state.insurers;
    };

    const loadProfessionals = async () => {
        if (state.professionals) return state.professionals;
        try {
            const res = await fetch(apiUrl('providers.php', { action: 'professionals' }));
            const json = await res.json();
            state.professionals = json.data ?? [];
        } catch {
            state.professionals = [];
        }
        return state.professionals;
    };

    const loadFacilities = async () => {
        if (state.facilities) return state.facilities;
        try {
            const res = await fetch(apiUrl('providers.php', { action: 'facilities' }));
            const json = await res.json();
            state.facilities = json.data ?? [];
        } catch {
            state.facilities = [];
        }
        return state.facilities;
    };

    const populateSelect = (selectEl, options, selectedValue = '') => {
        if (!selectEl) return;
        selectEl.innerHTML = '';
        options.forEach(opt => {
            const o = document.createElement('option');
            o.value = opt.value;
            o.textContent = opt.label;
            if (String(opt.value) === String(selectedValue)) o.selected = true;
            selectEl.appendChild(o);
        });
    };

    // -----------------------------------------------------------------------
    // Badges
    // -----------------------------------------------------------------------
    const activeBadge = (active) =>
        active == 1
            ? `<span class="covl-badge covl-badge-active">● ${t('active')}</span>`
            : `<span class="covl-badge covl-badge-inactive">○ ${t('inactive')}</span>`;

    const vigenciaBadge = (dateTo) => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (!dateTo) {
            return `<span class="covl-badge covl-badge-active">∞ ${t('no_expiry')}</span>`;
        }
        const end = new Date(dateTo + 'T00:00:00');
        const diffDays = Math.round((end - today) / 86400000);
        if (diffDays < 0) {
            return `<span class="covl-badge covl-badge-expired">${t('expired')}</span>`;
        }
        if (diffDays <= 30) {
            return `<span class="covl-badge covl-badge-expiring">${t('expiring')}</span>`;
        }
        return `<span class="covl-badge covl-badge-current">${t('current')}</span>`;
    };

    // -----------------------------------------------------------------------
    // Tab: Convenios de Prestadores
    // -----------------------------------------------------------------------
    const Prov = {

        tbody:   () => document.getElementById('covl-prov-tbody'),
        pager:   () => document.getElementById('covl-prov-pager'),
        totalEl: () => document.getElementById('covl-prov-total'),

        readFilters() {
            return {
                insurance_company_id: document.getElementById('flt-prov-insurer')?.value ?? '',
                user_id:              document.getElementById('flt-prov-user')?.value    ?? '',
                facility_id:          document.getElementById('flt-prov-facility')?.value ?? '',
                active:               document.getElementById('flt-prov-active')?.value   ?? '',
                search:               document.getElementById('flt-prov-search')?.value   ?? '',
                limit:                state.filters.limit,
                offset:               state.filters.offset,
            };
        },

        async load() {
            const tbody = Prov.tbody();
            if (!tbody) return;
            tbody.innerHTML = `<tr><td colspan="8"><div class="covl-loading"><div class="covl-spinner"></div> ${t('loading')}</div></td></tr>`;

            const filters = Prov.readFilters();
            try {
                const res  = await fetch(apiUrl('providers.php', { action: 'list', ...filters }));
                const json = await res.json();
                if (json.error) throw new Error(json.error);
                Prov.render(json.data ?? [], json.total ?? 0, filters);
            } catch {
                tbody.innerHTML = `<tr><td colspan="8"><div class="covl-empty"><div class="icon">⚠</div><p>${t('error_loading')}</p></div></td></tr>`;
            }
        },

        render(rows, total, filters) {
            const tbody   = Prov.tbody();
            const totalEl = Prov.totalEl();
            if (totalEl) totalEl.textContent = `${total} ${t('records')}`;

            if (rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8"><div class="covl-empty"><div class="icon">🤝</div><p>${t('no_results')}</p></div></td></tr>`;
                Prov.renderPager(total, filters);
                return;
            }

            tbody.innerHTML = rows.map(r => {
                const profName = r.lname
                    ? `${r.lname}, ${r.fname}` + (r.mname ? ` ${r.mname}` : '')
                    : `#${r.user_id}`;
                const facilityLabel = r.facility_id == 0
                    ? `<span class="covl-badge covl-badge-no_requerida">${t('all_facilities')}</span>`
                    : `<span class="covl-badge covl-badge-current">${escHtml(r.facility_name ?? '#')}</span>`;
                return `
                <tr data-id="${r.id}">
                    <td>
                        <strong>${escHtml(profName)}</strong>
                        ${r.user_specialty ? `<br><small class="text-muted">${escHtml(r.user_specialty)}</small>` : ''}
                    </td>
                    <td><small class="text-muted">#${r.insurance_company_id}</small><br>${escHtml(r.insurer_name ?? '—')}</td>
                    <td>${facilityLabel}</td>
                    <td><code>${escHtml(r.provider_number ?? '—')}</code></td>
                    <td>
                        <small>${escHtml(r.date_from)}</small>
                        <small class="text-muted"> → ${r.date_to ? escHtml(r.date_to) : '∞'}</small>
                        <br>${vigenciaBadge(r.date_to)}
                    </td>
                    <td><small>${escHtml(r.specialties ?? '—')}</small></td>
                    <td>${activeBadge(r.active)}</td>
                    <td class="covl-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="window.__COVL_Prov.openEdit(${r.id})" title="${t('edit')}">✏</button>
                        <button class="btn btn-sm ${r.active == 1 ? 'btn-outline-warning' : 'btn-outline-success'}"
                                onclick="window.__COVL_Prov.toggle(${r.id})" title="${r.active == 1 ? t('deactivate') : t('activate')}">
                            ${r.active == 1 ? '⛔' : '✅'}
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="window.__COVL_Prov.del(${r.id})" title="${t('delete')}">🗑</button>
                    </td>
                </tr>`;
            }).join('');

            Prov.renderPager(total, filters);
        },

        renderPager(total, filters) {
            const pager = Prov.pager();
            if (!pager) return;
            const limit   = filters.limit;
            const current = Math.floor(filters.offset / limit);
            const pages   = Math.ceil(total / limit);
            const prev = current > 0
                ? `<button onclick="window.__COVL_Prov.goPage(${current - 1})">‹</button>` : '<button disabled>‹</button>';
            const next = current < pages - 1
                ? `<button onclick="window.__COVL_Prov.goPage(${current + 1})">›</button>` : '<button disabled>›</button>';
            const nums = Array.from({ length: Math.min(pages, 7) }, (_, i) =>
                `<button class="${i === current ? 'current' : ''}" onclick="window.__COVL_Prov.goPage(${i})">${i + 1}</button>`
            ).join('');
            pager.innerHTML = `
                <span>${t('showing')} ${Math.min(filters.offset + 1, total)}–${Math.min(filters.offset + limit, total)} ${t('of')} ${total}</span>
                <div class="page-btns">${prev}${nums}${next}</div>
            `;
        },

        goPage(page) {
            state.filters.offset = page * state.filters.limit;
            Prov.load();
        },

        async openCreate() {
            state.editingId = null;
            document.getElementById('covlProvModalLabel').textContent = t('new_provider');
            const form = document.getElementById('covl-prov-form');
            if (form) form.reset();
            document.getElementById('fld-prov-id').value = '';
            document.getElementById('fld-prov-active').checked = true;

            await loadInsurers();
            await loadProfessionals();
            await loadFacilities();

            populateSelect(document.getElementById('fld-prov-insurer'),
                state.insurers.map(i => ({ value: i.id, label: `[${i.id}] ${i.name}` })), '');
            populateSelect(document.getElementById('fld-prov-user'),
                state.professionals.map(p => ({ value: p.id, label: `${p.fullname} (${p.username ?? '#' + p.id})` })), '');
            populateSelect(document.getElementById('fld-prov-facility'),
                [{ value: 0, label: t('all_facilities') }].concat(
                    state.facilities.map(f => ({ value: f.id, label: f.name }))
                ), 0);

            showModal('covlProvModal');
        },

        async openEdit(id) {
            state.editingId = id;
            document.getElementById('covlProvModalLabel').textContent = t('edit_provider');
            try {
                const res  = await fetch(apiUrl('providers.php', { action: 'get', id }));
                const data = await res.json();
                if (data.error) { toast(data.error, 'error'); return; }

                document.getElementById('fld-prov-id').value          = data.id;
                document.getElementById('fld-prov-number').value      = data.provider_number ?? '';
                document.getElementById('fld-prov-from').value        = data.date_from ?? '';
                document.getElementById('fld-prov-to').value          = data.date_to ?? '';
                document.getElementById('fld-prov-specialties').value = data.specialties ?? '';
                document.getElementById('fld-prov-active').checked    = data.active == 1;
                document.getElementById('fld-prov-notes').value       = data.notes ?? '';

                await loadInsurers();
                await loadProfessionals();
                await loadFacilities();

                populateSelect(document.getElementById('fld-prov-insurer'),
                    state.insurers.map(i => ({ value: i.id, label: `[${i.id}] ${i.name}` })), data.insurance_company_id);
                populateSelect(document.getElementById('fld-prov-user'),
                    state.professionals.map(p => ({ value: p.id, label: `${p.fullname} (${p.username ?? '#' + p.id})` })), data.user_id);
                populateSelect(document.getElementById('fld-prov-facility'),
                    [{ value: 0, label: t('all_facilities') }].concat(
                        state.facilities.map(f => ({ value: f.id, label: f.name }))
                    ), data.facility_id);

                showModal('covlProvModal');
            } catch {
                toast(t('error_fetch'), 'error');
            }
        },

        async save() {
            const id = document.getElementById('fld-prov-id').value;

            const userEl  = document.getElementById('fld-prov-user');
            const insEl   = document.getElementById('fld-prov-insurer');
            const fromEl  = document.getElementById('fld-prov-from');

            if (!userEl.value || !insEl.value || !fromEl.value) {
                toast(t('required_fields'), 'error');
                return;
            }

            const data = {
                csrf_token:           covlConfig.csrfToken,
                user_id:              userEl.value,
                insurance_company_id: insEl.value,
                facility_id:          document.getElementById('fld-prov-facility').value || 0,
                provider_number:      document.getElementById('fld-prov-number').value || '',
                date_from:            fromEl.value,
                date_to:              document.getElementById('fld-prov-to').value || '',
                specialties:          document.getElementById('fld-prov-specialties').value || '',
                notes:                document.getElementById('fld-prov-notes').value || '',
                active:               document.getElementById('fld-prov-active').checked ? 1 : 0,
            };

            const isEdit = !!id;
            const url    = isEdit
                ? apiUrl('providers.php', { action: 'update', id })
                : apiUrl('providers.php', { action: 'create' });

            try {
                const res  = await fetch(url, { method: 'POST', headers: csrfHeaders(), body: JSON.stringify(data) });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                hideModal('covlProvModal');
                toast(isEdit ? t('updated') : t('created'), 'success');
                Prov.load();
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        async toggle(id) {
            try {
                const res  = await fetch(apiUrl('providers.php', { action: 'toggle', id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify({ csrf_token: covlConfig.csrfToken }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(json.active ? t('activated') : t('deactivated'), 'info');
                Prov.load();
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        async del(id) {
            if (!confirmBox(t('confirm_delete'))) return;
            try {
                const res  = await fetch(apiUrl('providers.php', { action: 'delete', id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify({ csrf_token: covlConfig.csrfToken }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(t('deleted'), 'success');
                Prov.load();
            } catch {
                toast(t('error_save'), 'error');
            }
        },
    };

    // API pública global (para atributos onclick inline)
    window.__COVL_Prov = { openEdit: Prov.openEdit, toggle: Prov.toggle, del: Prov.del, goPage: Prov.goPage };

    // -----------------------------------------------------------------------
    // Inicialización
    // -----------------------------------------------------------------------
    const init = () => {
        if (!document.getElementById('covl-prov-tbody')) return;
        Prov.load();

        const bindFilters = () => {
            const els = document.querySelectorAll('[data-covl-filter-prov]');
            const onFilter = debounce(Prov.load);
            els.forEach(el => {
                el.addEventListener('input', onFilter);
                el.addEventListener('change', onFilter);
            });
        };
        bindFilters();

        const form = document.getElementById('covl-prov-form');
        if (form) {
            form.addEventListener('submit', (e) => { e.preventDefault(); Prov.save(); });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
