/* global t, OC */
(function() {
    window.ImmoApp = window.ImmoApp || {};

    ImmoApp.State = {
        currentUser: {
            userId: null,
            role: 'none',
        },
        ui: {
            currentRoute: '#/dashboard',
            currentView: null,
        },
        cache: {
            properties: new Map(),
        },
    };

    ImmoApp.Util = (function() {
        function escapeHtml(str) {
            if (str === null || str === undefined) {
                return '';
            }
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatMoney(amount) {
            if (amount === null || amount === undefined) {
                return '';
            }
            return amount.toLocaleString(undefined, { style: 'currency', currency: 'EUR', minimumFractionDigits: 2 });
        }

        function html(strings, ...values) {
            return strings.reduce((acc, str, idx) => {
                const value = idx < values.length ? escapeHtml(values[idx]) : '';
                return acc + str + value;
            }, '');
        }

        return { escapeHtml, formatMoney, html };
    })();

    ImmoApp.Api = (function() {
        const BASE = OC.generateUrl('/apps/immoapp/api');

        function buildUrl(path, params) {
            const url = new URL(BASE + path, window.location.origin);
            if (params) {
                Object.keys(params).forEach(key => {
                    const value = params[key];
                    if (value !== undefined && value !== null && value !== '') {
                        url.searchParams.append(key, value);
                    }
                });
            }
            return url.toString();
        }

        async function request(method, path, { params, body } = {}) {
            const headers = { 'OCS-APIREQUEST': 'true' };
            const options = {
                method,
                headers,
                credentials: 'same-origin',
            };
            if (body) {
                headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(body);
            }
            const response = await fetch(buildUrl(path, params), options);

            if (response.status === 204) {
                return null;
            }

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data && data.message ? data.message : 'Request failed');
            }

            return data;
        }

        return {
            me: () => request('GET', '/me'),
            getDashboardStats: params => request('GET', '/dashboard/stats', { params }),
            getProperties: () => request('GET', '/properties'),
            createProperty: body => request('POST', '/properties', { body }),
            getReports: params => request('GET', '/reports', { params }),
            createReport: body => request('POST', '/reports', { body }),
        };
    })();

    ImmoApp.Router = (function() {
        const routes = [];

        function addRoute(pattern, config) {
            routes.push({ pattern, config });
        }

        function resolve(hash) {
            const route = routes.find(r => r.pattern === hash);
            return route ? route.config : null;
        }

        function handle() {
            const fallback = ImmoApp.State.currentUser.role === 'tenant' ? '#/my-tenancies' : '#/dashboard';
            const hash = window.location.hash || fallback;
            const config = resolve(hash);
            const content = document.getElementById('immoapp-content');
            if (!content) {
                return;
            }

            if (!config) {
                content.innerHTML = '<p>' + ImmoApp.Util.escapeHtml(t('immoapp', 'Page not found')) + '</p>';
                return;
            }

            if (ImmoApp.State.ui.currentView && ImmoApp.State.ui.currentView.destroy) {
                ImmoApp.State.ui.currentView.destroy();
            }

            ImmoApp.State.ui.currentRoute = hash;
            ImmoApp.State.ui.currentView = config.view;
            config.view.init(content);
        }

        function init() {
            window.addEventListener('hashchange', handle);
            handle();
        }

        return { addRoute, init };
    })();

    ImmoApp.Views = ImmoApp.Views || {};

    ImmoApp.Views.Dashboard = (function() {
        async function init(root) {
            root.innerHTML = '<div class="section"><h2>' + ImmoApp.Util.escapeHtml(t('immoapp', 'Dashboard')) + '</h2><div class="icon-loading"></div></div>';
            try {
                const stats = await ImmoApp.Api.getDashboardStats({ year: new Date().getFullYear() });
                root.innerHTML = `
                    <div class="section">
                        <h2>${ImmoApp.Util.escapeHtml(t('immoapp', 'Dashboard'))}</h2>
                        <div class="dashboard-grid">
                            <div class="dashboard-card">
                                <h3>${ImmoApp.Util.escapeHtml(t('immoapp', 'Properties'))}</h3>
                                <div class="dashboard-value">${stats.counts.properties}</div>
                            </div>
                            <div class="dashboard-card">
                                <h3>${ImmoApp.Util.escapeHtml(t('immoapp', 'Units'))}</h3>
                                <div class="dashboard-value">${stats.counts.units}</div>
                            </div>
                            <div class="dashboard-card">
                                <h3>${ImmoApp.Util.escapeHtml(t('immoapp', 'Active tenancies'))}</h3>
                                <div class="dashboard-value">${stats.counts.activeTenancies}</div>
                            </div>
                            <div class="dashboard-card">
                                <h3>${ImmoApp.Util.escapeHtml(t('immoapp', 'Annual cold rent'))}</h3>
                                <div class="dashboard-value">${ImmoApp.Util.formatMoney(stats.rent.annualColdRent)}</div>
                            </div>
                        </div>
                        <div class="section">
                            <h3>${ImmoApp.Util.escapeHtml(t('immoapp', 'Cashflow'))}</h3>
                            <p>${ImmoApp.Util.escapeHtml(t('immoapp', 'Income'))}: ${ImmoApp.Util.formatMoney(stats.cashflow.income)}</p>
                            <p>${ImmoApp.Util.escapeHtml(t('immoapp', 'Expenses'))}: ${ImmoApp.Util.formatMoney(stats.cashflow.expense)}</p>
                            <p>${ImmoApp.Util.escapeHtml(t('immoapp', 'Net'))}: ${ImmoApp.Util.formatMoney(stats.cashflow.net)}</p>
                        </div>
                    </div>
                `;
            } catch (error) {
                root.innerHTML = '<div class="error">' + ImmoApp.Util.escapeHtml(error.message) + '</div>';
            }
        }

        function destroy() {}

        return { init, destroy };
    })();

    ImmoApp.Views.Properties = (function() {
        async function init(root) {
            root.innerHTML = '<div class="section"><h2>' + ImmoApp.Util.escapeHtml(t('immoapp', 'Properties')) + '</h2><div class="icon-loading"></div></div>';
            const properties = await ImmoApp.Api.getProperties();
            const rows = properties.map(property => `
                <tr>
                    <td>${ImmoApp.Util.escapeHtml(property.name)}</td>
                    <td>${ImmoApp.Util.escapeHtml(property.city || '')}</td>
                    <td>${ImmoApp.Util.escapeHtml(property.type || '')}</td>
                </tr>
            `).join('');
            root.innerHTML = `
                <div class="section">
                    <h2>${ImmoApp.Util.escapeHtml(t('immoapp', 'Properties'))}</h2>
                    <table class="grid">
                        <thead>
                            <tr>
                                <th>${ImmoApp.Util.escapeHtml(t('immoapp', 'Name'))}</th>
                                <th>${ImmoApp.Util.escapeHtml(t('immoapp', 'City'))}</th>
                                <th>${ImmoApp.Util.escapeHtml(t('immoapp', 'Type'))}</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        }

        function destroy() {}

        return { init, destroy };
    })();

    ImmoApp.Views.Accounting = (function() {
        async function init(root) {
            root.innerHTML = '<div class="section"><h2>' + ImmoApp.Util.escapeHtml(t('immoapp', 'Accounting')) + '</h2><div class="icon-loading"></div></div>';
            const year = new Date().getFullYear();
            const reports = await ImmoApp.Api.getReports({ year });
            const items = reports.map(report => `
                <li>
                    <strong>${ImmoApp.Util.escapeHtml(report.year.toString())}</strong>
                    – ${ImmoApp.Util.escapeHtml(report.path)}
                </li>
            `).join('');
            root.innerHTML = `
                <div class="section">
                    <h2>${ImmoApp.Util.escapeHtml(t('immoapp', 'Accounting'))}</h2>
                    <p>${ImmoApp.Util.escapeHtml(t('immoapp', 'Reports for year {year}', { year }))}</p>
                    <ul class="reports-list">${items}</ul>
                </div>
            `;
        }

        function destroy() {}

        return { init, destroy };
    })();

    ImmoApp.Views.MyTenancies = (function() {
        function init(root) {
            root.innerHTML = '<div class="section"><h2>' + ImmoApp.Util.escapeHtml(t('immoapp', 'My tenancies')) + '</h2><p>' + ImmoApp.Util.escapeHtml(t('immoapp', 'Tenant specific endpoints will be implemented in a later iteration.')) + '</p></div>';
        }

        function destroy() {}

        return { init, destroy };
    })();

    function registerRoutes(role) {
        if (role === 'manager') {
            ImmoApp.Router.addRoute('#/dashboard', { view: ImmoApp.Views.Dashboard });
            ImmoApp.Router.addRoute('#/properties', { view: ImmoApp.Views.Properties });
            ImmoApp.Router.addRoute('#/accounting', { view: ImmoApp.Views.Accounting });
        }

        ImmoApp.Router.addRoute('#/my-tenancies', { view: ImmoApp.Views.MyTenancies });
        ImmoApp.Router.addRoute('#/my-reports', { view: ImmoApp.Views.Accounting });
    }

    function applyNavigationRole(role) {
        const items = document.querySelectorAll('.immoapp-sidebar li');
        items.forEach(item => {
            const itemRole = item.getAttribute('data-role');
            if (!itemRole) {
                return;
            }
            if (role === 'manager') {
                item.style.display = itemRole === 'tenant' ? 'none' : '';
            } else if (role === 'tenant') {
                item.style.display = itemRole === 'manager' ? 'none' : '';
            } else {
                item.style.display = '';
            }
        });
    }

    async function bootstrap() {
        try {
            const me = await ImmoApp.Api.me();
            ImmoApp.State.currentUser = me;
            registerRoutes(me.role);
            applyNavigationRole(me.role);
            ImmoApp.Router.init();
        } catch (error) {
            const content = document.getElementById('immoapp-content');
            if (content) {
                content.innerHTML = '<div class="error">' + ImmoApp.Util.escapeHtml(error.message) + '</div>';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', bootstrap);
})();
