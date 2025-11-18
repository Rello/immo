## Komponentenplan (nur Frontend)

### 1. Komponenten (Module & Unter-Module)

Globales Namespace-Objekt:

```js
window.ImmoApp = window.ImmoApp || {};
```

#### 1.1 Core

- `ImmoApp.App`
  - Initialisiert App beim Laden des Templates (`DOMContentLoaded`).
  - Ruft `ImmoApp.Api.me()` auf, lädt Benutzerrolle/Konfiguration.
  - Initialisiert Router, setzt Start-Route (z. B. `#/dashboard` oder `#/my-tenancies` für Mieter).
  - Delegiert Navigation-Klicks an Router.

- `ImmoApp.Router`
  - Hash-basierter Router (`#/dashboard`, `#/properties`, `#/properties/:id`, …).
  - Mappt Route → View-Module + View-Mode (`list`, `detail`, `edit`, `new`).
  - Übergibt Parameter (ID, Filter) an View.
  - Kümmert sich um 404/„View nicht erlaubt“.

- `ImmoApp.Api`
  - Wrapper um `fetch`:
    - Basis-URL: `/apps/immoapp/api`.
    - Setzt `OCS-APIREQUEST: 'true'`.
    - JSON-Parsing, Fehlerbehandlung, einheitliche Fehlerrückgabe.
  - Methodensammlung:
    - `me()`
    - `getDashboardStats(params)`
    - `getProperties()`, `getProperty(id)`, `createProperty(data)`, `updateProperty(id, data)`, `deleteProperty(id)`
    - `getUnits(params)`, `createUnit(data)`, …
    - `getTenants()`, `createTenant(data)`, …
    - `getTenancies(params)`, `createTenancy(data)`, …
    - `getTransactions(params)`, `createTransaction(data)`
    - `getDocLinks(entityType, entityId)`, `createDocLink(data)`
    - `getReports(params)`, `createReport(data)`

- `ImmoApp.State`
  - Globaler UI- und Session-State (im RAM, kein Redux o. ä.):
    - `currentUser` (`userId`, `role`, `config`).
    - `ui`: `currentRoute`, `filters` (z. B. `year`, `propertyId`).
    - Caches: `propertiesById`, `unitsById`, optional `tenantsById` für schnelle Lookups.

- `ImmoApp.Util`
  - Helferfunktionen:
    - `formatDate(dateStr)`
    - `formatMoney(amount)`
    - `escapeHtml(str)`
    - Simple templating: `html(strings, ...values)` mit automatischem Escape.
    - Status-Bestimmung (optional, falls clientseitig nötig).

#### 1.2 Views

Jede View ist ein eigenes Modul mit der Signatur:

```js
ImmoApp.Views.X = (function() {
  function init(rootEl, routeParams, queryParams) {}
  function destroy() {}
  return { init, destroy };
})();
```

Gemeinsame Konvention:
- `rootEl`: DOM-Element (Content-Container).
- `routeParams`: z. B. `{ id: 1 }`.
- `queryParams`: z. B. `{ year: 2025 }`.

**Views für Verwalter (`manager`):**

- `ImmoApp.Views.Dashboard`
  - Formularleiste: Jahr (Dropdown + aktuelles Jahr), optional Immobilie-Dropdown.
  - Zeigt Kennzahlen-Kacheln, einfache Einnahmen/Ausgaben-Übersicht und offene Punkte.
  - Nutzt `GET /dashboard/stats`.

- `ImmoApp.Views.Properties`
  - Submodes:
    - `list`: Tabelle aller Properties, Button „Neu“.
    - `detail`: Kopf mit Stammdaten, Tabs: „Mietobjekte“, „Kennzahlen“, „Abrechnungen“, „Dokumente“.
    - `form`: Neu/Bearbeiten Property.
  - API:
    - Liste & Detail: `GET /properties`, `GET /properties/{id}`.
    - CRUD: `POST`, `PUT`, `DELETE`.

- `ImmoApp.Views.Units`
  - Liste aller Units (optional Filter `propertyId`).
  - Optional eingebettet in Property-Detail: `GET /units?propertyId=x`.
  - Form: Neu/Bearbeiten.

- `ImmoApp.Views.Tenants`
  - Liste mit Suche.
  - Detail mit Stammdaten, Mietverhältnissen (via `GET /tenancies?tenantId=...`), Dokumenten.
  - Form: Neu/Bearbeiten.

- `ImmoApp.Views.Tenancies`
  - Liste mit Filtern (Jahr, Status, Immobilie).
  - Detail mit Stammdaten, verknüpftem Mieter/Unit, Dokumenten.
  - Form: Neues/ Bearbeiten Mietverhältnis.
  - API: `GET /tenancies`, `POST /tenancies`.

