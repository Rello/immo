window.Immo = window.Immo || {};

(() => {
    const { t } = window;

    Immo.Api = (() => {
        const request = async (method, url, data = null, expectHtml = false) => {
            const options = {
                method,
                headers: {
                    'OCS-APIREQUEST': 'true',
                    'requesttoken': OC.requestToken
                }
            };
            if (data !== null && !(data instanceof FormData)) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
            const res = await fetch(url, options);
            if (!res.ok) {
                const text = await res.text();
                throw new Error(text || res.statusText);
            }
            return expectHtml ? res.text() : res.json();
        };
        return {
            getHtml: (url) => request('GET', url, null, true),
            getJson: (url) => request('GET', url),
            postJson: (url, data) => request('POST', url, data),
            putJson: (url, data) => request('PUT', url, data),
            delete: (url) => request('DELETE', url)
        };
    })();

    Immo.StateStore = (() => {
        const state = {
            currentRoute: 'dashboard',
            selectedYear: new Date().getFullYear(),
        };
        const listeners = {};
        const notify = (key) => {
            (listeners[key] || []).forEach(cb => cb(state[key]));
        };
        return {
            get: (key) => state[key],
            set: (key, value) => {
                if (state[key] === value) return;
                state[key] = value;
                notify(key);
            },
            subscribe: (key, cb) => {
                listeners[key] = listeners[key] || [];
                listeners[key].push(cb);
            }
        };
    })();

    Immo.ViewRouter = (() => {
        const routes = {
            'dashboard': { view: Immo.Views.Dashboard, url: OC.generateUrl('/apps/immo/view/dashboard') },
            'properties': { view: Immo.Views.Properties, url: OC.generateUrl('/apps/immo/view/property-list') },
            'units': { view: Immo.Views.Units, url: OC.generateUrl('/apps/immo/view/unit-list') },
            'tenants': { view: Immo.Views.Tenants, url: OC.generateUrl('/apps/immo/view/tenant-list') },
            'leases': { view: Immo.Views.Leases, url: OC.generateUrl('/apps/immo/view/lease-list') },
            'bookings': { view: Immo.Views.Bookings, url: OC.generateUrl('/apps/immo/view/booking-list') },
            'reports': { view: Immo.Views.Reports, url: OC.generateUrl('/apps/immo/view/report-list') },
        };

        const setActiveNav = (route) => {
            document.querySelectorAll('#app-navigation li').forEach(li => {
                li.classList.toggle('active', li.dataset.route === route);
            });
        };

        const load = async (route, params = {}) => {
            const config = routes[route];
            if (!config) {
                console.error('Unknown route', route);
                return;
            }
            Immo.StateStore.set('currentRoute', route);
            setActiveNav(route);
            const content = document.getElementById('app-content');
            content.innerHTML = `<div class="icon-loading">${t('immo', 'Loading...')}</div>`;
            const query = new URLSearchParams(params).toString();
            const url = query ? `${config.url}?${query}` : config.url;
            try {
                const html = await Immo.Api.getHtml(url);
                content.innerHTML = html;
                config.view && config.view.bindEvents(params);
            } catch (e) {
                content.innerHTML = `<div class="error">${t('immo', 'Failed to load view')}</div>`;
            }
        };

        return { load };
    })();

    Immo.Views = Immo.Views || {};

    Immo.Views.Layout = (() => {
        const buildNav = () => {
            const nav = document.getElementById('app-navigation');
            const list = document.createElement('ul');
            const routes = [
                { key: 'dashboard', label: t('immo', 'Dashboard') },
                { key: 'properties', label: t('immo', 'Properties') },
                { key: 'units', label: t('immo', 'Units') },
                { key: 'tenants', label: t('immo', 'Tenants') },
                { key: 'leases', label: t('immo', 'Leases') },
                { key: 'bookings', label: t('immo', 'Bookings') },
                { key: 'reports', label: t('immo', 'Reports') },
            ];
            routes.forEach(item => {
                const li = document.createElement('li');
                li.dataset.route = item.key;
                li.textContent = item.label;
                li.addEventListener('click', () => Immo.ViewRouter.load(item.key));
                list.appendChild(li);
            });
            nav.appendChild(list);
            Immo.ViewRouter.load('dashboard');
        };
        return { buildNav };
    })();

    Immo.Views.Dashboard = (() => {
        const bindEvents = () => loadStats();
        const loadStats = async () => {
            const year = Immo.StateStore.get('selectedYear');
            const target = document.querySelector('[data-partial="dashboard"] h2');
            if (!target) return;
            try {
                const metrics = await Immo.Api.getJson(OC.generateUrl(`/apps/immo/api/dashboard?year=${year}`));
                target.textContent = `${t('immo', 'Dashboard')} (${metrics.properties} ${t('immo', 'properties')})`;
            } catch (e) {
                target.textContent = t('immo', 'Could not load dashboard');
            }
        };
        return { bindEvents };
    })();

    Immo.Views.Properties = (() => {
        const bindEvents = () => {
            loadList();
            const addBtn = document.querySelector('[data-action="prop-add"]');
            addBtn && addBtn.addEventListener('click', () => openForm());
        };

        const loadList = async () => {
            const container = document.getElementById('prop-list');
            try {
                const data = await Immo.Api.getJson(OC.generateUrl('/apps/immo/api/prop'));
                container.innerHTML = data.map(item => `<div class="row"><span>${item.name}</span><button data-action="prop-edit" data-id="${item.id}">${t('immo', 'Edit')}</button><button data-action="prop-delete" data-id="${item.id}">${t('immo', 'Delete')}</button></div>`).join('');
                container.querySelectorAll('[data-action="prop-edit"]').forEach(btn => btn.addEventListener('click', (e) => openForm(e.currentTarget.dataset.id)));
                container.querySelectorAll('[data-action="prop-delete"]').forEach(btn => btn.addEventListener('click', (e) => deleteProp(e.currentTarget.dataset.id)));
            } catch (e) {
                container.textContent = t('immo', 'Failed to load properties');
            }
        };

        const openForm = (id = null) => {
            const modal = document.getElementById('prop-form-modal');
            modal.classList.add('open');
            const form = modal.querySelector('form');
            form.reset();
            if (id) {
                populateForm(id);
            }
            form.onsubmit = (ev) => {
                ev.preventDefault();
                save(id, new FormData(form));
            };
        };

        const populateForm = async (id) => {
            const data = await Immo.Api.getJson(OC.generateUrl(`/apps/immo/api/prop/${id}`));
            const form = document.querySelector('#prop-form-modal form');
            Object.keys(data).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) input.value = data[key] || '';
            });
        };

        const save = async (id, formData) => {
            const payload = {};
            formData.forEach((v, k) => payload[k] = v);
            const url = id ? OC.generateUrl(`/apps/immo/api/prop/${id}`) : OC.generateUrl('/apps/immo/api/prop');
            const method = id ? Immo.Api.putJson : Immo.Api.postJson;
            try {
                await method(url, payload);
                OC.Notification.showTemporary(t('immo', 'Property saved'));
                document.getElementById('prop-form-modal').classList.remove('open');
                loadList();
            } catch (e) {
                OC.Notification.showTemporary(t('immo', 'Could not save property'));
            }
        };

        const deleteProp = async (id) => {
            try {
                await Immo.Api.delete(OC.generateUrl(`/apps/immo/api/prop/${id}`));
                loadList();
            } catch (e) {
                OC.Notification.showTemporary(t('immo', 'Delete failed'));
            }
        };

        return { bindEvents };
    })();

    Immo.Views.Units = Immo.Views.Tenants = Immo.Views.Leases = Immo.Views.Bookings = Immo.Views.Reports = (() => {
        const bindEvents = () => {};
        return { bindEvents };
    })();

    document.addEventListener('DOMContentLoaded', () => {
        Immo.Views.Layout.buildNav();
    });
})();
