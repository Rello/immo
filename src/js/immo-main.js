(function () {
    if (!window.OCA) {
        window.OCA = {};
    }
    if (!OCA.Immo) {
        OCA.Immo = {};
    }

    OCA.Immo.Api = (function () {
        async function request(url, options) {
            const response = await fetch(url, Object.assign({
                headers: {
                    'Accept': 'application/json',
                    'requesttoken': OC.requestToken,
                },
                credentials: 'same-origin',
            }, options));
            if (!response.ok) {
                throw new Error('Request failed');
            }
            return response.json();
        }

        return {
            request,
        };
    }());

    OCA.Immo.UI = (function () {
        function flash(message, type) {
            const container = document.querySelector('.immo-flash') || document.body;
            const el = document.createElement('div');
            el.className = 'immo-flash-message ' + (type || 'info');
            el.textContent = message;
            container.appendChild(el);
            setTimeout(() => el.remove(), 5000);
        }

        function bindFilterForms(root) {
            root.querySelectorAll('form[data-immo-filter]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const params = new URLSearchParams(new FormData(form));
                    window.location = window.location.pathname + '?' + params.toString();
                });
            });
        }

        return {
            flash,
            bindFilterForms,
        };
    }());

    OCA.Immo.Components = OCA.Immo.Components || {};
    OCA.Immo.Components.Admin = OCA.Immo.Components.Admin || {};
    OCA.Immo.Components.Tenant = OCA.Immo.Components.Tenant || {};

    OCA.Immo.Components.Admin.Dashboard = (function () {
        function init(root) {
            const year = root.getAttribute('data-initial-year');
            loadStats(year);
        }

        async function loadStats(year) {
            try {
                const response = await OCA.Immo.Api.request(OC.generateUrl('/ocs/v2.php/apps/immo/api/v1/stats/dashboard?year=' + year));
                document.querySelector('[data-immo-metric="rent"] .immo-metric-value').textContent = response.ocs.data.stats.rent || '0';
                document.querySelector('[data-immo-metric="expenses"] .immo-metric-value').textContent = response.ocs.data.stats.expenses || '0';
                document.querySelector('[data-immo-metric="vacancy"] .immo-metric-value').textContent = response.ocs.data.stats.vacancy || '0%';
            } catch (error) {
                console.error(error);
                OCA.Immo.UI.flash(t('immo', 'Failed to load statistics'), 'error');
            }
        }

        return { init };
    }());

    OCA.Immo.Components.Admin.StatementWizard = (function () {
        function init(root) {
            const form = root.querySelector('[data-immo-statement-wizard]');
            if (!form) {
                return;
            }
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const data = Object.fromEntries(new FormData(form).entries());
                const url = OC.generateUrl('/apps/immo/statements/generate');
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'requesttoken': OC.requestToken,
                    },
                    body: new URLSearchParams(data),
                });
                const json = await response.json();
                const target = root.querySelector('[data-immo-statement-result]');
                target.textContent = t('immo', 'Created statement {file}', { file: json.filePath || json.file_path || '' });
            });
        }

        return { init };
    }());

    OCA.Immo.Components.Tenant.Dashboard = (function () {
        function init(root) {
            const leasesContainer = root.querySelector('[data-immo-tenant-leases]');
            leasesContainer.textContent = t('immo', 'No leases connected yet.');
        }

        return { init };
    }());

    OCA.Immo.App = (function () {
        function init() {
            const root = document.getElementById('immo-app');
            if (!root) {
                return;
            }
            OCA.Immo.UI.bindFilterForms(root);
            const view = root.getAttribute('data-immo-view');
            switch (view) {
                case 'admin-dashboard':
                    OCA.Immo.Components.Admin.Dashboard.init(root);
                    break;
                case 'admin-statement-wizard':
                    OCA.Immo.Components.Admin.StatementWizard.init(root);
                    break;
                case 'tenant-dashboard':
                    if (OCA.Immo.Components.Tenant.Dashboard) {
                        OCA.Immo.Components.Tenant.Dashboard.init(root);
                    }
                    break;
                default:
                    break;
            }
        }

        document.addEventListener('DOMContentLoaded', init);
        return { init };
    }());
}());