- `ImmoApp.Views.Transactions`
  - Liste Einnahmen/Ausgaben mit Filters:
    - Jahr (erforderlich), Immobilie, Typ, Kategorie.
  - Buttons: „Neue Einnahme“, „Neue Ausgabe“.
  - Form: Transaktion anlegen.
  - API: `GET /transactions`, `POST /transactions`.

- `ImmoApp.Views.Accounting`
  - Oben: Filter Immobilie + Jahr.
  - Abschnitt: Liste vorhandener Reports (via `GET /reports`).
  - Button „Abrechnung erstellen“ → POST `/reports` → aktualisiert Liste.
  - Download-Links basierend auf `path` (Nextcloud-URL).

- `ImmoApp.Views.DocLinks` (meist eingebettet)
  - Wird in anderen View-Details genutzt, bekommt `entityType` & `entityId`.
  - Listet Dokumente via `GET /doc-links/{type}/{id}`.
  - Button „Dokument verknüpfen“ → einfacher Dialog mit Eingabe von File-Pfad oder File-ID (für V1).
  - Aufruf `POST /doc-links`.

**Views für Mieter (`tenant`):**

- `ImmoApp.Views.MyTenancies`
  - Liste eigener Mietverhältnisse: `GET /tenancies?tenantId=...` (Backend interpretiert).  
  - Klick → Detail-Bereich ähnlich Tenancy-Detail, aber read-only.

- `ImmoApp.Views.MyReports`
  - Liste eigener Abrechnungen: `GET /reports?tenantId=...` oder implizit.
  - Download-Link.

- Optional: `ImmoApp.Views.MyDashboard`
  - Einfache Anzeige: „Aktuelles Mietverhältnis“, letzte Abrechnung, Dokumente.

---

## States

### 2.1 Globaler State (`ImmoApp.State`)

```js
ImmoApp.State = {
  currentUser: {
    userId: null,
    role: 'none', // 'manager' | 'tenant' | 'none'
    config: {
      defaultYear: (new Date()).getFullYear()
    }
  },
  ui: {
    currentRoute: '',
    currentView: null,   // Referenz auf aktives View-Modul
    filters: {
      year: null,
      propertyId: null
    },
    loading: false,
    lastError: null
  },
  cache: {
    properties: new Map(), // id -> object
    units: new Map(),      // id -> object
    tenants: new Map()     // id -> object
  }
};
```

State ist rein im JS gehalten und wird bei Reload verloren; das ist für V1 ok.

---

## Views & Navigation (Routing)

### 3.1 Routen-Definition (Beispiele)

```js
// Logische Routen (Hash-Basis)
const routes = {
  '#/dashboard': { view: ImmoApp.Views.Dashboard },
  '#/properties': { view: ImmoApp.Views.Properties, mode: 'list' },
  '#/properties/new': { view: ImmoApp.Views.Properties, mode: 'new' },
  '#/properties/:id': { view: ImmoApp.Views.Properties, mode: 'detail' },
  '#/properties/:id/edit': { view: ImmoApp.Views.Properties, mode: 'edit' },

  '#/units': { view: ImmoApp.Views.Units, mode: 'list' },
  '#/tenants': { view: ImmoApp.Views.Tenants, mode: 'list' },
  '#/tenancies': { view: ImmoApp.Views.Tenancies, mode: 'list' },
  '#/transactions': { view: ImmoApp.Views.Transactions, mode: 'list' },
  '#/accounting': { view: ImmoApp.Views.Accounting, mode: 'list' },

  // Mieter-spezifisch
  '#/my-tenancies': { view: ImmoApp.Views.MyTenancies, mode: 'list' },
  '#/my-reports': { view: ImmoApp.Views.MyReports, mode: 'list' }
};
```

Router generiert aus `location.hash` → Route-Key + Params.

### 3.2 Navigation links (linke Spalte)

- Für `manager`:

  - Dashboard → `#/dashboard`
  - Immobilien → `#/properties`
  - Mietobjekte → `#/units`
  - Mieter → `#/tenants`
  - Mietverhältnisse → `#/tenancies`
  - Einnahmen/Ausgaben → `#/transactions`
  - Abrechnungen → `#/accounting`

- Für `tenant`:

  - Meine Mietverhältnisse → `#/my-tenancies`
  - Meine Abrechnungen → `#/my-reports`

Navigation-Klicks:
- `a`-Elemente mit `href="#/…"`
- JS verhindert Fullpage-Reload nicht aktiv (Hash-Navigation), sondern reagiert auf `hashchange`.

