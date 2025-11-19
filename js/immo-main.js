/* global OC, t */
(function (window, document) {
    'use strict';

    const baseUrl = OC.generateUrl('/apps/immo');
    const escapeHtml = (value) => OC.Util.escapeHTML(value === null || value === undefined ? '' : String(value));

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
        const formWrapper = document.createElement('div');
        formWrapper.className = 'immo-panel';
        formWrapper.innerHTML = `
            <h3>${t('immo', 'Property form')}</h3>
            <form class="immo-form immo-form-grid" id="immo-prop-form">
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
                </div>
            </form>`;
        listEl.parentNode.insertBefore(formWrapper, listEl);
        const form = formWrapper.querySelector('form');
        const cancelBtn = form.querySelector('[data-action="cancel"]');
        const state = { editingId: null, props: [] };

        const setModeCreate = () => {
            state.editingId = null;
            formWrapper.querySelector('h3').textContent = t('immo', 'Create property');
            UI.resetForm(form);
        };

        const setModeEdit = (prop) => {
            state.editingId = prop.id;
            formWrapper.querySelector('h3').textContent = t('immo', 'Edit property') + ' #' + prop.id;
            UI.fillForm(form, prop);
        };

        form.addEventListener('submit', (ev) => {
            ev.preventDefault();
            const payload = UI.formToJSON(form);
            const action = state.editingId ? Api.updateProp(state.editingId, payload) : Api.createProp(payload);
            action.then(() => {
                UI.showSuccess(state.editingId ? t('immo', 'Property updated') : t('immo', 'Property created'));
                setModeCreate();
                renderList();
            }).catch(err => UI.showError(listEl, err.message));
        });
        cancelBtn.addEventListener('click', () => setModeCreate());
        if (addBtn) {
            addBtn.addEventListener('click', () => setModeCreate());
        }

        const renderList = () => {
            UI.showLoading(listEl);
            Api.getProps().then(props => {
                State.cache.props = props;
                state.props = props;
                if (!props.length) {
                    UI.showEmpty(listEl, t('immo', 'No properties yet'));
                    return;
                }
                let html = '<table class="immo-table"><thead><tr>' +
                    '<th>' + t('immo', 'Name') + '</th>' +
                    '<th>' + t('immo', 'Address') + '</th>' +
                    '<th>' + t('immo', 'Type') + '</th>' +
                    '<th></th></tr></thead><tbody>';
                props.forEach(prop => {
                    const addr = [prop.street, prop.zip, prop.city].filter(Boolean).join(', ');
                    html += '<tr>' +
                        '<td>' + escapeHtml(prop.name) + '</td>' +
                        '<td>' + escapeHtml(addr) + '</td>' +
                        '<td>' + escapeHtml(prop.type || '') + '</td>' +
                        '<td class="immo-table-actions">' +
                        '<button data-action="edit" data-id="' + prop.id + '">' + t('immo', 'Edit') + '</button>' +
                        '<button data-action="delete" data-id="' + prop.id + '">' + t('immo', 'Delete') + '</button>' +
                        '</td></tr>';
                });
                html += '</tbody></table>';
                listEl.innerHTML = html;
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
                    setModeEdit(prop);
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

        setModeCreate();
        renderList();
    };

    Views.units = (root) => {
        const listEl = root.querySelector('#immo-unit-list');
        if (!listEl) {
            return;
        }
        const toolbar = document.createElement('div');
        toolbar.className = 'immo-toolbar';
        toolbar.innerHTML = `<button type="button" class="primary">${t('immo', 'New unit')}</button>`;
        root.insertBefore(toolbar, listEl);
        const formWrapper = document.createElement('div');
        formWrapper.className = 'immo-panel';
        formWrapper.innerHTML = `
            <h3>${t('immo', 'Unit form')}</h3>
            <form class="immo-form immo-form-grid">
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
                </div>
            </form>`;
        listEl.parentNode.insertBefore(formWrapper, listEl);
        const form = formWrapper.querySelector('form');
        const cancelBtn = form.querySelector('[data-action="cancel"]');
        const addBtn = toolbar.querySelector('button');
        const state = { editingId: null, props: [], units: [] };

        const setModeCreate = () => {
            state.editingId = null;
            formWrapper.querySelector('h3').textContent = t('immo', 'Create unit');
            UI.resetForm(form);
            if (state.props.length) {
                form.propId.value = String(state.props[0].id);
            }
        };

        const setModeEdit = (unit) => {
            state.editingId = unit.id;
            formWrapper.querySelector('h3').textContent = t('immo', 'Edit unit') + ' #' + unit.id;
            UI.fillForm(form, unit);
        };

        const populateProps = () => {
            UI.populateSelect(form.propId, state.props, (prop) => ({ value: prop.id, label: `${prop.name} (${prop.city || ''})` }));
        };

        const renderList = () => {
            UI.showLoading(listEl);
            Api.getUnits().then(units => {
                state.units = units;
                State.cache.units = units;
                const propMap = {};
                state.props.forEach(prop => { propMap[prop.id] = prop; });
                if (!units.length) {
                    UI.showEmpty(listEl, t('immo', 'No units yet'));
                    return;
                }
                let html = '<table class="immo-table"><thead><tr>' +
                    '<th>' + t('immo', 'Label') + '</th>' +
                    '<th>' + t('immo', 'Property') + '</th>' +
                    '<th>' + t('immo', 'Living area m²') + '</th>' +
                    '<th>' + t('immo', 'Type') + '</th>' +
                    '<th></th></tr></thead><tbody>';
                units.forEach(unit => {
                    const prop = propMap[unit.propId];
                    html += '<tr>' +
                        '<td>' + escapeHtml(unit.label) + '</td>' +
                        '<td>' + escapeHtml(prop ? prop.name : ('#' + unit.propId)) + '</td>' +
                        '<td>' + escapeHtml(unit.areaRes || '') + '</td>' +
                        '<td>' + escapeHtml(unit.type || '') + '</td>' +
                        '<td class="immo-table-actions">' +
                        '<button data-action="edit" data-id="' + unit.id + '">' + t('immo', 'Edit') + '</button>' +
                        '<button data-action="delete" data-id="' + unit.id + '">' + t('immo', 'Delete') + '</button>' +
                        '</td></tr>';
                });
                html += '</tbody></table>';
                listEl.innerHTML = html;
            }).catch(err => UI.showError(listEl, err.message));
        };

        form.addEventListener('submit', (ev) => {
            ev.preventDefault();
            const payload = UI.formToJSON(form);
            const requestFn = state.editingId ? Api.updateUnit(state.editingId, payload) : Api.createUnit(payload);
            requestFn.then(() => {
                UI.showSuccess(state.editingId ? t('immo', 'Unit updated') : t('immo', 'Unit created'));
                setModeCreate();
                renderList();
            }).catch(err => UI.showError(listEl, err.message));
        });
        cancelBtn.addEventListener('click', () => setModeCreate());
        addBtn.addEventListener('click', () => setModeCreate());

        listEl.addEventListener('click', (ev) => {
            const btn = ev.target.closest('button[data-action]');
            if (!btn) {
                return;
            }
            const id = parseInt(btn.dataset.id, 10);
            if (btn.dataset.action === 'edit') {
                const unit = state.units.find(u => u.id === id);
                if (unit) {
                    setModeEdit(unit);
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
            populateProps();
        }).catch(() => {
            state.props = [];
        }).finally(() => {
            setModeCreate();
            renderList();
        });
    };

    Views.tenants = (root) => {
        const listEl = root.querySelector('#immo-tenant-list');
        if (!listEl) {
            return;
        }
        const formWrapper = document.createElement('div');
        formWrapper.className = 'immo-panel';
        formWrapper.innerHTML = `
            <h3>${t('immo', 'Tenant form')}</h3>
            <form class="immo-form immo-form-grid">
                <label>${t('immo', 'Name')}<input type="text" name="name" required></label>
                <label>${t('immo', 'Customer number')}<input type="text" name="custNo"></label>
                <label>${t('immo', 'Email')}<input type="email" name="email"></label>
                <label>${t('immo', 'Phone')}<input type="text" name="phone"></label>
                <label class="wide">${t('immo', 'Address')}<textarea name="addr"></textarea></label>
                <label class="wide">${t('immo', 'Notes')}<textarea name="note"></textarea></label>
                <div class="immo-form-actions">
                    <button type="submit" class="primary">${t('immo', 'Save')}</button>
                    <button type="button" data-action="cancel">${t('immo', 'Cancel')}</button>
                </div>
            </form>`;
        listEl.parentNode.insertBefore(formWrapper, listEl);
        const form = formWrapper.querySelector('form');
        const cancelBtn = form.querySelector('[data-action="cancel"]');
        const state = { editingId: null, tenants: [] };

        const setModeCreate = () => {
            state.editingId = null;
            formWrapper.querySelector('h3').textContent = t('immo', 'Create tenant');
            UI.resetForm(form);
        };

        const setModeEdit = (tenant) => {
            state.editingId = tenant.id;
            formWrapper.querySelector('h3').textContent = t('immo', 'Edit tenant') + ' #' + tenant.id;
            UI.fillForm(form, tenant);
        };

        const renderList = () => {
            UI.showLoading(listEl);
            Api.getTenants().then(tenants => {
                state.tenants = tenants;
                State.cache.tenants = tenants;
                if (!tenants.length) {
                    UI.showEmpty(listEl, t('immo', 'No tenants yet'));
                    return;
                }
                let html = '<table class="immo-table"><thead><tr>' +
                    '<th>' + t('immo', 'Name') + '</th>' +
                    '<th>' + t('immo', 'Contact') + '</th>' +
                    '<th>' + t('immo', 'Customer number') + '</th>' +
                    '<th></th></tr></thead><tbody>';
                tenants.forEach(tenant => {
                    const contact = [tenant.email, tenant.phone].filter(Boolean).join(' · ');
                    html += '<tr>' +
                        '<td>' + escapeHtml(tenant.name) + '</td>' +
                        '<td>' + escapeHtml(contact) + '</td>' +
                        '<td>' + escapeHtml(tenant.custNo || '') + '</td>' +
                        '<td class="immo-table-actions">' +
                        '<button data-action="edit" data-id="' + tenant.id + '">' + t('immo', 'Edit') + '</button>' +
                        '<button data-action="delete" data-id="' + tenant.id + '">' + t('immo', 'Delete') + '</button>' +
                        '</td></tr>';
                });
                html += '</tbody></table>';
                listEl.innerHTML = html;
            }).catch(err => UI.showError(listEl, err.message));
        };

        form.addEventListener('submit', (ev) => {
            ev.preventDefault();
            const payload = UI.formToJSON(form);
            const fn = state.editingId ? Api.updateTenant(state.editingId, payload) : Api.createTenant(payload);
            fn.then(() => {
                UI.showSuccess(state.editingId ? t('immo', 'Tenant updated') : t('immo', 'Tenant created'));
                setModeCreate();
                renderList();
            }).catch(err => UI.showError(listEl, err.message));
        });
        cancelBtn.addEventListener('click', () => setModeCreate());

        listEl.addEventListener('click', (ev) => {
            const btn = ev.target.closest('button[data-action]');
            if (!btn) {
                return;
            }
            const id = parseInt(btn.dataset.id, 10);
            if (btn.dataset.action === 'edit') {
                const tenant = state.tenants.find(tn => tn.id === id);
                if (tenant) {
                    setModeEdit(tenant);
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

        setModeCreate();
        renderList();
    };

    Views.leases = (root) => {
        const listEl = root.querySelector('#immo-lease-list');
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

        const formWrapper = document.createElement('div');
        formWrapper.className = 'immo-panel';
        formWrapper.innerHTML = `
            <h3>${t('immo', 'Lease form')}</h3>
            <form class="immo-form immo-form-grid">
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
                </div>
            </form>`;
        listEl.parentNode.insertBefore(formWrapper, listEl);
        const form = formWrapper.querySelector('form');
        const cancelBtn = form.querySelector('[data-action="cancel"]');
        const state = { editingId: null, leases: [], units: [], tenants: [] };

        const setModeCreate = () => {
            state.editingId = null;
            formWrapper.querySelector('h3').textContent = t('immo', 'Create lease');
            UI.resetForm(form);
            const today = new Date().toISOString().slice(0, 10);
            form.start.value = today;
        };

        const setModeEdit = (lease) => {
            state.editingId = lease.id;
            formWrapper.querySelector('h3').textContent = t('immo', 'Edit lease') + ' #' + lease.id;
            UI.fillForm(form, lease);
        };

        const populateRefs = () => {
            UI.populateSelect(form.unitId, state.units, (unit) => {
                const unitLabel = unit.label ? unit.label : '#' + unit.id;
                return { value: unit.id, label: `${unitLabel} (#${unit.id})` };
            });
            UI.populateSelect(form.tenantId, state.tenants, (tenant) => ({ value: tenant.id, label: tenant.name }));
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
                if (!leases.length) {
                    UI.showEmpty(listEl, t('immo', 'No leases yet'));
                    return;
                }
                const unitMap = {};
                state.units.forEach(unit => { unitMap[unit.id] = unit; });
                const tenantMap = {};
                state.tenants.forEach(tenant => { tenantMap[tenant.id] = tenant; });
                let html = '<table class="immo-table"><thead><tr>' +
                    '<th>' + t('immo', 'Tenant') + '</th>' +
                    '<th>' + t('immo', 'Unit') + '</th>' +
                    '<th>' + t('immo', 'Period') + '</th>' +
                    '<th>' + t('immo', 'Cold rent') + '</th>' +
                    '<th>' + t('immo', 'Status') + '</th>' +
                    '<th></th></tr></thead><tbody>';
                leases.forEach(lease => {
                    const tenant = tenantMap[lease.tenantId];
                    const unit = unitMap[lease.unitId];
                    const period = [lease.start, lease.end || '…'].filter(Boolean).join(' – ');
                    html += '<tr>' +
                        '<td>' + escapeHtml(tenant ? tenant.name : ('#' + lease.tenantId)) + '</td>' +
                        '<td>' + escapeHtml(unit ? unit.label : ('#' + lease.unitId)) + '</td>' +
                        '<td>' + escapeHtml(period) + '</td>' +
                        '<td>' + escapeHtml(lease.rentCold || '') + '</td>' +
                        '<td><span class="immo-tag">' + escapeHtml(lease.status) + '</span></td>' +
                        '<td class="immo-table-actions">' +
                        '<button data-action="edit" data-id="' + lease.id + '">' + t('immo', 'Edit') + '</button>' +
                        '<button data-action="delete" data-id="' + lease.id + '">' + t('immo', 'Delete') + '</button>' +
                        '</td></tr>';
                });
                html += '</tbody></table>';
                listEl.innerHTML = html;
            }).catch(err => UI.showError(listEl, err.message));
        };

        form.addEventListener('submit', (ev) => {
            ev.preventDefault();
            const payload = UI.formToJSON(form);
            const fn = state.editingId ? Api.updateLease(state.editingId, payload) : Api.createLease(payload);
            fn.then(() => {
                UI.showSuccess(state.editingId ? t('immo', 'Lease updated') : t('immo', 'Lease created'));
                setModeCreate();
                renderList();
            }).catch(err => UI.showError(listEl, err.message));
        });
        cancelBtn.addEventListener('click', () => setModeCreate());
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
                    setModeEdit(lease);
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
            populateRefs();
            renderList();
        }).catch(err => UI.showError(listEl, err.message));
        setModeCreate();
    };

    Views.books = (root) => {
        const listEl = root.querySelector('#immo-booking-list');
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

        const formWrapper = document.createElement('div');
        formWrapper.className = 'immo-panel';
        formWrapper.innerHTML = `
            <h3>${t('immo', 'Booking form')}</h3>
            <form class="immo-form immo-form-grid">
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
                </div>
            </form>`;
        listEl.parentNode.insertBefore(formWrapper, listEl);
        const form = formWrapper.querySelector('form');
        const cancelBtn = form.querySelector('[data-action="cancel"]');
        const state = { editingId: null, bookings: [], props: [], units: [], leases: [] };

        const populateRefs = () => {
            UI.populateSelect(form.propId, state.props, (prop) => ({ value: prop.id, label: prop.name }));
            UI.populateSelect(form.unitId, state.units, (unit) => ({ value: unit.id, label: unit.label || ('#' + unit.id) }));
            UI.populateSelect(form.leaseId, state.leases, (lease) => ({ value: lease.id, label: `${lease.id} · ${lease.start}` }));
        };

        const setModeCreate = () => {
            state.editingId = null;
            formWrapper.querySelector('h3').textContent = t('immo', 'Create booking');
            UI.resetForm(form);
            form.date.value = new Date().toISOString().slice(0, 10);
            form.type.value = 'in';
        };

        const setModeEdit = (booking) => {
            state.editingId = booking.id;
            formWrapper.querySelector('h3').textContent = t('immo', 'Edit booking') + ' #' + booking.id;
            UI.fillForm(form, booking);
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
                if (!bookings.length) {
                    UI.showEmpty(listEl, t('immo', 'No bookings yet'));
                    return;
                }
                const propMap = {};
                state.props.forEach(prop => { propMap[prop.id] = prop; });
                let html = '<table class="immo-table"><thead><tr>' +
                    '<th>' + t('immo', 'Date') + '</th>' +
                    '<th>' + t('immo', 'Category') + '</th>' +
                    '<th>' + t('immo', 'Amount') + '</th>' +
                    '<th>' + t('immo', 'Property') + '</th>' +
                    '<th></th></tr></thead><tbody>';
                bookings.forEach(booking => {
                    html += '<tr>' +
                        '<td>' + escapeHtml(booking.date) + '</td>' +
                        '<td>' + escapeHtml(booking.type + ' · ' + (booking.cat || '')) + '</td>' +
                        '<td>' + escapeHtml(booking.amt) + '</td>' +
                        '<td>' + escapeHtml(propMap[booking.propId] ? propMap[booking.propId].name : ('#' + booking.propId)) + '</td>' +
                        '<td class="immo-table-actions">' +
                        '<button data-action="edit" data-id="' + booking.id + '">' + t('immo', 'Edit') + '</button>' +
                        '<button data-action="delete" data-id="' + booking.id + '">' + t('immo', 'Delete') + '</button>' +
                        '</td></tr>';
                });
                html += '</tbody></table>';
                listEl.innerHTML = html;
            }).catch(err => UI.showError(listEl, err.message));
        };

        form.addEventListener('submit', (ev) => {
            ev.preventDefault();
            const payload = UI.formToJSON(form);
            const fn = state.editingId ? Api.updateBooking(state.editingId, payload) : Api.createBooking(payload);
            fn.then(() => {
                UI.showSuccess(state.editingId ? t('immo', 'Booking updated') : t('immo', 'Booking created'));
                setModeCreate();
                renderList();
            }).catch(err => UI.showError(listEl, err.message));
        });
        cancelBtn.addEventListener('click', () => setModeCreate());
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
                    setModeEdit(booking);
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

        setModeCreate();
        Promise.all([Api.getProps(), Api.getUnits(), Api.getLeases()]).then(([props, units, leases]) => {
            state.props = props;
            state.units = units;
            state.leases = leases;
            populateRefs();
            renderList();
        }).catch(err => UI.showError(listEl, err.message));
    };

    Views.reports = (root) => {
        const listEl = root.querySelector('#immo-report-list');
        const createForm = root.querySelector('#immo-report-create');
        if (!listEl || !createForm) {
            return;
        }
        const filterForm = document.createElement('form');
        filterForm.className = 'immo-filterbar';
        filterForm.innerHTML = `
            <label>${t('immo', 'Property')}<select name="propId" data-placeholder="${t('immo', 'All properties')}"></select></label>
            <label>${t('immo', 'Year')}<input type="number" name="year" value="${escapeHtml(new Date().getFullYear())}"></label>
            <button type="submit" class="primary">${t('immo', 'Filter')}</button>`;
        listEl.parentNode.insertBefore(filterForm, listEl);

        const state = { reports: [], props: [] };

        const populateProps = () => {
            UI.populateSelect(filterForm.propId, state.props, (prop) => ({ value: prop.id, label: prop.name }));
            UI.populateSelect(createForm.propId, state.props, (prop) => ({ value: prop.id, label: prop.name }));
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
                if (!reports.length) {
                    UI.showEmpty(listEl, t('immo', 'No reports yet'));
                    return;
                }
                const propMap = {};
                state.props.forEach(prop => { propMap[prop.id] = prop; });
                let html = '<table class="immo-table"><thead><tr>' +
                    '<th>' + t('immo', 'Property') + '</th>' +
                    '<th>' + t('immo', 'Year') + '</th>' +
                    '<th>' + t('immo', 'File path') + '</th>' +
                    '</tr></thead><tbody>';
                reports.forEach(report => {
                    html += '<tr>' +
                        '<td>' + escapeHtml(propMap[report.propId] ? propMap[report.propId].name : ('#' + report.propId)) + '</td>' +
                        '<td>' + escapeHtml(report.year) + '</td>' +
                        '<td>' + escapeHtml(report.path) + '</td>' +
                        '</tr>';
                });
                html += '</tbody></table>';
                listEl.innerHTML = html;
            }).catch(err => UI.showError(listEl, err.message));
        };

        createForm.addEventListener('submit', (ev) => {
            ev.preventDefault();
            const payload = UI.formToJSON(createForm);
            Api.createReport(payload).then(() => {
                UI.showSuccess(t('immo', 'Report created'));
                renderList();
            }).catch(err => UI.showError(listEl, err.message));
        });
        filterForm.addEventListener('submit', (ev) => {
            ev.preventDefault();
            renderList();
        });

        Api.getProps().then(props => {
            state.props = props;
            populateProps();
            renderList();
        }).catch(err => UI.showError(listEl, err.message));
    };

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
