/* global OC, t */
(function (window, document) {
    'use strict';

    const baseUrl = OC.generateUrl('/apps/immo');
    const escapeHtml = (value) => (value === null || value === undefined ? '' : String(value));

    const State = {
        currentView: null,
        currentUserRole: window.ImmoCurrentUserRole || 'verwalter',
        cache: {},
    };

    const buildQuery = (params = {}) => {
        const parts = [];
        Object.keys(params).forEach(key => {
            const value = params[key];
            if (value !== undefined && value !== null && value !== '') {
                parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
            }
        });
        return parts.length ? '?' + parts.join('&') : '';
    };

    const request = (method, path, data = null, expectHtml = false) => {
        const headers = {
            'OCS-APIREQUEST': 'true',
            'requesttoken': OC.requestToken,
        };
        const options = { method, headers };
        if (data) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }
        return fetch(baseUrl + path, options).then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    const message = text || t('immo', 'Request failed');
                    throw new Error(message);
                });
            }
            return expectHtml ? response.text() : response.json();
        });
    };

    const Api = {
        loadView(view) {
            return request('GET', '/view/' + view, null, true);
        },
        getDashboard(year) {
            const qs = buildQuery({ year });
            return request('GET', '/api/dashboard' + qs);
        },
        getProps() {
            return request('GET', '/api/prop');
        },
        getProp(id) {
            return request('GET', '/api/prop/' + id);
        },
        createProp(data) {
            return request('POST', '/api/prop', data);
        },
        updateProp(id, data) {
            return request('PUT', '/api/prop/' + id, data);
        },
        deleteProp(id) {
            return request('DELETE', '/api/prop/' + id);
        },
        getUnits(filter = {}) {
            return request('GET', '/api/unit' + buildQuery(filter));
        },
        createUnit(data) {
            return request('POST', '/api/unit', data);
        },
        updateUnit(id, data) {
            return request('PUT', '/api/unit/' + id, data);
        },
        deleteUnit(id) {
            return request('DELETE', '/api/unit/' + id);
        },
        getTenants() {
            return request('GET', '/api/tenant');
        },
        createTenant(data) {
            return request('POST', '/api/tenant', data);
        },
        updateTenant(id, data) {
            return request('PUT', '/api/tenant/' + id, data);
        },
        deleteTenant(id) {
            return request('DELETE', '/api/tenant/' + id);
        },
        getLeases(filter = {}) {
            return request('GET', '/api/lease' + buildQuery(filter));
        },
        createLease(data) {
            return request('POST', '/api/lease', data);
        },
        updateLease(id, data) {
            return request('PUT', '/api/lease/' + id, data);
        },
        deleteLease(id) {
            return request('DELETE', '/api/lease/' + id);
        },
        getBookings(filter = {}) {
            return request('GET', '/api/book' + buildQuery(filter));
        },
        createBooking(data) {
            return request('POST', '/api/book', data);
        },
        updateBooking(id, data) {
            return request('PUT', '/api/book/' + id, data);
        },
        deleteBooking(id) {
            return request('DELETE', '/api/book/' + id);
        },
        getReports(filter = {}) {
            return request('GET', '/api/report' + buildQuery(filter));
        },
        createReport(data) {
            return request('POST', '/api/report', data);
        },
    };

    const UI = {
        showLoading(target) {
            if (target) {
                target.innerHTML = '<div class="immo-loading">' + t('immo', 'Loading…') + '</div>';
            }
        },
        showError(target, message) {
            if (target) {
                target.innerHTML = '<div class="immo-empty">' + escapeHtml(message) + '</div>';
            } else {
                window.alert(message);
            }
        },
        showEmpty(target, message) {
            if (target) {
                target.innerHTML = '<div class="immo-empty">' + escapeHtml(message) + '</div>';
            }
        },
        showSuccess(message) {
            if (OC.Notification && OC.Notification.showTemporary) {
                OC.Notification.showTemporary(message);
            } else {
                window.alert(message);
            }
        },
        confirm(message) {
            return Promise.resolve(window.confirm(message));
        },
        formToJSON(form) {
            const data = {};
            Array.from(form.elements).forEach(el => {
                if (!el.name || el.disabled) {
                    return;
                }
                if (el.type === 'checkbox') {
                    data[el.name] = el.checked;
                } else {
                    data[el.name] = el.value;
                }
            });
            return data;
        },
        fillForm(form, data = {}) {
            Object.keys(data).forEach(key => {
                const el = form.elements.namedItem(key);
                if (!el) {
                    return;
                }
                if (el.type === 'checkbox') {
                    el.checked = Boolean(data[key]);
                } else {
                    el.value = data[key] === null || data[key] === undefined ? '' : data[key];
                }
            });
        },
        resetForm(form) {
            form.reset();
        },
        populateSelect(select, items, getOption) {
            if (!select) {
                return;
            }
            const currentValue = select.value;
            const placeholder = select.dataset.placeholder || t('immo', 'Please choose');
            select.innerHTML = '';
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = placeholder;
            select.appendChild(emptyOption);
            items.forEach(item => {
                const optionData = getOption(item);
                const option = document.createElement('option');
                option.value = optionData.value;
                option.textContent = optionData.label;
                select.appendChild(option);
            });
            if (currentValue) {
                select.value = currentValue;
            }
        },
        createModal(title) {
            const backdrop = document.createElement('div');
            backdrop.className = 'immo-modal-backdrop';
            const modal = document.createElement('div');
            modal.className = 'immo-modal';
            modal.innerHTML = `
                <div class="immo-modal-header">
                    <h3>${escapeHtml(title)}</h3>
                    <button type="button" class="immo-modal-close">×</button>
                </div>
                <div class="immo-modal-body"></div>`;
            backdrop.appendChild(modal);
            const titleEl = modal.querySelector('h3');
            const bodyEl = modal.querySelector('.immo-modal-body');
            const close = () => backdrop.remove();
            modal.querySelector('.immo-modal-close').addEventListener('click', close);
            backdrop.addEventListener('click', (ev) => {
                if (ev.target === backdrop) {
                    close();
                }
            });
            return {
                element: backdrop,
                body: bodyEl,
                open() {
                    document.body.appendChild(backdrop);
                },
                close,
                setTitle(newTitle) {
                    titleEl.textContent = newTitle;
                },
                setContent(node) {
                    bodyEl.innerHTML = '';
                    bodyEl.appendChild(node);
                },
            };
        },
        renderTable(target, { columns, rows, state = {}, emptyMessage }) {
            if (!rows || !rows.length) {
                UI.showEmpty(target, emptyMessage || t('immo', 'No entries yet'));
                return;
            }
            const sortableColumns = columns.filter(col => col.sortable !== false);
            if (!state.sortKey && sortableColumns.length) {
                state.sortKey = sortableColumns[0].id;
            }
            if (!state.sortDir) {
                state.sortDir = 'asc';
            }
            const getComparable = (val) => {
                if (val === null || val === undefined) {
                    return '';
                }
                if (typeof val === 'number') {
                    return val;
                }
                const num = Number(val);
                return Number.isNaN(num) ? String(val).toLowerCase() : num;
            };
            const sortRows = () => {
                const col = columns.find(c => c.id === state.sortKey) || columns[0];
                if (!col || col.sortable === false) {
                    return rows.slice();
                }
                const extractor = col.sortValue || ((row) => row[col.id]);
                const sorted = rows.slice().sort((a, b) => {
                    const va = getComparable(extractor(a));
                    const vb = getComparable(extractor(b));
                    if (va < vb) return state.sortDir === 'asc' ? -1 : 1;
                    if (va > vb) return state.sortDir === 'asc' ? 1 : -1;
                    return 0;
                });
                return sorted;
            };
            const performRender = () => {
                const sortedRows = sortRows();
                const table = document.createElement('table');
                table.className = 'immo-table';
                const thead = document.createElement('thead');
                const headRow = document.createElement('tr');
                columns.forEach(col => {
                    const th = document.createElement('th');
                    th.textContent = col.label;
                    if (col.sortable !== false) {
                        th.classList.add('sortable');
                        th.dataset.sortKey = col.id;
                        if (state.sortKey === col.id) {
                            th.classList.add(state.sortDir === 'desc' ? 'sorted-desc' : 'sorted-asc');
                        }
                    }
                    headRow.appendChild(th);
                });
                thead.appendChild(headRow);
                table.appendChild(thead);
                const tbody = document.createElement('tbody');
                sortedRows.forEach(row => {
                    const tr = document.createElement('tr');
                    columns.forEach(col => {
                        const td = document.createElement('td');
                        if (col.render) {
                            td.innerHTML = col.render(row);
                        } else {
                            td.textContent = escapeHtml(row[col.id] === undefined ? '' : row[col.id]);
                        }
                        if (col.className) {
                            td.className = col.className;
                        }
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
                table.appendChild(tbody);
                target.innerHTML = '';
                target.appendChild(table);
                thead.addEventListener('click', (ev) => {
                    const th = ev.target.closest('th[data-sort-key]');
                    if (!th) {
                        return;
                    }
                    const newKey = th.dataset.sortKey;
                    if (state.sortKey === newKey) {
                        state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        state.sortKey = newKey;
                        state.sortDir = 'asc';
                    }
                    performRender();
                });
            };
            performRender();
        },
    };

    const Views = {};

    Views.dashboard = (root) => {
        if (!root) {
            return;
        }
        let year = parseInt(root.getAttribute('data-year'), 10);
        if (!year) {
            year = new Date().getFullYear();
        }
        const filterBar = document.createElement('form');
        filterBar.className = 'immo-filterbar';
        filterBar.innerHTML = `
            <label>${t('immo', 'Year')}<input type="number" name="year" value="${escapeHtml(year)}" min="2000" max="2100"></label>
            <button type="submit" class="primary">${t('immo', 'Reload')}</button>`;
        const heading = root.querySelector('h2');
        if (heading) {
            heading.insertAdjacentElement('afterend', filterBar);
        } else {
            root.insertBefore(filterBar, root.firstChild);
        }
        const cardsContainer = root.querySelector('.immo-cards') || document.createElement('div');
        cardsContainer.classList.add('immo-cards');
        if (!cardsContainer.parentNode) {
            root.appendChild(cardsContainer);
        }
        const render = () => {
            UI.showLoading(cardsContainer);
            Api.getDashboard(year).then(data => {
                cardsContainer.innerHTML = `
                    <div class="card">${t('immo', 'Properties')}: ${escapeHtml(data.propCount)}</div>
                    <div class="card">${t('immo', 'Units')}: ${escapeHtml(data.unitCount)}</div>
                    <div class="card">${t('immo', 'Active leases')}: ${escapeHtml(data.activeLeaseCount)}</div>
                    <div class="card">${t('immo', 'Annual rent (cold)')}: ${escapeHtml(data.annualRentSum)}</div>`;
            }).catch(err => UI.showError(cardsContainer, err.message));
        };
        filterBar.addEventListener('submit', (ev) => {
            ev.preventDefault();
            const newYear = parseInt(filterBar.year.value, 10);
            if (newYear) {
                year = newYear;
            }
            render();
        });
        render();
    };

    Views.props = (root) => {
        if (!root) {
            return;
        }
        const listEl = root.querySelector('#immo-prop-list');
        const addBtn = root.querySelector('#immo-prop-add');
        if (!listEl) {
            return;
        }
        const state = { props: [], sort: { sortKey: 'name', sortDir: 'asc' } };

        const openFormModal = (prop = null) => {
            const isEdit = Boolean(prop);
            const modal = UI.createModal(isEdit ? t('immo', 'Edit property') : t('immo', 'Create property'));
            const form = document.createElement('form');
            form.className = 'immo-form immo-form-grid';
            form.innerHTML = `
                <label class="wide">${t('immo', 'Name')}<input type="text" name="name" required></label>
                <label>${t('immo', 'Street')}<input type="text" name="street"></label>
                <label>${t('immo', 'ZIP')}<input type="text" name="zip"></label>
                <label>${t('immo', 'City')}<input type="text" name="city"></label>
                <label>${t('immo', 'Country')}<input type="text" name="country"></label>
                <label>${t('immo', 'Type')}<input type="text" name="type"></label>
                <label class="wide">${t('immo', 'Notes')}<textarea name="note"></textarea></label>
                <div class="immo-form-actions">
                    <button type="submit" class="primary">${t('immo', 'Save')}</button>
                    <button type="button" data-action="cancel">${t('immo', 'Cancel')}</button>
                </div>`;
            modal.setContent(form);
            if (isEdit) {
                modal.setTitle(t('immo', 'Edit property') + ' #' + prop.id);
                UI.fillForm(form, prop);
            }
            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const payload = UI.formToJSON(form);
                const action = isEdit ? Api.updateProp(prop.id, payload) : Api.createProp(payload);
                action.then(() => {
                    UI.showSuccess(isEdit ? t('immo', 'Property updated') : t('immo', 'Property created'));
                    modal.close();
                    renderList();
                }).catch(err => UI.showError(listEl, err.message));
            });
            form.querySelector('[data-action="cancel"]').addEventListener('click', modal.close);
            modal.open();
        };

        const renderList = () => {
            UI.showLoading(listEl);
            Api.getProps().then(props => {
                State.cache.props = props;
                state.props = props;
                UI.renderTable(listEl, {
                    columns: [
                        { id: 'name', label: t('immo', 'Name') },
                        {
                            id: 'address',
                            label: t('immo', 'Address'),
                            render: (prop) => escapeHtml([prop.street, prop.zip, prop.city].filter(Boolean).join(', ')),
                            sortValue: (prop) => [prop.street, prop.zip, prop.city].filter(Boolean).join(', '),
                        },
                        { id: 'type', label: t('immo', 'Type') },
                        {
                            id: 'actions',
                            label: '',
                            sortable: false,
                            className: 'immo-table-actions',
                            render: (prop) => '<button data-action="edit" data-id="' + prop.id + '">' + t('immo', 'Edit') + '</button>' +
                                '<button data-action="delete" data-id="' + prop.id + '">' + t('immo', 'Delete') + '</button>',
                        },
                    ],
                    rows: props,
                    state: state.sort,
                    emptyMessage: t('immo', 'No properties yet'),
                });
            }).catch(err => UI.showError(listEl, err.message));
        };

        listEl.addEventListener('click', (ev) => {
            const btn = ev.target.closest('button[data-action]');
            if (!btn) {
                return;
            }
            const id = parseInt(btn.dataset.id, 10);
            if (btn.dataset.action === 'edit') {
                const prop = state.props.find(p => p.id === id);
                if (prop) {
                    openFormModal(prop);
                }
            } else if (btn.dataset.action === 'delete') {
                UI.confirm(t('immo', 'Delete this property?')).then(ok => {
                    if (!ok) {
                        return;
                    }
                    Api.deleteProp(id).then(() => {
                        UI.showSuccess(t('immo', 'Property deleted'));
                        renderList();
                    }).catch(err => UI.showError(listEl, err.message));
                });
            }
        });

        if (addBtn) {
            addBtn.addEventListener('click', () => openFormModal());
        }
        renderList();
    };

    Views.units = (root) => {
        const listEl = root.querySelector('#immo-unit-list');
        const addBtn = root.querySelector('#immo-unit-add');
        if (!listEl) {
            return;
        }
        const state = { editingId: null, props: [], units: [], sort: { sortKey: 'label', sortDir: 'asc' } };

        const populateProps = (select) => {
            UI.populateSelect(select, state.props, (prop) => ({ value: prop.id, label: `${prop.name} (${prop.city || ''})` }));
        };

        const openFormModal = (unit = null) => {
            const isEdit = Boolean(unit);
            const modal = UI.createModal(isEdit ? t('immo', 'Edit unit') : t('immo', 'Create unit'));
            const form = document.createElement('form');
            form.className = 'immo-form immo-form-grid';
            form.innerHTML = `
                <label>${t('immo', 'Property')}<select name="propId" required data-placeholder="${t('immo', 'Select property')}"></select></label>
                <label>${t('immo', 'Label')}<input type="text" name="label" required></label>
                <label>${t('immo', 'Location')}<input type="text" name="loc"></label>
                <label>${t('immo', 'Type')}<input type="text" name="type"></label>
                <label>${t('immo', 'Living area m²')}<input type="number" step="0.01" name="areaRes"></label>
                <label>${t('immo', 'Usable area m²')}<input type="number" step="0.01" name="areaUse"></label>
                <label>${t('immo', 'Ledger account')}<input type="text" name="gbook"></label>
                <label class="wide">${t('immo', 'Notes')}<textarea name="note"></textarea></label>
                <div class="immo-form-actions">
                    <button type="submit" class="primary">${t('immo', 'Save')}</button>
                    <button type="button" data-action="cancel">${t('immo', 'Cancel')}</button>
                </div>`;
            modal.setContent(form);
            populateProps(form.propId);
            if (isEdit) {
                modal.setTitle(t('immo', 'Edit unit') + ' #' + unit.id);
                UI.fillForm(form, unit);
            } else if (state.props.length) {
                form.propId.value = String(state.props[0].id);
            }
            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const payload = UI.formToJSON(form);
                const req = isEdit ? Api.updateUnit(unit.id, payload) : Api.createUnit(payload);
                req.then(() => {
                    UI.showSuccess(isEdit ? t('immo', 'Unit updated') : t('immo', 'Unit created'));
                    modal.close();
                    renderList();
                }).catch(err => UI.showError(listEl, err.message));
            });
            form.querySelector('[data-action="cancel"]').addEventListener('click', modal.close);
            modal.open();
        };

        const renderList = () => {
            UI.showLoading(listEl);
            Api.getUnits().then(units => {
                state.units = units;
                State.cache.units = units;
                const propMap = {};
                state.props.forEach(prop => { propMap[prop.id] = prop; });
                UI.renderTable(listEl, {
                    columns: [
                        { id: 'label', label: t('immo', 'Label') },
                        {
                            id: 'property',
                            label: t('immo', 'Property'),
                            render: (unit) => escapeHtml(propMap[unit.propId] ? propMap[unit.propId].name : ('#' + unit.propId)),
                            sortValue: (unit) => propMap[unit.propId] ? propMap[unit.propId].name : unit.propId,
                        },
                        { id: 'areaRes', label: t('immo', 'Living area m²') },
                        { id: 'type', label: t('immo', 'Type') },
                        {
                            id: 'actions',
                            label: '',
                            sortable: false,
                            className: 'immo-table-actions',
                            render: (unit) => '<button data-action="edit" data-id="' + unit.id + '">' + t('immo', 'Edit') + '</button>' +
                                '<button data-action="delete" data-id="' + unit.id + '">' + t('immo', 'Delete') + '</button>',
                        },
                    ],
                    rows: units,
                    state: state.sort,
                    emptyMessage: t('immo', 'No units yet'),
                });
            }).catch(err => UI.showError(listEl, err.message));
        };

        listEl.addEventListener('click', (ev) => {
            const btn = ev.target.closest('button[data-action]');
            if (!btn) {
                return;
            }
            const id = parseInt(btn.dataset.id, 10);
            if (btn.dataset.action === 'edit') {
                const unit = state.units.find(u => u.id === id);
                if (unit) {
                    openFormModal(unit);
                }
            } else if (btn.dataset.action === 'delete') {
                UI.confirm(t('immo', 'Delete this unit?')).then(ok => {
                    if (!ok) {
                        return;
                    }
                    Api.deleteUnit(id).then(() => {
                        UI.showSuccess(t('immo', 'Unit deleted'));
                        renderList();
                    }).catch(err => UI.showError(listEl, err.message));
                });
            }
        });

        Api.getProps().then(props => {
            state.props = props;
        }).catch(() => {
            state.props = [];
        }).finally(() => {
            renderList();
        });
        if (addBtn) {
            addBtn.addEventListener('click', () => openFormModal());
        }
    };

    Views.tenants = (root) => {
        const listEl = root.querySelector('#immo-tenant-list');
        const addBtn = root.querySelector('#immo-tenant-add');
        if (!listEl) {
            return;
        }
        const state = { tenants: [], sort: { sortKey: 'name', sortDir: 'asc' } };

        const openFormModal = (tenant = null) => {
            const isEdit = Boolean(tenant);
            const modal = UI.createModal(isEdit ? t('immo', 'Edit tenant') : t('immo', 'Create tenant'));
            const form = document.createElement('form');
            form.className = 'immo-form immo-form-grid';
            form.innerHTML = `
                <label>${t('immo', 'Name')}<input type="text" name="name" required></label>
                <label>${t('immo', 'Customer number')}<input type="text" name="custNo"></label>
                <label>${t('immo', 'Email')}<input type="email" name="email"></label>
                <label>${t('immo', 'Phone')}<input type="text" name="phone"></label>
                <label class="wide">${t('immo', 'Address')}<textarea name="addr"></textarea></label>
                <label class="wide">${t('immo', 'Notes')}<textarea name="note"></textarea></label>
                <div class="immo-form-actions">
                    <button type="submit" class="primary">${t('immo', 'Save')}</button>
                    <button type="button" data-action="cancel">${t('immo', 'Cancel')}</button>
                </div>`;
            modal.setContent(form);
            if (isEdit) {
                modal.setTitle(t('immo', 'Edit tenant') + ' #' + tenant.id);
                UI.fillForm(form, tenant);
            }
            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const payload = UI.formToJSON(form);
                const fn = isEdit ? Api.updateTenant(tenant.id, payload) : Api.createTenant(payload);
                fn.then(() => {
                    UI.showSuccess(isEdit ? t('immo', 'Tenant updated') : t('immo', 'Tenant created'));
                    modal.close();
                    renderList();
                }).catch(err => UI.showError(listEl, err.message));
            });
            form.querySelector('[data-action="cancel"]').addEventListener('click', modal.close);
            modal.open();
        };

        const renderList = () => {
            UI.showLoading(listEl);
            Api.getTenants().then(tenants => {
                state.tenants = tenants;
                State.cache.tenants = tenants;
                UI.renderTable(listEl, {
                    columns: [
                        { id: 'name', label: t('immo', 'Name') },
                        {
                            id: 'contact',
                            label: t('immo', 'Contact'),
                            render: (tenant) => escapeHtml([tenant.email, tenant.phone].filter(Boolean).join(' · ')),
                            sortValue: (tenant) => [tenant.email, tenant.phone].filter(Boolean).join(' '),
                        },
                        { id: 'custNo', label: t('immo', 'Customer number') },
                        {
                            id: 'actions',
                            label: '',
                            sortable: false,
                            className: 'immo-table-actions',
                            render: (tenant) => '<button data-action="edit" data-id="' + tenant.id + '">' + t('immo', 'Edit') + '</button>' +
                                '<button data-action="delete" data-id="' + tenant.id + '">' + t('immo', 'Delete') + '</button>',
                        },
                    ],
                    rows: tenants,
                    state: state.sort,
                    emptyMessage: t('immo', 'No tenants yet'),
                });
            }).catch(err => UI.showError(listEl, err.message));
        };

        listEl.addEventListener('click', (ev) => {
            const btn = ev.target.closest('button[data-action]');
            if (!btn) {
                return;
            }
            const id = parseInt(btn.dataset.id, 10);
            if (btn.dataset.action === 'edit') {
                const tenant = state.tenants.find(tn => tn.id === id);
                if (tenant) {
                    openFormModal(tenant);
                }
            } else if (btn.dataset.action === 'delete') {
                UI.confirm(t('immo', 'Delete this tenant?')).then(ok => {
                    if (!ok) {
                        return;
                    }
                    Api.deleteTenant(id).then(() => {
                        UI.showSuccess(t('immo', 'Tenant deleted'));
                        renderList();
                    }).catch(err => UI.showError(listEl, err.message));
                });
            }
        });

        if (addBtn) {
            addBtn.addEventListener('click', () => openFormModal());
        }
        renderList();
    };

    Views.leases = (root) => {
        const listEl = root.querySelector('#immo-lease-list');
        const addBtn = root.querySelector('#immo-lease-add');
        if (!listEl) {
            return;
        }
        const filterBar = document.createElement('form');
        filterBar.className = 'immo-filterbar';
        filterBar.innerHTML = `
            <label>${t('immo', 'Status')}<select name="status">
                <option value="">${t('immo', 'All')}</option>
                <option value="future">${t('immo', 'Future')}</option>
                <option value="active">${t('immo', 'Active')}</option>
                <option value="hist">${t('immo', 'Ended')}</option>
            </select></label>
            <label>${t('immo', 'Year')}<input type="number" name="year" value="${escapeHtml(new Date().getFullYear())}"></label>
            <button type="submit" class="primary">${t('immo', 'Filter')}</button>`;
        root.insertBefore(filterBar, listEl);

        const state = { editingId: null, leases: [], units: [], tenants: [], sort: { sortKey: 'start', sortDir: 'asc' } };

        const populateRefs = (form) => {
            UI.populateSelect(form.unitId, state.units, (unit) => {
                const unitLabel = unit.label ? unit.label : '#' + unit.id;
                return { value: unit.id, label: `${unitLabel} (#${unit.id})` };
            });
            UI.populateSelect(form.tenantId, state.tenants, (tenant) => ({ value: tenant.id, label: tenant.name }));
        };

        const openFormModal = (lease = null) => {
            const isEdit = Boolean(lease);
            const modal = UI.createModal(isEdit ? t('immo', 'Edit lease') : t('immo', 'Create lease'));
            const form = document.createElement('form');
            form.className = 'immo-form immo-form-grid';
            form.innerHTML = `
                <label>${t('immo', 'Unit')}<select name="unitId" required data-placeholder="${t('immo', 'Select unit')}"></select></label>
                <label>${t('immo', 'Tenant')}<select name="tenantId" required data-placeholder="${t('immo', 'Select tenant')}"></select></label>
                <label>${t('immo', 'Start')}<input type="date" name="start" required></label>
                <label>${t('immo', 'End')}<input type="date" name="end"></label>
                <label>${t('immo', 'Cold rent')}<input type="number" step="0.01" name="rentCold"></label>
                <label>${t('immo', 'Additional costs')}<input type="text" name="costs"></label>
                <label>${t('immo', 'Costs type')}<input type="text" name="costsType"></label>
                <label>${t('immo', 'Deposit')}<input type="text" name="deposit"></label>
                <label class="wide">${t('immo', 'Contract conditions')}<textarea name="cond"></textarea></label>
                <div class="immo-form-actions">
                    <button type="submit" class="primary">${t('immo', 'Save')}</button>
                    <button type="button" data-action="cancel">${t('immo', 'Cancel')}</button>
                </div>`;
            modal.setContent(form);
            populateRefs(form);
            if (isEdit) {
                modal.setTitle(t('immo', 'Edit lease') + ' #' + lease.id);
                UI.fillForm(form, lease);
            } else {
                const today = new Date().toISOString().slice(0, 10);
                form.start.value = today;
            }
            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const payload = UI.formToJSON(form);
                const action = isEdit ? Api.updateLease(lease.id, payload) : Api.createLease(payload);
                action.then(() => {
                    UI.showSuccess(isEdit ? t('immo', 'Lease updated') : t('immo', 'Lease created'));
                    modal.close();
                    renderList();
                }).catch(err => UI.showError(listEl, err.message));
            });
            form.querySelector('[data-action="cancel"]').addEventListener('click', modal.close);
            modal.open();
        };

        const renderList = () => {
            UI.showLoading(listEl);
            const filter = {
                status: filterBar.status.value,
                year: filterBar.year.value,
            };
            Api.getLeases(filter).then(leases => {
                state.leases = leases;
                State.cache.leases = leases;
                const unitMap = {};
                state.units.forEach(unit => { unitMap[unit.id] = unit; });
                const tenantMap = {};
                state.tenants.forEach(tenant => { tenantMap[tenant.id] = tenant; });
                UI.renderTable(listEl, {
                    columns: [
                        {
                            id: 'tenant',
                            label: t('immo', 'Tenant'),
                            render: (lease) => escapeHtml(tenantMap[lease.tenantId] ? tenantMap[lease.tenantId].name : ('#' + lease.tenantId)),
                            sortValue: (lease) => tenantMap[lease.tenantId] ? tenantMap[lease.tenantId].name : lease.tenantId,
                        },
                        {
                            id: 'unit',
                            label: t('immo', 'Unit'),
                            render: (lease) => escapeHtml(unitMap[lease.unitId] ? unitMap[lease.unitId].label : ('#' + lease.unitId)),
                            sortValue: (lease) => unitMap[lease.unitId] ? unitMap[lease.unitId].label : lease.unitId,
                        },
                        {
                            id: 'period',
                            label: t('immo', 'Period'),
                            render: (lease) => escapeHtml([lease.start, lease.end || '…'].filter(Boolean).join(' – ')),
                            sortValue: (lease) => lease.start,
                        },
                        { id: 'rentCold', label: t('immo', 'Cold rent') },
                        {
                            id: 'status',
                            label: t('immo', 'Status'),
                            render: (lease) => '<span class="immo-tag">' + escapeHtml(lease.status) + '</span>',
                        },
                        {
                            id: 'actions',
                            label: '',
                            sortable: false,
                            className: 'immo-table-actions',
                            render: (lease) => '<button data-action="edit" data-id="' + lease.id + '">' + t('immo', 'Edit') + '</button>' +
                                '<button data-action="delete" data-id="' + lease.id + '">' + t('immo', 'Delete') + '</button>',
                        },
                    ],
                    rows: leases,
                    state: state.sort,
                    emptyMessage: t('immo', 'No leases yet'),
                });
            }).catch(err => UI.showError(listEl, err.message));
        };

        filterBar.addEventListener('submit', (ev) => {
            ev.preventDefault();
            renderList();
        });

        listEl.addEventListener('click', (ev) => {
            const btn = ev.target.closest('button[data-action]');
            if (!btn) {
                return;
            }
            const id = parseInt(btn.dataset.id, 10);
            if (btn.dataset.action === 'edit') {
                const lease = state.leases.find(l => l.id === id);
                if (lease) {
                    openFormModal(lease);
                }
            } else if (btn.dataset.action === 'delete') {
                UI.confirm(t('immo', 'Delete this lease?')).then(ok => {
                    if (!ok) {
                        return;
                    }
                    Api.deleteLease(id).then(() => {
                        UI.showSuccess(t('immo', 'Lease deleted'));
                        renderList();
                    }).catch(err => UI.showError(listEl, err.message));
                });
            }
        });

        Promise.all([Api.getUnits(), Api.getTenants()]).then(([units, tenants]) => {
            state.units = units;
            state.tenants = tenants;
            renderList();
        }).catch(err => UI.showError(listEl, err.message));
        if (addBtn) {
            addBtn.addEventListener('click', () => openFormModal());
        }
    };
    Views.books = (root) => {
        const listEl = root.querySelector('#immo-booking-list');
        const addBtn = root.querySelector('#immo-booking-add');
        if (!listEl) {
            return;
        }
        const filterBar = document.createElement('form');
        filterBar.className = 'immo-filterbar';
        const currentYear = new Date().getFullYear();
        filterBar.innerHTML = `
            <label>${t('immo', 'Year')}<input type="number" name="year" value="${escapeHtml(currentYear)}"></label>
            <label>${t('immo', 'Type')}<select name="type">
                <option value="">${t('immo', 'All')}</option>
                <option value="in">${t('immo', 'Income')}</option>
                <option value="out">${t('immo', 'Expense')}</option>
            </select></label>
            <button type="submit" class="primary">${t('immo', 'Filter')}</button>`;
        root.insertBefore(filterBar, listEl);

        const state = { editingId: null, bookings: [], props: [], units: [], leases: [], sort: { sortKey: 'date', sortDir: 'desc' } };

        const populateRefs = (form) => {
            UI.populateSelect(form.propId, state.props, (prop) => ({ value: prop.id, label: prop.name }));
            UI.populateSelect(form.unitId, state.units, (unit) => ({ value: unit.id, label: unit.label || ('#' + unit.id) }));
            UI.populateSelect(form.leaseId, state.leases, (lease) => ({ value: lease.id, label: `${lease.id} · ${lease.start}` }));
        };

        const openFormModal = (booking = null) => {
            const isEdit = Boolean(booking);
            const modal = UI.createModal(isEdit ? t('immo', 'Edit booking') : t('immo', 'Create booking'));
            const form = document.createElement('form');
            form.className = 'immo-form immo-form-grid';
            form.innerHTML = `
                <label>${t('immo', 'Type')}<select name="type">
                    <option value="in">${t('immo', 'Income')}</option>
                    <option value="out">${t('immo', 'Expense')}</option>
                </select></label>
                <label>${t('immo', 'Category')}<input type="text" name="cat" required></label>
                <label>${t('immo', 'Amount')}<input type="number" step="0.01" name="amt" required></label>
                <label>${t('immo', 'Date')}<input type="date" name="date" required></label>
                <label>${t('immo', 'Property')}<select name="propId" required data-placeholder="${t('immo', 'Select property')}"></select></label>
                <label>${t('immo', 'Unit')}<select name="unitId" data-placeholder="${t('immo', 'Optional')}"></select></label>
                <label>${t('immo', 'Lease')}<select name="leaseId" data-placeholder="${t('immo', 'Optional')}"></select></label>
                <label>${t('immo', 'Recurring yearly')}<input type="checkbox" name="isYearly"></label>
                <label class="wide">${t('immo', 'Description')}<textarea name="desc"></textarea></label>
                <div class="immo-form-actions">
                    <button type="submit" class="primary">${t('immo', 'Save')}</button>
                    <button type="button" data-action="cancel">${t('immo', 'Cancel')}</button>
                </div>`;
            modal.setContent(form);
            populateRefs(form);
            if (isEdit) {
                modal.setTitle(t('immo', 'Edit booking') + ' #' + booking.id);
                UI.fillForm(form, booking);
            } else {
                form.date.value = new Date().toISOString().slice(0, 10);
                form.type.value = 'in';
            }
            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const payload = UI.formToJSON(form);
                const fn = isEdit ? Api.updateBooking(booking.id, payload) : Api.createBooking(payload);
                fn.then(() => {
                    UI.showSuccess(isEdit ? t('immo', 'Booking updated') : t('immo', 'Booking created'));
                    modal.close();
                    renderList();
                }).catch(err => UI.showError(listEl, err.message));
            });
            form.querySelector('[data-action="cancel"]').addEventListener('click', modal.close);
            modal.open();
        };

        const renderList = () => {
            UI.showLoading(listEl);
            const filter = {
                year: filterBar.year.value,
                type: filterBar.type.value,
            };
            Api.getBookings(filter).then(bookings => {
                state.bookings = bookings;
                State.cache.bookings = bookings;
                const propMap = {};
                state.props.forEach(prop => { propMap[prop.id] = prop; });
                UI.renderTable(listEl, {
                    columns: [
                        { id: 'date', label: t('immo', 'Date') },
                        {
                            id: 'category',
                            label: t('immo', 'Category'),
                            render: (booking) => escapeHtml(booking.type + ' · ' + (booking.cat || '')),
                            sortValue: (booking) => booking.cat || booking.type,
                        },
                        { id: 'amt', label: t('immo', 'Amount') },
                        {
                            id: 'property',
                            label: t('immo', 'Property'),
                            render: (booking) => escapeHtml(propMap[booking.propId] ? propMap[booking.propId].name : ('#' + booking.propId)),
                            sortValue: (booking) => propMap[booking.propId] ? propMap[booking.propId].name : booking.propId,
                        },
                        {
                            id: 'actions',
                            label: '',
                            sortable: false,
                            className: 'immo-table-actions',
                            render: (booking) => '<button data-action="edit" data-id="' + booking.id + '">' + t('immo', 'Edit') + '</button>' +
                                '<button data-action="delete" data-id="' + booking.id + '">' + t('immo', 'Delete') + '</button>',
                        },
                    ],
                    rows: bookings,
                    state: state.sort,
                    emptyMessage: t('immo', 'No bookings yet'),
                });
            }).catch(err => UI.showError(listEl, err.message));
        };

        filterBar.addEventListener('submit', (ev) => {
            ev.preventDefault();
            renderList();
        });

        listEl.addEventListener('click', (ev) => {
            const btn = ev.target.closest('button[data-action]');
            if (!btn) {
                return;
            }
            const id = parseInt(btn.dataset.id, 10);
            if (btn.dataset.action === 'edit') {
                const booking = state.bookings.find(b => b.id === id);
                if (booking) {
                    openFormModal(booking);
                }
            } else if (btn.dataset.action === 'delete') {
                UI.confirm(t('immo', 'Delete this booking?')).then(ok => {
                    if (!ok) {
                        return;
                    }
                    Api.deleteBooking(id).then(() => {
                        UI.showSuccess(t('immo', 'Booking deleted'));
                        renderList();
                    }).catch(err => UI.showError(listEl, err.message));
                });
            }
        });

        Promise.all([Api.getProps(), Api.getUnits(), Api.getLeases()]).then(([props, units, leases]) => {
            state.props = props;
            state.units = units;
            state.leases = leases;
            renderList();
        }).catch(err => UI.showError(listEl, err.message));
        if (addBtn) {
            addBtn.addEventListener('click', () => openFormModal());
        }
    };
    Views.reports = (root) => {
        const listEl = root.querySelector('#immo-report-list');
        const addBtn = root.querySelector('#immo-report-add');
        if (!listEl) {
            return;
        }
        const filterForm = document.createElement('form');
        filterForm.className = 'immo-filterbar';
        filterForm.innerHTML = `
            <label>${t('immo', 'Property')}<select name="propId" data-placeholder="${t('immo', 'All properties')}"></select></label>
            <label>${t('immo', 'Year')}<input type="number" name="year" value="${escapeHtml(new Date().getFullYear())}"></label>
            <button type="submit" class="primary">${t('immo', 'Filter')}</button>`;
        listEl.parentNode.insertBefore(filterForm, listEl);

        const state = { reports: [], props: [], sort: { sortKey: 'year', sortDir: 'desc' } };

        const populateProps = (select) => {
            UI.populateSelect(select, state.props, (prop) => ({ value: prop.id, label: prop.name }));
        };

        const openFormModal = () => {
            const modal = UI.createModal(t('immo', 'Create report'));
            const form = document.createElement('form');
            form.className = 'immo-form immo-form-grid';
            form.innerHTML = `
                <label>${t('immo', 'Property')}<select name="propId" required data-placeholder="${t('immo', 'Select property')}"></select></label>
                <label>${t('immo', 'Year')}<input type="number" name="year" value="${escapeHtml(new Date().getFullYear())}"></label>
                <div class="immo-form-actions">
                    <button class="primary" type="submit">${t('immo', 'Create report')}</button>
                    <button type="button" data-action="cancel">${t('immo', 'Cancel')}</button>
                </div>`;
            modal.setContent(form);
            populateProps(form.propId);
            form.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const payload = UI.formToJSON(form);
                Api.createReport(payload).then(() => {
                    UI.showSuccess(t('immo', 'Report created'));
                    modal.close();
                    renderList();
                }).catch(err => UI.showError(listEl, err.message));
            });
            form.querySelector('[data-action="cancel"]').addEventListener('click', modal.close);
            modal.open();
        };

        const renderList = () => {
            UI.showLoading(listEl);
            const filter = {
                propId: filterForm.propId.value,
                year: filterForm.year.value,
            };
            Api.getReports(filter).then(reports => {
                state.reports = reports;
                State.cache.reports = reports;
                const propMap = {};
                state.props.forEach(prop => { propMap[prop.id] = prop; });
                UI.renderTable(listEl, {
                    columns: [
                        {
                            id: 'property',
                            label: t('immo', 'Property'),
                            render: (report) => escapeHtml(propMap[report.propId] ? propMap[report.propId].name : ('#' + report.propId)),
                            sortValue: (report) => propMap[report.propId] ? propMap[report.propId].name : report.propId,
                        },
                        { id: 'year', label: t('immo', 'Year') },
                        { id: 'path', label: t('immo', 'File path') },
                    ],
                    rows: reports,
                    state: state.sort,
                    emptyMessage: t('immo', 'No reports yet'),
                });
            }).catch(err => UI.showError(listEl, err.message));
        };

        filterForm.addEventListener('submit', (ev) => {
            ev.preventDefault();
            renderList();
        });

        Api.getProps().then(props => {
            state.props = props;
            populateProps(filterForm.propId);
            renderList();
        }).catch(err => UI.showError(listEl, err.message));

        if (addBtn) {
            addBtn.addEventListener('click', () => openFormModal());
        }
    };

    const Navigation = {
    const Navigation = {
        links: {},
        init() {
            const nav = document.getElementById('app-navigation');
            nav.innerHTML = '';
            const list = document.createElement('ul');
            const items = this.getItems();
            items.forEach(item => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = '#';
                a.textContent = item.label;
                a.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    this.navigate(item.view);
                });
                li.appendChild(a);
                list.appendChild(li);
                this.links[item.view] = li;
            });
            nav.appendChild(list);
        },
        getItems() {
            const managerItems = [
                { view: 'dashboard', label: t('immo', 'Dashboard') },
                { view: 'props', label: t('immo', 'Properties') },
                { view: 'units', label: t('immo', 'Units') },
                { view: 'tenants', label: t('immo', 'Tenants') },
                { view: 'leases', label: t('immo', 'Leases') },
                { view: 'books', label: t('immo', 'Bookings') },
                { view: 'reports', label: t('immo', 'Reports') },
            ];
            if (State.currentUserRole === 'mieter') {
                return [{ view: 'dashboard', label: t('immo', 'Dashboard') }, { view: 'reports', label: t('immo', 'Reports') }];
            }
            return managerItems;
        },
        setActive(view) {
            Object.keys(this.links).forEach(key => {
                this.links[key].classList.toggle('active', key === view);
            });
        },
        navigate(view) {
            const content = document.getElementById('app-content');
            State.currentView = view;
            content.innerHTML = '<div class="immo-loading">' + t('immo', 'Loading…') + '</div>';
            Api.loadView(view).then(html => {
                content.innerHTML = html;
                const root = content.querySelector('.immo-view') || content;
                if (Views[view]) {
                    Views[view](root);
                }
                this.setActive(view);
            }).catch(err => {
                UI.showError(content, err.message);
            });
        },
    };

    window.Immo = {
        Api,
        UI,
        Views,
        Navigation,
        State,
    };

    document.addEventListener('DOMContentLoaded', () => {
        Navigation.init();
        const defaultView = State.currentUserRole === 'mieter' ? 'dashboard' : 'dashboard';
        Navigation.navigate(defaultView);
    });
}(window, document));