---

## API-Layer (ImmoApp.Api)

### 4.1 Basismethoden

```js
ImmoApp.Api = (function() {
  const BASE = OC.linkTo('immoapp', 'api'); // oder einfach '/apps/immoapp/api'

  function buildUrl(path, params) {
    const url = new URL(BASE + path, window.location.origin);
    if (params) {
      Object.keys(params).forEach(key => {
        if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
          url.searchParams.append(key, params[key]);
        }
      });
    }
    return url.toString();
  }

  async function request(method, path, { params, body } = {}) {
    const url = buildUrl(path, params);
    const options = {
      method,
      headers: {
        'OCS-APIREQUEST': 'true'
      },
      credentials: 'same-origin'
    };
    if (body) {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);

    if (response.status === 204) {
      return null;
    }

    const contentType = response.headers.get('Content-Type') || '';
    const isJson = contentType.indexOf('application/json') !== -1;
    const data = isJson ? await response.json() : await response.text();

    if (!response.ok) {
      const error = new Error((data && data.message) || 'Request failed');
      error.status = response.status;
      error.payload = data;
      throw error;
    }

    return data;
  }

  // öffentliche Methoden
  async function me() {
    return request('GET', '/me');
  }

  async function getDashboardStats(params) {
    return request('GET', '/dashboard/stats', { params });
  }

  // Properties
  async function getProperties() {
    return request('GET', '/properties');
  }
  async function getProperty(id) {
    return request('GET', `/properties/${encodeURIComponent(id)}`);
  }
  async function createProperty(data) {
    return request('POST', '/properties', { body: data });
  }
  async function updateProperty(id, data) {
    return request('PUT', `/properties/${encodeURIComponent(id)}`, { body: data });
  }
  async function deleteProperty(id) {
    return request('DELETE', `/properties/${encodeURIComponent(id)}`);
  }

  // Units
  async function getUnits(params) {
    return request('GET', '/units', { params });
  }
  async function createUnit(data) {
    return request('POST', '/units', { body: data });
  }

  // Tenants
  async function getTenants(params) {
    return request('GET', '/tenants', { params });
  }
  async function createTenant(data) {
    return request('POST', '/tenants', { body: data });
  }

  // Tenancies
  async function getTenancies(params) {
    return request('GET', '/tenancies', { params });
  }
  async function createTenancy(data) {
    return request('POST', '/tenancies', { body: data });
  }

  // Transactions
  async function getTransactions(params) {
    return request('GET', '/transactions', { params });
  }
  async function createTransaction(data) {
    return request('POST', '/transactions', { body: data });
  }

  // Doc links
  async function getDocLinks(entityType, entityId) {
    return request('GET', `/doc-links/${encodeURIComponent(entityType)}/${encodeURIComponent(entityId)}`);
  }
  async function createDocLink(data) {
    return request('POST', '/doc-links', { body: data });
  }

  // Reports
  async function getReports(params) {
    return request('GET', '/reports', { params });
  }
  async function createReport(data) {
    return request('POST', '/reports', { body: data });
  }

  return {
    me,
    getDashboardStats,
    getProperties,
    getProperty,
    createProperty,
    updateProperty,
    deleteProperty,
    getUnits,
    createUnit,
    getTenants,
    createTenant,
    getTenancies,
    createTenancy,
    getTransactions,
    createTransaction,
    getDocLinks,
    createDocLink,
    getReports,
    createReport
  };
})();
```

---

## Beispielcode (JS – Vanilla ES6, Modul-Pattern)

### 5.1 Util-Modul

```js
ImmoApp.Util = (function() {
  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    if (Number.isNaN(date.getTime())) return dateStr;
    return date.toLocaleDateString();
  }

  function formatMoney(amount) {
    if (amount === null || amount === undefined) return '';
    return amount.toLocaleString(undefined, {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 2
    });
  }

  function html(strings, ...values) {
    return strings.reduce((acc, s, i) => {
      let v = '';
      if (i < values.length) {
        const value = values[i];
        v = (value && value.__raw === true) ? String(value.value) : escapeHtml(value);
      }
      return acc + s + v;
    }, '');
  }

  function raw(value) {
    return { __raw: true, value };
  }

  return {
    escapeHtml,
    formatDate,
    formatMoney,
    html,
    raw
  };
})();
```

### 5.2 Router-Modul

