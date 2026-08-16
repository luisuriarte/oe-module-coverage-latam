/**
 * oe-module-coverage-latam — batches-crud.js
 *
 * Módulo JS para el CRUD de lotes de liquidación (tab "batches" del dashboard):
 * listado, alta/edición, gestión de ítems (prestaciones) y transiciones de estado.
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
        filters:   { limit: 50, offset: 0 },
        editingId: null,
        currentBatch: null,   // lote activo en el modal de ítems
        insurers:  null,
        facilities: null,
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

    const fmtMoney = (n, currency = '') => {
        const num = parseFloat(n || 0);
        const str = num.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return currency ? `${str} ${escHtml(currency)}` : str;
    };

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

    const loadFacilities = async () => {
        if (state.facilities) return state.facilities;
        try {
            const res = await fetch(apiUrl('batches.php', { action: 'facilities' }));
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
    // Badges de estado
    // -----------------------------------------------------------------------
    const statusBadge = (status) => {
        const map = {
            borrador:      ['covl-badge-borrador',      t('status_borrador')],
            armado:        ['covl-badge-armado',        t('status_armado')],
            presentado:    ['covl-badge-presentado',    t('status_presentado')],
            pagado_parcial:['covl-badge-pagado_parcial', t('status_pagado_parcial')],
            pagado_total:  ['covl-badge-pagado_total',  t('status_pagado_total')],
            en_disputa:    ['covl-badge-en_disputa',    t('status_en_disputa')],
            anulado:       ['covl-badge-anulado',       t('status_anulado')],
        };
        const [cls, label] = map[status] ?? ['covl-badge-borrador', status];
        return `<span class="covl-badge ${cls}">${escHtml(label)}</span>`;
    };

    const itemStatusBadge = (status) => {
        const map = {
            incluido:  ['covl-badge-automatica', t('item_incluido')],
            aprobado:  ['covl-badge-active',     t('item_aprobado')],
            rechazado: ['covl-badge-bloqueo',    t('item_rechazado')],
            debitado:  ['covl-badge-requerida',  t('item_debitado')],
        };
        const [cls, label] = map[status] ?? ['covl-badge-borrador', status];
        return `<span class="covl-badge ${cls}">${escHtml(label)}</span>`;
    };

    // -----------------------------------------------------------------------
    // Tab: Lotes de Liquidación
    // -----------------------------------------------------------------------
    const Batch = {

        tbody:   () => document.getElementById('covl-batch-tbody'),
        pager:   () => document.getElementById('covl-batch-pager'),
        totalEl: () => document.getElementById('covl-batch-total'),

        readFilters() {
            return {
                insurance_company_id: document.getElementById('flt-batch-insurer')?.value ?? '',
                facility_id:          document.getElementById('flt-batch-facility')?.value ?? '',
                status:               document.getElementById('flt-batch-status')?.value   ?? '',
                period_from:          document.getElementById('flt-batch-from')?.value     ?? '',
                period_to:            document.getElementById('flt-batch-to')?.value       ?? '',
                search:               document.getElementById('flt-batch-search')?.value   ?? '',
                limit:                state.filters.limit,
                offset:               state.filters.offset,
            };
        },

        async load() {
            const tbody = Batch.tbody();
            if (!tbody) return;
            tbody.innerHTML = `<tr><td colspan="8"><div class="covl-loading"><div class="covl-spinner"></div> ${t('loading')}</div></td></tr>`;

            const filters = Batch.readFilters();
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'list', ...filters }));
                const json = await res.json();
                Batch.render(json.data ?? [], json.total ?? 0, filters);
            } catch {
                tbody.innerHTML = `<tr><td colspan="8"><div class="covl-empty"><div class="icon">⚠</div><p>${t('error_loading')}</p></div></td></tr>`;
            }
        },

        render(rows, total, filters) {
            const tbody   = Batch.tbody();
            const totalEl = Batch.totalEl();
            if (totalEl) totalEl.textContent = `${total} ${t('records')}`;

            if (rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8"><div class="covl-empty"><div class="icon">📦</div><p>${t('no_results')}</p></div></td></tr>`;
                Batch.renderPager(total, filters);
                return;
            }

            tbody.innerHTML = rows.map(r => {
                const paid = r.paid_amount !== null ? fmtMoney(r.paid_amount, r.currency) : '—';
                const status = r.status;
                const actions = `
                    <button class="btn btn-sm btn-outline-primary" onclick="window.__COVL_Batch.viewItems(${r.id})" title="${t('items')}">📋</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.__COVL_Batch.openEdit(${r.id})" title="${t('edit')}">✏</button>
                    ${['borrador', 'armado'].includes(status)
                        ? `<button class="btn btn-sm btn-outline-danger" onclick="window.__COVL_Batch.del(${r.id})" title="${t('delete')}">🗑</button>`
                        : ''}
                `;
                return `
                <tr data-id="${r.id}">
                    <td><code><strong>${escHtml(r.batch_number)}</strong></code></td>
                    <td><small class="text-muted">#${r.insurance_company_id}</small><br>${escHtml(r.insurer_name ?? '—')}</td>
                    <td>${escHtml(r.facility_name ?? '—')}</td>
                    <td><small>${escHtml(r.period_from)} → ${escHtml(r.period_to)}</small></td>
                    <td>${statusBadge(status)}</td>
                    <td class="text-center">${r.total_items}</td>
                    <td><strong>${fmtMoney(r.total_amount, r.currency)}</strong>${r.status !== 'borrador' && r.paid_amount !== null ? `<br><small class="text-muted">${t('paid_label')}: ${fmtMoney(r.paid_amount, r.currency)}</small>` : ''}</td>
                    <td class="covl-actions">${actions}</td>
                </tr>`;
            }).join('');

            Batch.renderPager(total, filters);
        },

        renderPager(total, filters) {
            const pager = Batch.pager();
            if (!pager) return;
            const limit   = filters.limit;
            const current = Math.floor(filters.offset / limit);
            const pages   = Math.ceil(total / limit);
            const prev = current > 0
                ? `<button onclick="window.__COVL_Batch.goPage(${current - 1})">‹</button>` : '<button disabled>‹</button>';
            const next = current < pages - 1
                ? `<button onclick="window.__COVL_Batch.goPage(${current + 1})">›</button>` : '<button disabled>›</button>';
            const nums = Array.from({ length: Math.min(pages, 7) }, (_, i) =>
                `<button class="${i === current ? 'current' : ''}" onclick="window.__COVL_Batch.goPage(${i})">${i + 1}</button>`
            ).join('');
            pager.innerHTML = `
                <span>${t('showing')} ${Math.min(filters.offset + 1, total)}–${Math.min(filters.offset + limit, total)} ${t('of')} ${total}</span>
                <div class="page-btns">${prev}${nums}${next}</div>
            `;
        },

        goPage(page) {
            state.filters.offset = page * state.filters.limit;
            Batch.load();
        },

        async openCreate() {
            state.editingId = null;
            document.getElementById('covlBatchModalLabel').textContent = t('new_batch');
            const form = document.getElementById('covl-batch-form');
            if (form) form.reset();
            document.getElementById('fld-batch-id').value = '';

            await loadInsurers();
            await loadFacilities();
            populateSelect(document.getElementById('fld-batch-insurer'),
                state.insurers.filter(i => i.id !== 0).map(i => ({ value: i.id, label: `[${i.id}] ${i.name}` })), '');
            populateSelect(document.getElementById('fld-batch-facility'),
                state.facilities.map(f => ({ value: f.id, label: f.name })), '');

            const curEl = document.getElementById('fld-batch-currency');
            if (curEl) {
                curEl.value = (covlConfig.activeCurrency && covlConfig.activeCurrency.code)
                    ? covlConfig.activeCurrency.code
                    : 'ARS';
            }

            showModal('covlBatchModal');
        },

        async openEdit(id) {
            state.editingId = id;
            document.getElementById('covlBatchModalLabel').textContent = t('edit_batch');
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'get', id }));
                const data = await res.json();
                if (data.error) { toast(data.error, 'error'); return; }

                document.getElementById('fld-batch-id').value   = data.id;
                document.getElementById('fld-batch-from').value = data.period_from ?? '';
                document.getElementById('fld-batch-to').value   = data.period_to ?? '';
                document.getElementById('fld-batch-currency').value = data.currency ?? 'ARS';

                await loadInsurers();
                await loadFacilities();
                populateSelect(document.getElementById('fld-batch-insurer'),
                    state.insurers.filter(i => i.id !== 0).map(i => ({ value: i.id, label: `[${i.id}] ${i.name}` })), data.insurance_company_id);
                populateSelect(document.getElementById('fld-batch-facility'),
                    state.facilities.map(f => ({ value: f.id, label: f.name })), data.facility_id);

                showModal('covlBatchModal');
            } catch {
                toast(t('error_fetch'), 'error');
            }
        },

        async save() {
            const id = document.getElementById('fld-batch-id').value;

            const insEl = document.getElementById('fld-batch-insurer');
            const fromEl = document.getElementById('fld-batch-from');
            const toEl   = document.getElementById('fld-batch-to');

            if (!insEl.value || !fromEl.value || !toEl.value) {
                toast(t('required_fields'), 'error');
                return;
            }

            const data = {
                csrf_token:           covlConfig.csrfToken,
                insurance_company_id: insEl.value,
                facility_id:          document.getElementById('fld-batch-facility').value || 0,
                period_from:          fromEl.value,
                period_to:            toEl.value,
                currency:             document.getElementById('fld-batch-currency').value || 'ARS',
            };

            const isEdit = !!id;
            const url    = isEdit
                ? apiUrl('batches.php', { action: 'update', id })
                : apiUrl('batches.php', { action: 'create' });

            try {
                const res  = await fetch(url, { method: 'POST', headers: csrfHeaders(), body: JSON.stringify(data) });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                hideModal('covlBatchModal');
                toast(isEdit ? t('updated') : t('created'), 'success');
                Batch.load();
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        async del(id) {
            if (!confirmBox(t('confirm_delete'))) return;
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'delete', id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify({ csrf_token: covlConfig.csrfToken }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(t('deleted'), 'success');
                Batch.load();
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        // -------------------------------------------------------------------
        // Detalle de ítems + transiciones
        // -------------------------------------------------------------------
        async viewItems(id) {
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'get', id }));
                const data = await res.json();
                if (data.error) { toast(data.error, 'error'); return; }
                state.currentBatch = data;

                document.getElementById('covlBatchItemsTitle').textContent = `${t('batch')} ${data.batch_number}`;
                document.getElementById('covl-batch-items-status').innerHTML = statusBadge(data.status);
                document.getElementById('covl-batch-items-total').textContent =
                    `${fmtMoney(data.total_amount, data.currency)} · ${data.total_items} ${t('items')}`;

                Batch.renderItems(data.items ?? []);
                Batch.renderTransitions(data);
                showModal('covlBatchItemsModal');
            } catch {
                toast(t('error_fetch'), 'error');
            }
        },

        renderItems(items) {
            const tbody = document.getElementById('covl-items-tbody');
            if (!tbody) return;
            const canEdit = ['borrador', 'armado'].includes(state.currentBatch?.status);

            if (items.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7"><div class="covl-empty"><div class="icon">📋</div><p>${t('no_items')}</p></div></td></tr>`;
                return;
            }

            tbody.innerHTML = items.map(i => `
                <tr data-id="${i.id}">
                    <td>${escHtml(i.patient_name ?? '#')}<br><small class="text-muted">PID ${i.pid}</small></td>
                    <td><span class="badge bg-light text-dark border">${escHtml(i.code_type)}</span> <code>${escHtml(i.code)}</code><br><small class="text-muted">${escHtml(i.billing_code_text ?? '')}</small></td>
                    <td class="text-center">${i.encounter_id ? `Enc #${i.encounter_id}` : '—'}</td>
                    <td class="text-center"><strong>${fmtMoney(i.fee, i.currency)}</strong></td>
                    <td>${itemStatusBadge(i.item_status)}${i.debit_reason ? `<br><small class="text-danger">${escHtml(i.debit_reason)}</small>` : ''}</td>
                    <td class="text-center"><small>${t('attempt')} ${i.attempt_number}</small></td>
                    <td class="covl-actions">
                        ${canEdit ? `
                            <button class="btn btn-sm btn-outline-success" onclick="window.__COVL_Batch.setItem(${i.id},'aprobado')" title="${t('approve')}">✓</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="window.__COVL_Batch.promptDebit(${i.id})" title="${t('debit')}">✎</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="window.__COVL_Batch.promptReject(${i.id})" title="${t('reject')}">✕</button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="window.__COVL_Batch.removeItem(${i.id})" title="${t('remove')}">🗑</button>
                        ` : ''}
                    </td>
                </tr>
            `).join('');
        },

        renderTransitions(data) {
            const bar = document.getElementById('covl-batch-transitions');
            if (!bar) return;
            const st = data.status;
            const btns = [];

            if (st === 'borrador') {
                btns.push(`<button class="btn btn-sm btn-primary" onclick="window.__COVL_Batch.transition('armado')">📦 ${t('arm')}</button>`);
                btns.push(`<button class="btn btn-sm btn-outline-danger" onclick="window.__COVL_Batch.transition('anulado', true)">${t('annul')}</button>`);
            } else if (st === 'armado') {
                btns.push(`<button class="btn btn-sm btn-primary" onclick="window.__COVL_Batch.transition('presentado')">🚀 ${t('present')}</button>`);
                btns.push(`<button class="btn btn-sm btn-outline-danger" onclick="window.__COVL_Batch.transition('anulado', true)">${t('annul')}</button>`);
            } else if (st === 'presentado' || st === 'en_disputa') {
                btns.push(`<button class="btn btn-sm btn-success" onclick="window.__COVL_Batch.openPay()">💰 ${t('register_payment')}</button>`);
                if (st === 'presentado') {
                    btns.push(`<button class="btn btn-sm btn-outline-warning" onclick="window.__COVL_Batch.openDispute()">⚠ ${t('dispute')}</button>`);
                }
            }

            bar.innerHTML = btns.length
                ? `<div class="d-flex flex-wrap gap-1 align-items-center"><span class="text-muted small me-1">${t('actions')}:</span>${btns.join('')}</div>`
                : '<div class="text-muted small">—</div>';

            const addBtn = document.getElementById('btn-batch-add-billing');
            if (addBtn) {
                addBtn.style.display = ['borrador', 'armado'].includes(st) ? '' : 'none';
            }
        },

        async addBilling(billingId) {
            if (!state.currentBatch) return;
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'add_item' }), {
                    method: 'POST', headers: csrfHeaders(),
                    body: JSON.stringify({ csrf_token: covlConfig.csrfToken, batch_id: state.currentBatch.id, billing_id: billingId }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(t('billing_added'), 'success');
                hideModal('covlBillingModal');
                Batch.viewItems(state.currentBatch.id);
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        async removeItem(itemId) {
            if (!state.currentBatch) return;
            if (!confirmBox(t('confirm_remove_item'))) return;
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'remove_item' }), {
                    method: 'POST', headers: csrfHeaders(),
                    body: JSON.stringify({ csrf_token: covlConfig.csrfToken, item_id: itemId, batch_id: state.currentBatch.id }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(t('deleted'), 'success');
                Batch.viewItems(state.currentBatch.id);
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        async setItem(itemId, itemStatus) {
            if (!state.currentBatch) return;
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'item_status' }), {
                    method: 'POST', headers: csrfHeaders(),
                    body: JSON.stringify({
                        csrf_token: covlConfig.csrfToken,
                        item_id: itemId,
                        batch_id: state.currentBatch.id,
                        item_status: itemStatus,
                    }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(t('item_updated'), 'success');
                Batch.viewItems(state.currentBatch.id);
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        promptReject(itemId) {
            const reason = window.prompt(t('prompt_reject_reason'), '');
            if (reason === null) return;
            Batch.updateItemWithDebit(itemId, 'rechazado', reason, 0);
        },

        promptDebit(itemId) {
            const reason = window.prompt(t('prompt_debit_reason'), '');
            if (reason === null) return;
            const amount = window.prompt(t('prompt_debit_amount'), '0');
            if (amount === null) return;
            Batch.updateItemWithDebit(itemId, 'debitado', reason, parseFloat(amount) || 0);
        },

        async updateItemWithDebit(itemId, itemStatus, debitReason, debitAmount) {
            if (!state.currentBatch) return;
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'item_status' }), {
                    method: 'POST', headers: csrfHeaders(),
                    body: JSON.stringify({
                        csrf_token: covlConfig.csrfToken,
                        item_id: itemId,
                        batch_id: state.currentBatch.id,
                        item_status: itemStatus,
                        debit_reason: debitReason,
                        debit_amount: debitAmount,
                    }),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(t('item_updated'), 'success');
                Batch.viewItems(state.currentBatch.id);
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        async transition(status, confirmIt = false) {
            if (!state.currentBatch) return;
            if (confirmIt && !confirmBox(t('confirm_annul'))) return;
            const data = { csrf_token: covlConfig.csrfToken, status };
            if (status === 'presentado') {
                data.presentation_date = new Date().toISOString().slice(0, 10);
            }
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'transition', id: state.currentBatch.id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify(data),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(t('transition_' + status), 'success');
                hideModal('covlBatchItemsModal');
                Batch.load();
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        openPay() {
            if (!state.currentBatch) return;
            const b = state.currentBatch;
            document.getElementById('fld-pay-amount').value = b.total_amount ?? '';
            document.getElementById('fld-pay-date').value = new Date().toISOString().slice(0, 10);
            document.getElementById('fld-pay-reference').value = '';
            showModal('covlPayModal');
        },

        async submitPay() {
            if (!state.currentBatch) return;
            const amount = parseFloat(document.getElementById('fld-pay-amount').value);
            if (isNaN(amount) || amount <= 0) {
                toast(t('required_fields'), 'error');
                return;
            }
            const total = parseFloat(state.currentBatch.total_amount || 0);
            const status = amount >= total ? 'pagado_total' : 'pagado_parcial';
            const data = {
                csrf_token: covlConfig.csrfToken,
                status,
                paid_amount: amount,
                payment_date: document.getElementById('fld-pay-date').value || '',
                payment_reference: document.getElementById('fld-pay-reference').value || '',
            };
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'transition', id: state.currentBatch.id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify(data),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(t('transition_' + status), 'success');
                hideModal('covlPayModal');
                hideModal('covlBatchItemsModal');
                Batch.load();
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        openDispute() {
            if (!state.currentBatch) return;
            document.getElementById('fld-dispute-notes').value = '';
            showModal('covlDisputeModal');
        },

        async submitDispute() {
            if (!state.currentBatch) return;
            const data = {
                csrf_token: covlConfig.csrfToken,
                status: 'en_disputa',
                dispute_notes: document.getElementById('fld-dispute-notes').value || '',
            };
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'transition', id: state.currentBatch.id }), {
                    method: 'POST', headers: csrfHeaders(), body: JSON.stringify(data),
                });
                const json = await res.json();
                if (json.error) { toast(json.error, 'error'); return; }
                toast(t('transition_en_disputa'), 'success');
                hideModal('covlDisputeModal');
                hideModal('covlBatchItemsModal');
                Batch.load();
            } catch {
                toast(t('error_save'), 'error');
            }
        },

        // -------------------------------------------------------------------
        // Búsqueda de prestaciones candidatas (billing)
        // -------------------------------------------------------------------
        async openBillingPicker() {
            if (!state.currentBatch) return;
            document.getElementById('flt-billing-q').value = '';
            document.getElementById('covl-billing-tbody').innerHTML =
                `<tr><td colspan="6"><div class="covl-loading"><div class="covl-spinner"></div> ${t('loading')}</div></td></tr>`;
            showModal('covlBillingModal');
            Batch.searchBillings();
        },

        async searchBillings() {
            if (!state.currentBatch) return;
            const q = document.getElementById('flt-billing-q').value || '';
            const params = {
                batch_id: state.currentBatch.id,
                period_from: state.currentBatch.period_from,
                period_to: state.currentBatch.period_to,
                q,
                limit: 50,
            };
            const tbody = document.getElementById('covl-billing-tbody');
            try {
                const res  = await fetch(apiUrl('batches.php', { action: 'billings', ...params }));
                const json = await res.json();
                const rows = json.data ?? [];
                if (rows.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6"><div class="covl-empty"><div class="icon">🔍</div><p>${t('no_billings')}</p></div></td></tr>`;
                    return;
                }
                tbody.innerHTML = rows.map(b => `
                    <tr data-id="${b.id}">
                        <td>${escHtml(b.patient_name ?? '—')}<br><small class="text-muted">PID ${b.pid}</small></td>
                        <td><span class="badge bg-light text-dark border">${escHtml(b.code_type)}</span> <code>${escHtml(b.code)}</code></td>
                        <td><small>${escHtml(b.code_text ?? '')}</small></td>
                        <td><small>${escHtml(b.date ?? '')}</small></td>
                        <td><strong>${fmtMoney(b.fee, state.currentBatch.currency)}</strong></td>
                        <td class="covl-actions">
                            <button class="btn btn-sm btn-outline-primary" onclick="window.__COVL_Batch.addBilling(${b.id})">${t('add')}</button>
                        </td>
                    </tr>
                `).join('');
            } catch {
                tbody.innerHTML = `<tr><td colspan="6"><div class="covl-empty"><div class="icon">⚠</div><p>${t('error_loading')}</p></div></td></tr>`;
            }
        },
    };

    // API pública global (para atributos onclick inline)
    window.__COVL_Batch = {
        openCreate: Batch.openCreate,
        viewItems: Batch.viewItems,
        openEdit:  Batch.openEdit,
        del:       Batch.del,
        goPage:    Batch.goPage,
        addBilling: Batch.addBilling,
        removeItem: Batch.removeItem,
        setItem:   Batch.setItem,
        promptReject: Batch.promptReject,
        promptDebit:  Batch.promptDebit,
        transition:   Batch.transition,
        openPay:   Batch.openPay,
        submitPay: Batch.submitPay,
        openDispute: Batch.openDispute,
        submitDispute: Batch.submitDispute,
        openBillingPicker: Batch.openBillingPicker,
        searchBillings: Batch.searchBillings,
    };

    // -----------------------------------------------------------------------
    // Inicialización
    // -----------------------------------------------------------------------
    const init = () => {
        if (!document.getElementById('covl-batch-tbody')) return;
        Batch.load();

        const bindFilters = () => {
            const onFilter = debounce(Batch.load);
            document.querySelectorAll('[data-covl-filter-batch]').forEach(el => {
                el.addEventListener('input', onFilter);
                el.addEventListener('change', onFilter);
            });
        };
        bindFilters();

        const form = document.getElementById('covl-batch-form');
        if (form) form.addEventListener('submit', (e) => { e.preventDefault(); Batch.save(); });

        const payForm = document.getElementById('covl-pay-form');
        if (payForm) payForm.addEventListener('submit', (e) => { e.preventDefault(); Batch.submitPay(); });

        const disputeForm = document.getElementById('covl-dispute-form');
        if (disputeForm) disputeForm.addEventListener('submit', (e) => { e.preventDefault(); Batch.submitDispute(); });

        const billingSearch = document.getElementById('flt-billing-q');
        if (billingSearch) billingSearch.addEventListener('input', debounce(Batch.searchBillings));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
