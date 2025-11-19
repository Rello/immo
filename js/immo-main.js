/* global OC, OCA, t */
(function (window) {
    'use strict';

    const baseUrl = OC.generateUrl('/apps/immo');

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
                    throw new Error(text || t('immo', 'Request failed'));
                });
            }
            return expectHtml ? response.text() : response.json();
        });
    };

    const Api = {
        getDashboard(year) {
            const qs = year ? '?year=' + encodeURIComponent(year) : '';
            return request('GET', '/api/dashboard' + qs);
        },
        loadView(view) {
            return request('GET', '/view/' + view, null, true);
        },
        getProps() {
            return request('GET', '/api/prop');
        },
        createProp(data) {
            return request('POST', '/api/prop', data);
        },
        getReports(filter = {}) {
            const params = [];
            if (filter.propId) params.push('propId=' + encodeURIComponent(filter.propId));
            if (filter.year) params.push('year=' + encodeURIComponent(filter.year));
            const qs = params.length ? '?' + params.join('&') : '';
            return request('GET', '/api/report' + qs);
        },
        createReport(data) {
            return request('POST', '/api/report', data);
        },
    };

    const UI = {
        showError(target, message) {
            target.innerHTML = '<div class="error">' + OC.Util.escapeHTML(message) + '</div>';
        },
        showProps(listEl, props) {
            if (!props.length) {
                listEl.innerHTML = '<p>' + t('immo', 'No properties yet') + '</p>';
                return;
            }
            let html = '<ul class="immo-list">';
            props.forEach(prop => {
                html += '<li>' + OC.Util.escapeHTML(prop.name || '') + '</li>';
            });
            html += '</ul>';
            listEl.innerHTML = html;
        },
    };

    const Views = {
        dashboard(container) {
            Api.getDashboard(new Date().getFullYear()).then(data => {
                container.innerHTML = '';
                const cards = document.createElement('div');
                cards.className = 'immo-cards';
                cards.innerHTML = `
                    <div class="card">${t('immo', 'Properties')}: ${data.propCount}</div>
                    <div class="card">${t('immo', 'Units')}: ${data.unitCount}</div>
                    <div class="card">${t('immo', 'Active leases')}: ${data.activeLeaseCount}</div>
                    <div class="card">${t('immo', 'Annual rent (cold)')}: ${data.annualRentSum}</div>`;
                container.appendChild(cards);
            }).catch(err => {
                UI.showError(container, err.message);
            });
        },
        props(container) {
            const list = container.querySelector('#immo-prop-list') || container;
            Api.getProps().then(props => {
                UI.showProps(list, props);
            }).catch(err => UI.showError(container, err.message));
        },
        reports(container) {
            const list = container.querySelector('#immo-report-list') || container;
            Api.getReports().then(reports => {
                if (!reports.length) {
                    list.innerHTML = '<p>' + t('immo', 'No reports yet') + '</p>';
                    return;
                }
                let html = '<ul>';
                reports.forEach(rep => {
                    html += '<li>' + OC.Util.escapeHTML(rep.path) + '</li>';
                });
                html += '</ul>';
                list.innerHTML = html;
            });
        },
    };

    const Navigation = {
        init() {
            const nav = document.getElementById('app-navigation');
            const list = document.createElement('ul');
            const items = [
                { id: 'dashboard', label: t('immo', 'Dashboard') },
                { id: 'props', label: t('immo', 'Properties') },
                { id: 'reports', label: t('immo', 'Reports') },
            ];
            items.forEach(item => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = '#';
                a.textContent = item.label;
                a.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    Navigation.navigate(item.id);
                });
                li.appendChild(a);
                list.appendChild(li);
            });
            nav.appendChild(list);
        },
        navigate(view) {
            const content = document.getElementById('app-content');
            content.innerHTML = '<div class="loading">' + t('immo', 'Loading…') + '</div>';
            Api.loadView(view).then(html => {
                content.innerHTML = html;
                const root = content.querySelector('.immo-view') || content;
                if (Views[view]) {
                    Views[view](root);
                } else if (view === 'dashboard') {
                    Views.dashboard(root);
                }
            }).catch(err => UI.showError(content, err.message));
        },
    };

    window.Immo = {
        Api,
        UI,
        Views,
        Navigation,
    };

    document.addEventListener('DOMContentLoaded', () => {
        Navigation.init();
        Navigation.navigate('dashboard');
    });
}(window));