```js
ImmoApp.Router = (function() {
  const routes = [];

  function addRoute(pattern, config) {
    const tokens = pattern.split('/').filter(Boolean);
    const paramNames = [];

    const regexParts = tokens.map(tok => {
      if (tok.startsWith(':')) {
        paramNames.push(tok.substring(1));
        return '([^/]+)';
      }
      return tok.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    });

    const regex = new RegExp('^' + regexParts.join('/') + '$');
    routes.push({ pattern, regex, paramNames, config });
  }

  function match(hash) {
    const path = hash.replace(/^#/, '');
    for (const r of routes) {
      const m = path.match(r.regex);
      if (m) {
        const params = {};
        r.paramNames.forEach((name, idx) => {
          params[name] = decodeURIComponent(m[idx + 1]);
        });
        return { config: r.config, params };
      }
    }
    return null;
  }

  function navigate(hash) {
    if (window.location.hash === hash) {
      handleHashChange();
    } else {
      window.location.hash = hash;
    }
  }

  function handleHashChange() {
    const hash = window.location.hash || '#/dashboard';
    const matchInfo = match(hash);
    const contentEl = document.getElementById('immoapp-content');

    if (!contentEl) {
      console.error('Missing content container #immoapp-content');
      return;
    }

    // Destroy previous view if present
    if (ImmoApp.State.ui.currentView && ImmoApp.State.ui.currentView.destroy) {
      ImmoApp.State.ui.currentView.destroy();
    }

    if (!matchInfo) {
      contentEl.innerHTML = '<div class="section"><h2>' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'Page not found')) +
        '</h2></div>';
      return;
    }

    const { config, params } = matchInfo;
    const view = config.view;
    const mode = config.mode || 'list';

    ImmoApp.State.ui.currentRoute = hash;
    ImmoApp.State.ui.currentView = view;

    // Berechtigungsebene im Frontend grob prüfen
    const role = ImmoApp.State.currentUser.role;
    const allowedForTenant = ['#/my-tenancies', '#/my-reports'];
    if (role === 'tenant' && !allowedForTenant.some(prefix => hash.startsWith(prefix))) {
      contentEl.innerHTML = '<div class="section"><h2>' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'You are not allowed to perform this action.')) +
        '</h2></div>';
      return;
    }

    view.init(contentEl, { mode, params }, {});
  }

  function init() {
    window.addEventListener('hashchange', handleHashChange);
    if (!window.location.hash) {
      if (ImmoApp.State.currentUser.role === 'tenant') {
        navigate('#/my-tenancies');
      } else {
        navigate('#/dashboard');
      }
    } else {
      handleHashChange();
    }
  }

  return {
    init,
    navigate,
    addRoute
  };
})();
```

Initiale Router-Konfiguration (in `ImmoApp.App.init()`):

```js
// Konfiguration
ImmoApp.Router.addRoute('/dashboard', { view: ImmoApp.Views.Dashboard });
ImmoApp.Router.addRoute('/properties', { view: ImmoApp.Views.Properties, mode: 'list' });
ImmoApp.Router.addRoute('/properties/new', { view: ImmoApp.Views.Properties, mode: 'new' });
ImmoApp.Router.addRoute('/properties/:id', { view: ImmoApp.Views.Properties, mode: 'detail' });
ImmoApp.Router.addRoute('/properties/:id/edit', { view: ImmoApp.Views.Properties, mode: 'edit' });

ImmoApp.Router.addRoute('/units', { view: ImmoApp.Views.Units, mode: 'list' });
ImmoApp.Router.addRoute('/tenants', { view: ImmoApp.Views.Tenants, mode: 'list' });
ImmoApp.Router.addRoute('/tenancies', { view: ImmoApp.Views.Tenancies, mode: 'list' });
ImmoApp.Router.addRoute('/transactions', { view: ImmoApp.Views.Transactions, mode: 'list' });
ImmoApp.Router.addRoute('/accounting', { view: ImmoApp.Views.Accounting, mode: 'list' });

ImmoApp.Router.addRoute('/my-tenancies', { view: ImmoApp.Views.MyTenancies, mode: 'list' });
ImmoApp.Router.addRoute('/my-reports', { view: ImmoApp.Views.MyReports, mode: 'list' });
```

### 5.3 App-Init-Modul

