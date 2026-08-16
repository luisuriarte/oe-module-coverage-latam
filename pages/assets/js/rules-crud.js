/**
 * oe-module-coverage-latam — rules-crud.js
 *
 * Módulo JS para el CRUD de reglas de autorización y frecuencia.
 * Sin dependencias externas — usa jQuery (ya cargado por OpenEMR) y fetch().
 * Incluye soporte para el componente FlagSelect con iconos de lipis/flag-icons.
 *
 * @author    Luis A. Uriarte <luis.uriarte@gmail.com>
 * @copyright Copyright (c) 2026 Luis A. Uriarte
 * @license   GPL-3.0
 */

/* global covlConfig, $ */

'use strict';

// ---------------------------------------------------------------------------
// Configuración inyectada desde PHP (ver rules.php)
// covlConfig = { csrfToken, baseApiUrl, basePageUrl, countryPacks }
// ---------------------------------------------------------------------------
const COVL = (() => {

    // -----------------------------------------------------------------------
    // Estado compartido
    // -----------------------------------------------------------------------
    let state = {
        activeTab:     'auth',        // 'auth' | 'freq' | 'providers'
        activeCountry: '',           // 'AR' | 'CL' | etc. | '' = todos
        authFilters:   { limit: 50, offset: 0 },
        freqFilters:   { limit: 50, offset: 0 },
        editingId:     null,          // null = crear nuevo
        insurerCache:  null,          // Cache de financiadores
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
            el.innerHTML = `<span>${icons[type] ?? 'ℹ'}</span><span>${message}</span>`;
            ensure().appendChild(el);
            setTimeout(() => el.remove(), durationMs);
        };
    })();

    // -----------------------------------------------------------------------
    // FlagSelect Component Manager
    // -----------------------------------------------------------------------
    const FlagSelect = {
        init() {
            document.querySelectorAll('.covl-flag-select').forEach(wrapper => {
                if (wrapper.dataset.fsInitialized) return;
                wrapper.dataset.fsInitialized = '1';

                const trigger = wrapper.querySelector('.fs-trigger');
                const dropdown = wrapper.querySelector('.fs-dropdown');
                const inputId = wrapper.dataset.inputId;
                const hiddenInput = document.getElementById(inputId);

                // Toggle dropdown
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const isOpen = wrapper.classList.contains('open');
                    FlagSelect.closeAll();
                    if (!isOpen) {
                        wrapper.classList.add('open');
                        trigger.setAttribute('aria-expanded', 'true');
                    }
                });

                // Option click
                dropdown.querySelectorAll('.fs-option').forEach(opt => {
                    opt.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const val = opt.dataset.value ?? '';
                        const code = opt.dataset.code ?? '';
                        const label = opt.querySelector('.fs-option-label')?.textContent ?? '';

                        FlagSelect.setValue(inputId, val, code, label);
                        wrapper.classList.remove('open');
                        trigger.setAttribute('aria-expanded', 'false');

                        // Disparar input event para debounce de filtros
                        if (hiddenInput) {
                            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                });
            });

            // Cerrar al clickear afuera
            document.addEventListener('click', () => FlagSelect.closeAll());
        },

        closeAll() {
            document.querySelectorAll('.covl-flag-select.open').forEach(w => {
                w.classList.remove('open');
                w.querySelector('.fs-trigger')?.setAttribute('aria-expanded', 'false');
            });
        },

        setValue(inputId, val, code = null, label = null) {
            const wrapper = document.querySelector(`.covl-flag-select[data-input-id="${inputId}"]`);
            const hiddenInput = document.getElementById(inputId);
            if (hiddenInput) hiddenInput.value = val;
            if (!wrapper) return;

            // Si no se pasaron code o label, buscarlos en las opciones
            if (code === null || label === null) {
                const opt = wrapper.querySelector(`.fs-option[data-value="${val}"]`);
                if (opt) {
                    code = opt.dataset.code ?? '';
                    label = opt.querySelector('.fs-option-label')?.textContent ?? '';
                } else {
                    code = val;
                    label = val || '— Todos los países —';
                }
            }

            // Actualizar opciones seleccionadas
            wrapper.querySelectorAll('.fs-option').forEach(o => {
                o.classList.toggle('selected', o.dataset.value === String(val));
            });

            // Actualizar trigger
            const flagEl = wrapper.querySelector('.fs-flag');
            const labelEl = wrapper.querySelector('.fs-label');
            if (flagEl) {
                flagEl.className = code ? `fs-flag fi fi-${code.toLowerCase()}` : 'fs-flag fi';
            }
            if (labelEl) {
                labelEl.textContent = label;
                labelEl.classList.remove('text-muted');
            }
        }
    };

    // -----------------------------------------------------------------------
    // Carga de financiadores (con caché)
    // -----------------------------------------------------------------------
    const loadInsurers = async () => {
        if (state.insurerCache) return state.insurerCache;
        try {
            const res = await fetch(apiUrl('insurers.php'));
            const json = await res.json();
            if (json.error) {
                console.error('insurers.php:', json.error);
                return [{ id: 0, name: t('error_loading') }];
            }
            state.insurerCache = json;
        } catch {
            state.insurerCache = [{ id: 0, name: '— Error al cargar —' }];
        }
        return state.insurerCache;
    };

    const populateInsurerSelect = async (selectEl, selectedId = 0) => {
        const insurers = await loadInsurers();
        selectEl.innerHTML = '';
        insurers.forEach(ins => {
            const opt = document.createElement('option');
            opt.value = ins.id;
            opt.textContent = ins.id === 0 ? ins.name : `[${ins.id}] ${ins.name}`;
            if (parseInt(ins.id) === parseInt(selectedId)) opt.selected = true;
            selectEl.appendChild(opt);
        });
    };

    // -----------------------------------------------------------------------
    // Badges helpers
    // -----------------------------------------------------------------------
    const authModeBadge = (mode) => {
        const map = {
            automatica:   ['covl-badge-automatica',   '✓ Automática'],
            requerida:    ['covl-badge-requerida',    '⚑ Requerida'],
            no_requerida: ['covl-badge-no_requerida', '— No requerida'],
        };
        const [cls, label] = map[mode] ?? ['covl-badge-requerida', mode];
        return `<span class="covl-badge ${cls}">${label}</span>`;
    };

    const severityBadge = (sev) => {
        const map = {
            alerta:  ['covl-badge-alerta',  '⚠ Alerta'],
            bloqueo: ['covl-badge-bloqueo', '🚫 Bloqueo'],
        };
        const [cls, label] = map[sev] ?? ['covl-badge-alerta', sev];
        return `<span class="covl-badge ${cls}">${label}</span>`;
    };

    const activeBadge = (active) =>
        active == 1
            ? '<span class="covl-badge covl-badge-active">● Activa</span>'
            : '<span class="covl-badge covl-badge-inactive">○ Inactiva</span>';

    const countryBadge = (code) =>
        code
            ? `<span class="covl-badge-country"><span class="fi fi-${code.toLowerCase()}"></span><span>${code}</span></span>`
            : '<span class="covl-badge-country" style="opacity:.4">—</span>';

    // -----------------------------------------------------------------------
    // Confirmación de eliminación
    // -----------------------------------------------------------------------
    const confirmDelete = (message) => new Promise((resolve) => {
        if (window.confirm(message)) resolve(true);
        else resolve(false);
    });

    // -----------------------------------------------------------------------
    // ===== TAB: REGLAS DE AUTORIZACIÓN =====
    // -----------------------------------------------------------------------
    const Auth = {

        tableBody: () => document.getElementById('covl-auth-tbody'),
        pager:     () => document.getElementById('covl-auth-pager'),
        totalEl:   () => document.getElementById('covl-auth-total'),
        modal:     () => document.getElementById('covlAuthModal'),

        // Leer filtros del formulario de la barra
        readFilters() {
            return {
                country_code:         document.getElementById('flt-auth-country')?.value  ?? '',
                insurance_company_id: document.getElementById('flt-auth-insurer')?.value  ?? '',
                code_type:            document.getElementById('flt-auth-codetype')?.value ?? '',
                code:                 document.getElementById('flt-auth-code')?.value     ?? '',
                active:               document.getElementById('flt-auth-active')?.value   ?? '',
                limit:                state.authFilters.limit,
                offset:               state.authFilters.offset,
            };
        },

        async load() {
            const tbody = Auth.tableBody();
            if (!tbody) return;
            tbody.innerHTML = '<tr><td colspan="9"><div class="covl-loading"><div class="covl-spinner"></div> Cargando...</div></td></tr>';

            const filters = Auth.readFilters();
            try {
                const res  = await fetch(apiUrl('auth_rules.php', { action: 'list', ...filters }));
                const json = await res.json();
                Auth.render(json.data ?? [], json.total ?? 0, filters);
            } catch {
                tbody.innerHTML = '<tr><td colspan="9"><div class="covl-empty"><div class="icon">⚠</div><p>Error al cargar los datos</p></div></td></tr>';
            }
        },

        render(rows, total, filters) {
            const tbody = Auth.tableBody();
            const totalEl = Auth.totalEl();
            if (totalEl) totalEl.textContent = `${total} regla${total !== 1 ? 's' : ''}`;

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9"><div class="covl-empty"><div class="icon">📋</div><p>No hay reglas que coincidan con los filtros</p></div></td></tr>';
                Auth.renderPager(total, filters);
                return;
            }

            tbody.innerHTML = rows.map(r => `
                <tr data-id="${r.id}">
                    <td>${countryBadge(r.country_code)}</td>
                    <td><small class="text-muted">#${r.insurance_company_id}</small><br>${escHtml(r.insurer_name ?? '—')}</td>
                    <td><code>${escHtml(r.plan_pattern === '0' ? '(todos)' : r.plan_pattern)}</code></td>
                    <td><code>${escHtml(r.code_type)}</code></td>
                    <td><code>${escHtml(r.code === '0' ? '(todos)' : r.code)}</code></td>
                    <td>${authModeBadge(r.auth_mode)}</td>
                    <td class="text-center">${r.max_quantity !== null ? escHtml(r.max_quantity) : '<span class="text-muted">—</span>'}</td>
                    <td>${activeBadge(r.active)}</td>
                    <td class="covl-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="COVL.Auth.openEdit(${r.id})" title="Editar">✏</button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="COVL.Auth.clone(${r.id})" title="Clonar">📋</button>
                        <button class="btn btn-sm ${r.active == 1 ? 'btn-outline-warning' : 'btn-outline-success'}"
                                onclick="COVL.Auth.toggle(${r.id})" title="${r.active == 1 ? 'Desactivar' : 'Activar'}">
                            ${r.active == 1 ? '⛔' : '✅'}
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="COVL.Auth.del(${r.id})" title="Eliminar">🗑</button>
                    </td>
                </tr>
            `).join('');

            Auth.renderPager(total, filters);
        },

        renderPager(total, filters) {
            const pager = Auth.pager();
            if (!pager) return;
            const limit   = filters.limit;
            const current = Math.floor(filters.offset / limit);
            const pages   = Math.ceil(total / limit);

            const prev = current > 0
                ? `<button onclick="COVL.Auth.goPage(${current - 1})">‹</button>` : '<button disabled>‹</button>';
            const next = current < pages - 1
                ? `<button onclick="COVL.Auth.goPage(${current + 1})">›</button>` : '<button disabled>›</button>';
            const nums = Array.from({ length: Math.min(pages, 7) }, (_, i) =>
                `<button class="${i === current ? 'current' : ''}" onclick="COVL.Auth.goPage(${i})">${i + 1}</button>`
            ).join('');

            pager.innerHTML = `
                <span>Mostrando ${Math.min(filters.offset + 1, total)}–${Math.min(filters.offset + limit, total)} de ${total}</span>
                <div class="page-btns">${prev}${nums}${next}</div>
            `;
        },

        goPage(page) {
            state.authFilters.offset = page * state.authFilters.limit;
            Auth.load();
        },

        async openCreate() {
            state.editingId = null;
            document.getElementById('covlAuthModalLabel').textContent = 'Nueva Regla de Autorización';
            document.getElementById('covl-auth-form').reset();
            document.getElementById('fld-auth-id').value = '';
            FlagSelect.setValue('fld-auth-country', state.activeCountry || '');
            Auth.toggleMaxQtyField();
            openModal('covlAuthModal');
            try {
                await populateInsurerSelect(document.getElementById('fld-auth-insurer'), 0);
            } catch {
                toast('No se pudieron cargar los financiadores', 'error');
            }
        },

        async openEdit(id) {
            state.editingId = id;
            document.getElementById('covlAuthModalLabel').textContent = 'Editar Regla de Autorización';
            try {
                const res  = await fetch(apiUrl('auth_rules.php', { action: 'get', id }));
                const data = await res.json();
                if (data.error) { toast(data.error, 'error'); return; }

                document.getElementById('fld-auth-id').value               = data.id;
                FlagSelect.setValue('fld-auth-country', data.country_code ?? '');
                document.getElementById('fld-auth-codetype').value         = data.code_type ?? '';
                document.getElementById('fld-auth-code').value             = data.code === '0' ? '' : (data.code ?? '');
                document.getElementById('fld-auth-plan-pattern').value     = data.plan_pattern === '0' ? '' : (data.plan_pattern ?? '');
                document.getElementById('fld-auth-mode').value             = data.auth_mode ?? 'requerida';
                document.getElementById('fld-auth-max-qty').value          = data.max_quantity ?? '';
                document.getElementById('fld-auth-priority').value         = data.priority ?? 100;
                document.getElementById('fld-auth-notes').value            = data.notes ?? '';
                document.getElementById('fld-auth-active').checked         = data.active == 1;

                await populateInsurerSelect(document.getElementById('fld-auth-insurer'), data.insurance_company_id);
                Auth.toggleMaxQtyField();
                openModal('covlAuthModal');
            } catch {
                toast('Error al cargar la regla', 'error');
            }
        },

        async clone(id) {
            await Auth.openEdit(id);
            // Al abrir en modo clone, limpiar el ID para que se cree una nueva
            state.editingId = null;
            document.getElementById('fld-auth-id').value = '';
            document.getElementById('covlAuthModalLabel').textContent = 'Clonar Regla de Autorización (nueva)';
        },

        toggleMaxQtyField() {
            const mode  = document.getElementById('fld-auth-mode')?.value;
            const group = document.getElementById('grp-auth-max-qty');
            if (group) group.style.display = mode === 'automatica' ? '' : 'none';
        },

        async save() {
            const id   = document.getElementById('fld-auth-id').value;
            const countryVal = document.getElementById('fld-auth-country').value;
            if (!countryVal) {
                toast('Debes seleccionar un país', 'error');
                return;
            }
            const data = {
                csrf_token:            covlConfig.csrfToken,
                country_code:          countryVal,
                insurance_company_id:  document.getElementById('fld-auth-insurer').value,
                plan_pattern:          document.getElementById('fld-auth-plan-pattern').value || '0',
                code_type:             document.getElementById('fld-auth-codetype').value,
                code:                  document.getElementById('fld-auth-code').value || '0',
                auth_mode:             document.getElementById('fld-auth-mode').value,
                max_quantity:          document.getElementById('fld-auth-max-qty').value,
                priority:              document.getElementById('fld-auth-priority').value,
                notes:                 document.getElementById('fld-auth-notes').value,
                active:                document.getElementById('fld-auth-active').checked ? 1 : 0,
            };

            const isEdit = !!id;
            const url    = isEdit
                ? apiUrl('auth_rules.php', { action: 'update', id })
                : apiUrl('auth_rules.php', { action: 'create' });

            try {
                const res  = await fetch(url, { method: 'POST', headers: csrfHeaders(), body: JSON.stringify(data) });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                closeModal('covlAuthModal');
                toast(isEdit ? 'Regla actualizada correctamente' : 'Regla creada correctamente', 'success');
                Auth.load();
            } catch {
                toast('Error al guardar la regla', 'error');
            }
        },

        async toggle(id) {
            try {
                const res  = await fetch(apiUrl('auth_rules.php', { action: 'toggle', id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify({ csrf_token: covlConfig.csrfToken }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(json.active ? 'Regla activada' : 'Regla desactivada', 'info');
                Auth.load();
            } catch {
                toast('Error al cambiar estado', 'error');
            }
        },

        async del(id) {
            if (!await confirmDelete('¿Eliminar esta regla de autorización? Esta acción no se puede deshacer.')) return;
            try {
                const res  = await fetch(apiUrl('auth_rules.php', { action: 'delete', id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify({ csrf_token: covlConfig.csrfToken }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast('Regla eliminada', 'success');
                Auth.load();
            } catch {
                toast('Error al eliminar', 'error');
            }
        },
    };

    // -----------------------------------------------------------------------
    // ===== TAB: REGLAS DE FRECUENCIA =====
    // -----------------------------------------------------------------------
    const Freq = {

        tableBody: () => document.getElementById('covl-freq-tbody'),
        pager:     () => document.getElementById('covl-freq-pager'),
        totalEl:   () => document.getElementById('covl-freq-total'),

        readFilters() {
            return {
                country_code:         document.getElementById('flt-freq-country')?.value  ?? '',
                insurance_company_id: document.getElementById('flt-freq-insurer')?.value  ?? '',
                code_type:            document.getElementById('flt-freq-codetype')?.value ?? '',
                code:                 document.getElementById('flt-freq-code')?.value     ?? '',
                severity:             document.getElementById('flt-freq-severity')?.value ?? '',
                active:               document.getElementById('flt-freq-active')?.value   ?? '',
                limit:                state.freqFilters.limit,
                offset:               state.freqFilters.offset,
            };
        },

        async load() {
            const tbody = Freq.tableBody();
            if (!tbody) return;
            tbody.innerHTML = '<tr><td colspan="9"><div class="covl-loading"><div class="covl-spinner"></div> Cargando...</div></td></tr>';

            const filters = Freq.readFilters();
            try {
                const res  = await fetch(apiUrl('frequency_rules.php', { action: 'list', ...filters }));
                const json = await res.json();
                Freq.render(json.data ?? [], json.total ?? 0, filters);
            } catch {
                tbody.innerHTML = '<tr><td colspan="9"><div class="covl-empty"><div class="icon">⚠</div><p>Error al cargar los datos</p></div></td></tr>';
            }
        },

        daysToHuman(days) {
            if (days >= 365 && days % 365 === 0) return `${days / 365} año${days / 365 > 1 ? 's' : ''}`;
            if (days >= 30  && days % 30  === 0) return `${days / 30} mes${days / 30 > 1 ? 'es' : ''}`;
            return `${days} días`;
        },

        render(rows, total, filters) {
            const tbody   = Freq.tableBody();
            const totalEl = Freq.totalEl();
            if (totalEl) totalEl.textContent = `${total} regla${total !== 1 ? 's' : ''}`;

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9"><div class="covl-empty"><div class="icon">⏱</div><p>No hay reglas de frecuencia que coincidan</p></div></td></tr>';
                Freq.renderPager(total, filters);
                return;
            }

            tbody.innerHTML = rows.map(r => `
                <tr data-id="${r.id}">
                    <td>${countryBadge(r.country_code)}</td>
                    <td><small class="text-muted">#${r.insurance_company_id}</small><br>${escHtml(r.insurer_name ?? '—')}</td>
                    <td><code>${escHtml(r.code_type)}</code></td>
                    <td><code>${escHtml(r.code)}</code></td>
                    <td class="text-center"><strong>${escHtml(r.min_interval_days)}</strong> días<br><small class="text-muted">(${Freq.daysToHuman(parseInt(r.min_interval_days))})</small></td>
                    <td class="text-center">${r.max_per_year !== null ? escHtml(r.max_per_year) + '/año' : '<span class="text-muted">—</span>'}</td>
                    <td>${severityBadge(r.severity)}</td>
                    <td>${activeBadge(r.active)}</td>
                    <td class="covl-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="COVL.Freq.openEdit(${r.id})" title="Editar">✏</button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="COVL.Freq.clone(${r.id})" title="Clonar">📋</button>
                        <button class="btn btn-sm ${r.active == 1 ? 'btn-outline-warning' : 'btn-outline-success'}"
                                onclick="COVL.Freq.toggle(${r.id})" title="${r.active == 1 ? 'Desactivar' : 'Activar'}">
                            ${r.active == 1 ? '⛔' : '✅'}
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="COVL.Freq.del(${r.id})" title="Eliminar">🗑</button>
                    </td>
                </tr>
            `).join('');

            Freq.renderPager(total, filters);
        },

        renderPager(total, filters) {
            const pager = Freq.pager();
            if (!pager) return;
            const limit   = filters.limit;
            const current = Math.floor(filters.offset / limit);
            const pages   = Math.ceil(total / limit);
            const prev    = current > 0
                ? `<button onclick="COVL.Freq.goPage(${current - 1})">‹</button>` : '<button disabled>‹</button>';
            const next    = current < pages - 1
                ? `<button onclick="COVL.Freq.goPage(${current + 1})">›</button>` : '<button disabled>›</button>';
            const nums    = Array.from({ length: Math.min(pages, 7) }, (_, i) =>
                `<button class="${i === current ? 'current' : ''}" onclick="COVL.Freq.goPage(${i})">${i + 1}</button>`
            ).join('');
            pager.innerHTML = `
                <span>Mostrando ${Math.min(filters.offset + 1, total)}–${Math.min(filters.offset + limit, total)} de ${total}</span>
                <div class="page-btns">${prev}${nums}${next}</div>
            `;
        },

        goPage(page) {
            state.freqFilters.offset = page * state.freqFilters.limit;
            Freq.load();
        },

        async openCreate() {
            state.editingId = null;
            document.getElementById('covlFreqModalLabel').textContent = 'Nueva Regla de Frecuencia';
            document.getElementById('covl-freq-form').reset();
            document.getElementById('fld-freq-id').value = '';
            document.getElementById('fld-freq-severity-alerta').checked = true;
            FlagSelect.setValue('fld-freq-country', state.activeCountry || '');
            Freq.updateIntervalHint();
            openModal('covlFreqModal');
            try {
                await populateInsurerSelect(document.getElementById('fld-freq-insurer'), 0);
            } catch {
                toast('No se pudieron cargar los financiadores', 'error');
            }
        },

        async openEdit(id) {
            state.editingId = id;
            document.getElementById('covlFreqModalLabel').textContent = 'Editar Regla de Frecuencia';
            try {
                const res  = await fetch(apiUrl('frequency_rules.php', { action: 'get', id }));
                const data = await res.json();
                if (data.error) { toast(data.error, 'error'); return; }

                document.getElementById('fld-freq-id').value           = data.id;
                FlagSelect.setValue('fld-freq-country', data.country_code ?? '');
                document.getElementById('fld-freq-codetype').value     = data.code_type ?? '';
                document.getElementById('fld-freq-code').value         = data.code ?? '';
                document.getElementById('fld-freq-interval').value     = data.min_interval_days ?? '';
                document.getElementById('fld-freq-max-year').value     = data.max_per_year ?? '';
                document.getElementById('fld-freq-notes').value        = data.notes ?? '';
                document.getElementById('fld-freq-active').checked     = data.active == 1;

                const sevEl = document.getElementById(`fld-freq-severity-${data.severity ?? 'alerta'}`);
                if (sevEl) sevEl.checked = true;

                await populateInsurerSelect(document.getElementById('fld-freq-insurer'), data.insurance_company_id);
                Freq.updateIntervalHint();
                openModal('covlFreqModal');
            } catch {
                toast('Error al cargar la regla', 'error');
            }
        },

        async clone(id) {
            await Freq.openEdit(id);
            state.editingId = null;
            document.getElementById('fld-freq-id').value = '';
            document.getElementById('covlFreqModalLabel').textContent = 'Clonar Regla de Frecuencia (nueva)';
        },

        updateIntervalHint() {
            const days    = parseInt(document.getElementById('fld-freq-interval')?.value ?? '0');
            const hintEl  = document.getElementById('freq-interval-hint');
            if (!hintEl) return;
            if (!days) { hintEl.textContent = ''; return; }
            if (days >= 365 && days % 365 === 0) hintEl.textContent = `= ${days / 365} año(s)`;
            else if (days >= 30 && days % 30 === 0) hintEl.textContent = `= ${days / 30} mes(es) aprox.`;
            else hintEl.textContent = `${days} días`;
        },

        async save() {
            const id  = document.getElementById('fld-freq-id').value;
            const countryVal = document.getElementById('fld-freq-country').value;
            if (!countryVal) {
                toast('Debes seleccionar un país', 'error');
                return;
            }
            const sev = document.querySelector('input[name="fld-freq-severity"]:checked')?.value ?? 'alerta';
            const data = {
                csrf_token:            covlConfig.csrfToken,
                country_code:          countryVal,
                insurance_company_id:  document.getElementById('fld-freq-insurer').value,
                code_type:             document.getElementById('fld-freq-codetype').value,
                code:                  document.getElementById('fld-freq-code').value,
                min_interval_days:     document.getElementById('fld-freq-interval').value,
                max_per_year:          document.getElementById('fld-freq-max-year').value,
                severity:              sev,
                notes:                 document.getElementById('fld-freq-notes').value,
                active:                document.getElementById('fld-freq-active').checked ? 1 : 0,
            };
            const isEdit = !!id;
            const url    = isEdit
                ? apiUrl('frequency_rules.php', { action: 'update', id })
                : apiUrl('frequency_rules.php', { action: 'create' });
            try {
                const res  = await fetch(url, { method: 'POST', headers: csrfHeaders(), body: JSON.stringify(data) });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                closeModal('covlFreqModal');
                toast(isEdit ? 'Regla actualizada' : 'Regla creada', 'success');
                Freq.load();
            } catch {
                toast('Error al guardar', 'error');
            }
        },

        async toggle(id) {
            try {
                const res  = await fetch(apiUrl('frequency_rules.php', { action: 'toggle', id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify({ csrf_token: covlConfig.csrfToken }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(json.active ? 'Regla activada' : 'Regla desactivada', 'info');
                Freq.load();
            } catch {
                toast('Error al cambiar estado', 'error');
            }
        },

        async del(id) {
            if (!await confirmDelete('¿Eliminar esta regla de frecuencia?')) return;
            try {
                const res  = await fetch(apiUrl('frequency_rules.php', { action: 'delete', id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify({ csrf_token: covlConfig.csrfToken }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast('Regla eliminada', 'success');
                Freq.load();
            } catch {
                toast('Error al eliminar', 'error');
            }
        },
    };

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    const escHtml = (str) => String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    // -----------------------------------------------------------------------
    // Inicialización
    // -----------------------------------------------------------------------
    const init = () => {
        // Inicializar componentes FlagSelect
        FlagSelect.init();

        // Detectar qué tab está presente en la página (rules.php usa tabs internas;
        // dashboard.php renderiza una sola tab por URL).
        const hasAuth = !!document.getElementById('covl-auth-tbody');
        const hasFreq = !!document.getElementById('covl-freq-tbody');
        if (hasAuth && !hasFreq) state.activeTab = 'auth';
        if (hasFreq && !hasAuth) state.activeTab = 'freq';

        // Cargar la(s) tabla(s) presente(s) al iniciar
        if (hasAuth) Auth.load();
        if (hasFreq) Freq.load();

        // Tab switch (solo en rules.php con tabs internas)
        document.querySelectorAll('[data-covl-tab]').forEach(el => {
            el.addEventListener('click', () => {
                state.activeTab = el.dataset.covlTab;
                if (state.activeTab === 'auth') Auth.load();
                if (state.activeTab === 'freq') Freq.load();
            });
        });

        // Filtros con debounce
        let filterTimer;
        const onFilterChange = (tabFn) => () => {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(tabFn, 350);
        };
        document.querySelectorAll('[data-covl-filter-auth]').forEach(el =>
            el.addEventListener('input', onFilterChange(Auth.load)));
        document.querySelectorAll('[data-covl-filter-freq]').forEach(el =>
            el.addEventListener('input', onFilterChange(Freq.load)));

        // Selector de país (pills superiores)
        document.querySelectorAll('[data-covl-country]').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                const country = el.dataset.covlCountry;
                state.activeCountry = country;
                document.querySelectorAll('[data-covl-country]').forEach(p =>
                    p.classList.toggle('active', p.dataset.covlCountry === country)
                );

                // Sincronizar componentes FlagSelect de los filtros
                FlagSelect.setValue('flt-auth-country', country);
                FlagSelect.setValue('flt-freq-country', country);

                if (hasAuth) Auth.load();
                if (hasFreq) Freq.load();
            });
        });

        // Toggle max_qty en form de auth
        document.getElementById('fld-auth-mode')?.addEventListener('change', Auth.toggleMaxQtyField);

        // Hint de intervalo en form de frecuencia
        document.getElementById('fld-freq-interval')?.addEventListener('input', Freq.updateIntervalHint);
    };

    // API pública
    return { init, Auth, Freq, FlagSelect, toast };

})(); // fin COVL IIFE

document.addEventListener('DOMContentLoaded', COVL.init);