```js
ImmoApp.App = (function() {
  async function init() {
    const contentEl = document.getElementById('immoapp-content');
    if (!contentEl) {
      return;
    }

    try {
      const me = await ImmoApp.Api.me();
      ImmoApp.State.currentUser.userId = me.userId;
      ImmoApp.State.currentUser.role = me.role;
      ImmoApp.State.currentUser.config = me.config || ImmoApp.State.currentUser.config;
      ImmoApp.State.ui.filters.year = me.config?.defaultYear || (new Date()).getFullYear();
    } catch (e) {
      contentEl.innerHTML = '<div class="section"><h2>' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'Error loading user information')) +
        '</h2></div>';
      return;
    }

    // Navigation in der Sidebar für Rolle anpassen
    setupSidebarForRole(ImmoApp.State.currentUser.role);

    // Router konfigurieren (siehe oben)
    // ... Router.addRoute(...)
    ImmoApp.Router.init();
  }

  function setupSidebarForRole(role) {
    const navManager = document.querySelector('[data-immoapp-nav="manager"]');
    const navTenant = document.querySelector('[data-immoapp-nav="tenant"]');
    if (role === 'tenant') {
      if (navManager) navManager.style.display = 'none';
      if (navTenant) navTenant.style.display = '';
    } else {
      if (navManager) navManager.style.display = '';
      if (navTenant) navTenant.style.display = 'none';
    }
  }

  return {
    init
  };
})();

document.addEventListener('DOMContentLoaded', ImmoApp.App.init);
```

### 5.4 Beispiel: Dashboard-View

```js
ImmoApp.Views = ImmoApp.Views || {};

ImmoApp.Views.Dashboard = (function() {
  let root;

  async function init(rootEl, route, query) {
    root = rootEl;
    renderSkeleton();
    await loadData();
  }

  function destroy() {
    if (root) {
      root.innerHTML = '';
    }
  }

  function renderSkeleton() {
    const year = ImmoApp.State.ui.filters.year || (new Date()).getFullYear();
    root.innerHTML = ImmoApp.Util.html`
      <div class="section">
        <h2>${t('immoapp', 'Dashboard')}</h2>
        <div class="filters">
          <label>
            ${t('immoapp', 'Year')}
            <select id="immoapp-dashboard-year">
              ${ImmoApp.Util.raw(renderYearOptions(year))}
            </select>
          </label>
        </div>
        <div id="immoapp-dashboard-content">
          <div class="icon-loading"></div>
        </div>
      </div>
    `;

    const yearSelect = document.getElementById('immoapp-dashboard-year');
    yearSelect.addEventListener('change', onYearChange);
  }

  function renderYearOptions(selectedYear) {
    const currentYear = (new Date()).getFullYear();
    const years = [];
    for (let y = currentYear + 1; y >= currentYear - 5; y--) {
      const selected = y === Number(selectedYear) ? ' selected' : '';
      years.push(`<option value="${y}"${selected}>${y}</option>`);
    }
    return years.join('');
  }

  async function loadData() {
    const year = document.getElementById('immoapp-dashboard-year').value;
    ImmoApp.State.ui.filters.year = Number(year);

    const container = document.getElementById('immoapp-dashboard-content');
    container.innerHTML = '<div class="icon-loading"></div>';

    try {
      const stats = await ImmoApp.Api.getDashboardStats({ year });
      container.innerHTML = ImmoApp.Util.html`
        <div class="dashboard-grid">
          <div class="dashboard-card">
            <h3>${t('immoapp', 'Properties')}</h3>
            <div class="dashboard-value">${stats.counts.properties}</div>
          </div>
          <div class="dashboard-card">
            <h3>${t('immoapp', 'Units')}</h3>
            <div class="dashboard-value">${stats.counts.units}</div>
          </div>
          <div class="dashboard-card">
            <h3>${t('immoapp', 'Active tenancies')}</h3>
            <div class="dashboard-value">${stats.counts.activeTenancies}</div>
          </div>
          <div class="dashboard-card">
            <h3>${t('immoapp', 'Annual cold rent')}</h3>
            <div class="dashboard-value">
              ${ImmoApp.Util.formatMoney(stats.rent.annualColdRent)}
            </div>
          </div>
        </div>
        <div class="section">
          <h3>${t('immoapp', 'Cashflow')}</h3>
          <p>${t('immoapp', 'Income')}: ${ImmoApp.Util.formatMoney(stats.cashflow.income)}</p>
          <p>${t('immoapp', 'Expenses')}: ${ImmoApp.Util.formatMoney(stats.cashflow.expense)}</p>
          <p>${t('immoapp', 'Net')}: ${ImmoApp.Util.formatMoney(stats.cashflow.net)}</p>
        </div>
        <div class="section">
          <h3>${t('immoapp', 'Open items')}</h3>
          ${ImmoApp.Util.raw(renderOpenItems(stats.openItems))}
        </div>
      `;
    } catch (e) {
      container.innerHTML = '<div class="error">' +
        ImmoApp.Util.escapeHtml(
          t('immoapp', 'Failed to load dashboard data.')
        ) +
        '</div>';
    }
  }

  function renderOpenItems(openItems) {
    const hasItems =
      (openItems.tenanciesStartingSoon && openItems.tenanciesStartingSoon.length > 0) ||
      (openItems.tenanciesEndingSoon && openItems.tenanciesEndingSoon.length > 0) ||
      (openItems.transactionsWithoutCategory && openItems.transactionsWithoutCategory.length > 0) ||
      (openItems.transactionsWithoutTenancy && openItems.transactionsWithoutTenancy.length > 0);

    if (!hasItems) {
      return `<p>${ImmoApp.Util.escapeHtml(t('immoapp', 'No open items'))}</p>`;
    }

    return `
      <ul class="open-items-list">
        ${openItems.tenanciesStartingSoon.map(tn =>
          `<li>${ImmoApp.Util.escapeHtml(t('immoapp', 'Tenancy starts soon'))}: ${ImmoApp.Util.escapeHtml(tn.label || '')}</li>`
        ).join('')}
        ${openItems.tenanciesEndingSoon.map(tn =>
          `<li>${ImmoApp.Util.escapeHtml(t('immoapp', 'Tenancy ends soon'))}: ${ImmoApp.Util.escapeHtml(tn.label || '')}</li>`
        ).join('')}
        ${openItems.transactionsWithoutCategory.map(tr =>
          `<li>${ImmoApp.Util.escapeHtml(t('immoapp', 'Transaction without category'))}: ${ImmoApp.Util.escapeHtml(tr.description || '')}</li>`
        ).join('')}
        ${openItems.transactionsWithoutTenancy.map(tr =>
          `<li>${ImmoApp.Util.escapeHtml(t('immoapp', 'Transaction not assigned to tenancy'))}: ${ImmoApp.Util.escapeHtml(tr.description || '')}</li>`
        ).join('')}
      </ul>
    `;
  }

  function onYearChange() {
    loadData();
  }

  return {
    init,
    destroy
  };
})();
```

### 5.5 Beispiel: Properties-View (Liste + Form grob)

```js
ImmoApp.Views.Properties = (function() {
  let root;
  let mode;
  let currentId;

  async function init(rootEl, route) {
    root = rootEl;
    mode = route.mode || 'list';
    currentId = route.params.id ? Number(route.params.id) : null;

    if (mode === 'list') {
      await renderList();
    } else if (mode === 'new') {
      renderForm();
    } else if (mode === 'detail') {
      await renderDetail();
    } else if (mode === 'edit') {
      await renderEditForm();
    }
  }

  function destroy() {
    if (root) {
      root.innerHTML = '';
    }
  }

  async function renderList() {
    root.innerHTML = '<div class="section"><h2>' +
      ImmoApp.Util.escapeHtml(t('immoapp', 'Properties')) +
      '</h2><div class="icon-loading"></div></div>';

    try {
      const properties = await ImmoApp.Api.getProperties();
      const rows = properties.map(p => ImmoApp.Util.html`
        <tr data-id="${p.id}">
          <td><a href="#/properties/${p.id}">${p.name}</a></td>
          <td>${p.city}</td>
          <td>${p.stats ? p.stats.unitCount : ''}</td>
          <td>${p.stats ? ImmoApp.Util.formatMoney(p.stats.annualIncome) : ''}</td>
        </tr>
      `).join('');

      root.innerHTML = ImmoApp.Util.html`
        <div class="section">
          <div class="header">
            <h2>${t('immoapp', 'Properties')}</h2>
            <button id="immoapp-property-new" class="primary">
              ${t('immoapp', 'New property')}
            </button>
          </div>
          ${
            properties.length === 0
              ? ImmoApp.Util.html`
                <p>${t('immoapp', 'No properties yet.')}</p>
                <button id="immoapp-property-new-empty" class="primary">
                  ${t('immoapp', 'Create first property')}
                </button>
              `
              : ImmoApp.Util.html`
                <table class="grid">
                  <thead>
                    <tr>
                      <th>${t('immoapp', 'Name')}</th>
                      <th>${t('immoapp', 'City')}</th>
                      <th>${t('immoapp', 'Units')}</th>
                      <th>${t('immoapp', 'Annual income')}</th>
                    </tr>
                  </thead>
                  <tbody>${ImmoApp.Util.raw(rows)}</tbody>
                </table>
              `
          }
        </div>
      `;

      const newBtn = document.getElementById('immoapp-property-new') ||
        document.getElementById('immoapp-property-new-empty');

      if (newBtn) {
        newBtn.addEventListener('click', () => {
          ImmoApp.Router.navigate('#/properties/new');
        });
      }
    } catch (e) {
      root.innerHTML = '<div class="section"><h2>' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'Properties')) +
        '</h2><div class="error">' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'Failed to load properties')) +
        '</div></div>';
    }
  }

  function renderForm(property) {
    const isEdit = !!property;
    const title = isEdit
      ? t('immoapp', 'Edit property')
      : t('immoapp', 'New property');

    root.innerHTML = ImmoApp.Util.html`
      <div class="section">
        <h2>${title}</h2>
        <form id="immoapp-property-form">
          <label>
            ${t('immoapp', 'Name')} *
            <input type="text" name="name" value="${property ? property.name : ''}" required />
          </label>
          <label>
            ${t('immoapp', 'Street')}
            <input type="text" name="street" value="${property ? property.street : ''}" />
          </label>
          <label>
            ${t('immoapp', 'ZIP')}
            <input type="text" name="zip" value="${property ? property.zip : ''}" />
          </label>
          <label>
            ${t('immoapp', 'City')}
            <input type="text" name="city" value="${property ? property.city : ''}" />
          </label>
          <label>
            ${t('immoapp', 'Country')}
            <input type="text" name="country" value="${property ? property.country : ''}" />
          </label>
          <label>
            ${t('immoapp', 'Type')}
            <input type="text" name="type" value="${property ? property.type : ''}" />
          </label>
          <label>
            ${t('immoapp', 'Notes')}
            <textarea name="notes">${property ? (property.notes || '') : ''}</textarea>
          </label>
          <div class="form-actions">
            <button type="submit" class="primary">
              ${t('immoapp', 'Save')}
            </button>
            <button type="button" id="immoapp-property-cancel">
              ${t('immoapp', 'Cancel')}
            </button>
          </div>
        </form>
      </div>
    `;

    const form = document.getElementById('immoapp-property-form');
    const cancelBtn = document.getElementById('immoapp-property-cancel');

    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(form);
      const data = {
        name: formData.get('name'),
        street: formData.get('street'),
        zip: formData.get('zip'),
        city: formData.get('city'),
        country: formData.get('country'),
        type: formData.get('type'),
        notes: formData.get('notes')
      };

      try {
        if (isEdit) {
          await ImmoApp.Api.updateProperty(property.id, data);
        } else {
          await ImmoApp.Api.createProperty(data);
        }
        ImmoApp.Router.navigate('#/properties');
      } catch (err) {
        alert(t('immoapp', 'Failed to save property.'));
      }
    });

    cancelBtn.addEventListener('click', function() {
      if (isEdit && property && property.id) {
        ImmoApp.Router.navigate(`#/properties/${property.id}`);
      } else {
        ImmoApp.Router.navigate('#/properties');
      }
    });
  }

  async function renderDetail() {
    if (!currentId) {
      ImmoApp.Router.navigate('#/properties');
      return;
    }

    root.innerHTML = '<div class="section"><h2>' +
      ImmoApp.Util.escapeHtml(t('immoapp', 'Property')) +
      '</h2><div class="icon-loading"></div></div>';

    try {
      const property = await ImmoApp.Api.getProperty(currentId);

      root.innerHTML = ImmoApp.Util.html`
        <div class="section">
          <div class="header">
            <h2>${property.name}</h2>
            <div>
              <button id="immoapp-property-edit">
                ${t('immoapp', 'Edit')}
              </button>
              <button id="immoapp-property-delete">
                ${t('immoapp', 'Delete')}
              </button>
            </div>
          </div>
          <p>${property.street}, ${property.zip} ${property.city}, ${property.country}</p>
          <p>${ImmoApp.Util.escapeHtml(property.type || '')}</p>
          <p>${ImmoApp.Util.escapeHtml(property.notes || '')}</p>

          <h3>${t('immoapp', 'Units')}</h3>
          <div id="immoapp-property-units">
            <div class="icon-loading"></div>
          </div>

          <h3>${t('immoapp', 'Reports')}</h3>
          <div id="immoapp-property-reports">
            <div class="icon-loading"></div>
          </div>

          <h3>${t('immoapp', 'Documents')}</h3>
          <div id="immoapp-property-docs">
            <div class="icon-loading"></div>
          </div>
        </div>
      `;

      document.getElementById('immoapp-property-edit')
        .addEventListener('click', () => ImmoApp.Router.navigate(`#/properties/${property.id}/edit`));
      document.getElementById('immoapp-property-delete')
        .addEventListener('click', async () => {
          if (!confirm(t('immoapp', 'Do you really want to delete this property?'))) {
            return;
          }
          try {
            await ImmoApp.Api.deleteProperty(property.id);
            ImmoApp.Router.navigate('#/properties');
          } catch (e) {
            alert(t('immoapp', 'Cannot delete property.'));
          }
        });

      // Unterbereiche laden
      loadUnitsForProperty(property.id);
      loadReportsForProperty(property.id);
      loadDocsForProperty(property.id);

    } catch (e) {
      root.innerHTML = '<div class="section"><h2>' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'Property')) +
        '</h2><div class="error">' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'Property not found or access denied.')) +
        '</div></div>';
    }
  }

  async function renderEditForm() {
    if (!currentId) {
      ImmoApp.Router.navigate('#/properties');
      return;
    }
    try {
      const property = await ImmoApp.Api.getProperty(currentId);
      renderForm(property);
    } catch (e) {
      ImmoApp.Router.navigate('#/properties');
    }
  }

  async function loadUnitsForProperty(propertyId) {
    const container = document.getElementById('immoapp-property-units');
    try {
      const units = await ImmoApp.Api.getUnits({ propertyId });
      if (units.length === 0) {
        container.innerHTML = ImmoApp.Util.html`
          <p>${t('immoapp', 'No units for this property yet.')}</p>
        `;
        return;
      }
      const rows = units.map(u => ImmoApp.Util.html`
        <tr>
          <td>${u.label}</td>
          <td>${u.unitNumber || ''}</td>
          <td>${u.livingArea || ''}</td>
        </tr>
      `).join('');
      container.innerHTML = ImmoApp.Util.html`
        <table class="grid">
          <thead>
            <tr>
              <th>${t('immoapp', 'Label')}</th>
              <th>${t('immoapp', 'Unit number')}</th>
              <th>${t('immoapp', 'Living area')}</th>
            </tr>
          </thead>
          <tbody>${ImmoApp.Util.raw(rows)}</tbody>
        </table>
      `;
    } catch (e) {
      container.innerHTML = '<div class="error">' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'Failed to load units')) +
        '</div>';
    }
  }

  async function loadReportsForProperty(propertyId) {
    const container = document.getElementById('immoapp-property-reports');
    try {
      const reports = await ImmoApp.Api.getReports({ propertyId });
      if (reports.length === 0) {
        container.innerHTML = ImmoApp.Util.html`
          <p>${t('immoapp', 'No reports yet.')}</p>
        `;
        return;
      }
      const rows = reports.map(r => ImmoApp.Util.html`
        <tr>
          <td>${r.year}</td>
          <td>${ImmoApp.Util.formatDate(new Date(r.createdAt * 1000).toISOString())}</td>
          <td><a href="${ImmoApp.Util.escapeHtml(r.path)}" target="_blank">
            ${t('immoapp', 'Download')}
          </a></td>
        </tr>
      `).join('');
      container.innerHTML = ImmoApp.Util.html`
        <table class="grid">
          <thead>
            <tr>
              <th>${t('immoapp', 'Year')}</th>
              <th>${t('immoapp', 'Created')}</th>
              <th>${t('immoapp', 'File')}</th>
            </tr>
          </thead>
          <tbody>${ImmoApp.Util.raw(rows)}</tbody>
        </table>
      `;
    } catch (e) {
      container.innerHTML = '<div class="error">' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'Failed to load reports')) +
        '</div>';
    }
  }

  async function loadDocsForProperty(propertyId) {
    const container = document.getElementById('immoapp-property-docs');
    try {
      const docs = await ImmoApp.Api.getDocLinks('property', propertyId);
      if (docs.length === 0) {
        container.innerHTML = ImmoApp.Util.html`
          <p>${t('immoapp', 'No documents linked yet.')}</p>
        `;
        return;
      }
      const items = docs.map(d => ImmoApp.Util.html`
        <li>
          <a href="${ImmoApp.Util.escapeHtml(d.path)}" target="_blank">
            ${d.name || d.path}
          </a>
        </li>
      `).join('');
      container.innerHTML = ImmoApp.Util.html`
        <ul>${ImmoApp.Util.raw(items)}</ul>
      `;
    } catch (e) {
      container.innerHTML = '<div class="error">' +
        ImmoApp.Util.escapeHtml(t('immoapp', 'Failed to load documents')) +
        '</div>';
    }
  }

  return {
    init,
    destroy
  };
})();
```

---

Damit sind UI-Komponenten, Zustände, Views, Navigation und der API-Layer ausschließlich im Frontend (Vanilla JS, Nextcloud-Kontext) definiert – ohne PHP, Entities oder Datenbankdetails.