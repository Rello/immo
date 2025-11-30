**Komponenten**

1. **Domus.App**
   - Bootstrapping, lädt Rollen, initialisiert Navigation & Default-View.
2. **Domus.Navigation**
   - Rendert `<ul>` in `#app-navigation`, steuert View-Wechsel, setzt aktive Klasse.
3. **Domus.State**
   - Hält globale Zustände: `currentView`, `roles`, `filters` (year, propertyId), `selectedEntity`.
4. **Domus.Api**
   - Wrapper um `fetch` mit `OC.generateUrl`, Header `OCS-APIREQUEST: 'true'`, JSON-Handling.
5. **Domus.Views.Dashboard**
   - Lädt Kennzahlen, Alerts, Jahr/Immobilie-Filter.
6. **Domus.Views.Properties**
   - Liste mit Paging, Buttons (Neu/Bearbeiten/Löschen), Detail-Modal mit Tabs (nur clientseitig).
7. **Domus.Views.Units**
   - Filter (property, status), Liste + KPI Badge, Detailanzeige.
8. **Domus.Views.Partners**
   - Suchfeld, Typ-Filter, Tabelle, Detailanzeige.
9. **Domus.Views.Tenancies**
   - Filter (unit, partner, status), Status-Badges, Enden-Action.
10. **Domus.Views.Bookings**
    - Filterleiste (year, propertyId, etc.), Summenzeile, Formular-Modal.
11. **Domus.Views.Reports**
    - Filter (year, propertyId), Tabelle, Generation-Formular.
12. **Domus.Modals**
    - Generischer Modal-Renderer für Formulare (CRUD), Dokument-Verknüpfungen.
13. **Domus.Sidebar**
    - Optionaler Detail-/Filter-Content für aktuelle View.
14. **Domus.Util**
    - Formatierer (Datum, Betrag), Error-/Empty-State Renderer, Form Helpers.

---

**States**

```js
Domus.State = {
  currentView: 'dashboard',
  roles: {
    isManager: false,
    isLandlord: false,
    isTenant: false,
    isOwner: false,
    partnerId: null,
    restrictedTenancyIds: [],
  },
  filters: {
    year: new Date().getFullYear(),
    propertyId: 'all',
  },
  selectedEntity: null,
  lists: {
    properties: [],
    units: [],
    partners: [],
    tenancies: [],
    bookings: [],
    reports: [],
  },
  ui: {
    loading: false,
    error: null,
  },
}
```

---

**Views**

1. **Dashboard**
   - Header mit Filter (Jahr-Dropdown, Immobilie-Dropdown im Sidebar).
   - KPI-Kacheln (properties, units, tenancies, annualBaseRent, avgRentPerSqm).
   - Alerts-Liste unterhalb.
   - Tenant-Sicht: Liste „Meine Mietverhältnisse“ statt globaler KPIs.

2. **Immobilien**
   - Toolbar: Button „Neu“, Dropdowns (Sortierung, Paging).
   - Tabelle (Name, Adresse, roleType, unitCount, actions).
   - Click → Detail in `#app-content` mit Tabs (Mietobjekte, Kennzahlen, Dokumente, Abrechnungen) – Tab-Inhalte per zusätzliche API-Calls.
   - Sidebar: Filter, Quick-Stats.

3. **Mietobjekte**
   - Filter im Sidebar: Immobilie, Status.
   - Liste mit Card-Layout oder Tabelle (Name, Property, Fläche, statusBadge, actions).
   - Detail: Stammdaten + Tabs (Tenancies, Bookings, Documents).

4. **Geschäftspartner**
   - Suchfeld + Typ-Filter in Sidebar.
   - Tabelle (Name, Typ, Ort, Kundennummer, actions).
   - Detail: Kontaktinfos, Tabs (Tenancies/Objects, Documents).

5. **Mietverhältnisse**
   - Filter (unit, partner, status, year) im Sidebar.
   - Tabelle (Unit, Partner, Zeitraum, Miete, Status-Badge, actions).
   - Detail: Stammdaten, Tabs (Bookings, Documents, KPIs).
   - Action „Beenden“ → POST `/tenancies/{id}/end`.

6. **Einnahmen & Ausgaben**
   - Toolbar: Filter (year obligatorisch, property, unit, tenancy, type, category), Button „Neu“.
   - Tabelle mit Summenzeile.
   - Detail Modal mit Document Links.

7. **Abrechnungen**
   - Filter (year, propertyId) im Sidebar.
   - Tabelle (Jahr, Property, Typ, createdAt, Download-Link).
   - Formular „Abrechnung erstellen“ (Property + Year) → POST `/reports/propertyYear`.

---

**API-Layer**

```js
Domus.Api = (() => {
  const request = async (path, options = {}) => {
    const base = OC.generateUrl(`/apps/domus${path}`)
    const headers = Object.assign({
      'Content-Type': 'application/json',
      'OCS-APIREQUEST': 'true',
      'requesttoken': OC.requestToken,
    }, options.headers || {})
    const response = await fetch(base, { ...options, headers })
    if (!response.ok) {
      const error = await response.json().catch(() => ({}))
      throw new Error(error.message || response.statusText)
    }
    return response.status === 204 ? null : response.json()
  }

  return {
    getRoles: () => request('/roles/me'),
    getDashboard: (params = {}) => request(`/dashboard?${new URLSearchParams(params)}`),
    listProperties: (params = {}) => request(`/properties?${new URLSearchParams(params)}`),
    createProperty: (payload) => request('/properties', { method: 'POST', body: JSON.stringify(payload) }),
    updateProperty: (id, payload) => request(`/properties/${id}`, { method: 'PUT', body: JSON.stringify(payload) }),
    deleteProperty: (id) => request(`/properties/${id}`, { method: 'DELETE' }),
    listUnits: (params = {}) => request(`/units?${new URLSearchParams(params)}`),
    listPartners: (params = {}) => request(`/partners?${new URLSearchParams(params)}`),
    listTenancies: (params = {}) => request(`/tenancies?${new URLSearchParams(params)}`),
    endTenancy: (id, payload) => request(`/tenancies/${id}/end`, { method: 'POST', body: JSON.stringify(payload) }),
    listBookings: (params = {}) => request(`/bookings?${new URLSearchParams(params)}`),
    createBooking: (payload) => request('/bookings', { method: 'POST', body: JSON.stringify(payload) }),
    listReports: (params = {}) => request(`/reports?${new URLSearchParams(params)}`),
    createReport: (payload) => request('/reports/propertyYear', { method: 'POST', body: JSON.stringify(payload) }),
    listFileLinks: (entityType, entityId) => request(`/files/${entityType}/${entityId}`),
    createFileLink: (payload) => request('/files', { method: 'POST', body: JSON.stringify(payload) }),
    deleteFileLink: (id) => request(`/files/${id}`, { method: 'DELETE' }),
  }
})()
```

---

**Beispielcode (domus-main.js)**

```js
window.Domus = window.Domus || {}

Domus.App = (() => {
  const init = async () => {
    try {
      Domus.State.ui.loading = true
      Domus.UI.showLoading()
      const roles = await Domus.Api.getRoles()
      Domus.State.roles = roles
      Domus.Navigation.init(roles)
      const defaultView = roles.isTenant && !roles.isManager ? 'tenancies' : 'dashboard'
      Domus.Navigation.navigate(defaultView)
    } catch (error) {
      Domus.UI.showError(error.message)
    } finally {
      Domus.State.ui.loading = false
      Domus.UI.hideLoading()
    }
  }

  return { init }
})()

Domus.Navigation = (() => {
  const navItemsManager = [
    { id: 'dashboard', label: t('domus', 'Dashboard') },
    { id: 'properties', label: t('domus', 'Properties') },
    { id: 'units', label: t('domus', 'Units') },
    { id: 'partners', label: t('domus', 'Partners') },
    { id: 'tenancies', label: t('domus', 'Tenancies') },
    { id: 'bookings', label: t('domus', 'Bookings') },
    { id: 'reports', label: t('domus', 'Reports') },
  ]
  const navItemsTenant = [
    { id: 'tenancies', label: t('domus', 'My tenancies') },
    { id: 'reports', label: t('domus', 'My reports') },
  ]
  let current = null

  const init = (roles) => {
    const container = document.getElementById('app-navigation')
    container.innerHTML = ''
    const list = document.createElement('ul')
    const items = roles.isManager || roles.isLandlord ? navItemsManager : navItemsTenant
    items.forEach(item => {
      const li = document.createElement('li')
      li.dataset.view = item.id
      li.textContent = item.label
      li.addEventListener('click', () => navigate(item.id))
      list.appendChild(li)
    })
    container.appendChild(list)
  }

  const navigate = async (viewId) => {
    if (current === viewId) {
      return
    }
    current = viewId
    Domus.State.currentView = viewId
    highlight(viewId)
    await Domus.Views.load(viewId)
  }

  const highlight = (viewId) => {
    document.querySelectorAll('#app-navigation li').forEach(li => {
      li.classList.toggle('active', li.dataset.view === viewId)
    })
  }

  return { init, navigate }
})()

Domus.Views = (() => {
  const registry = {
    dashboard: Domus.ViewsDashboard,
    properties: Domus.ViewsProperties,
    units: Domus.ViewsUnits,
    partners: Domus.ViewsPartners,
    tenancies: Domus.ViewsTenancies,
    bookings: Domus.ViewsBookings,
    reports: Domus.ViewsReports,
  }

  const load = async (viewId) => {
    const view = registry[viewId]
    if (!view) {
      Domus.UI.showError(t('domus', 'Unknown view'))
      return
    }
    Domus.UI.showLoading()
    try {
      await view.render()
    } catch (error) {
      Domus.UI.showError(error.message)
    } finally {
      Domus.UI.hideLoading()
    }
  }

  return { load }
})()

Domus.ViewsDashboard = (() => {
  const render = async () => {
    const params = { year: Domus.State.filters.year }
    if (Domus.State.filters.propertyId !== 'all') {
      params.propertyId = Domus.State.filters.propertyId
    }
    const data = await Domus.Api.getDashboard(params)
    const content = document.getElementById('app-content')
    content.innerHTML = `
      <div class="domus-header">
        <h2>${t('domus', 'Dashboard')}</h2>
        ${renderFilters()}
      </div>
      <div class="domus-kpis">
        ${renderKpi(t('domus', 'Properties'), data.stats.propertyCount)}
        ${renderKpi(t('domus', 'Units'), data.stats.unitCount)}
        ${renderKpi(t('domus', 'Active tenancies'), data.stats.activeTenancies)}
        ${renderKpi(t('domus', 'Annual rent'), Domus.Util.formatCurrency(data.stats.annualBaseRent))}
        ${renderKpi(t('domus', 'Avg. rent per sqm'), `${Domus.Util.formatNumber(data.stats.avgRentPerSqm)} €/m²`)}
      </div>
      <div class="domus-alerts">
        ${data.alerts?.length ? data.alerts.map(a => `<div class="alert alert-${a.type}">${a.message}</div>`).join('') : `<div class="empty">${t('domus', 'No alerts')}</div>`}
      </div>
    `
    bindFilters()
  }

  const renderFilters = () => {
    const year = Domus.State.filters.year
    return `
      <label>
        ${t('domus', 'Year')}
        <select id="domus-filter-year">
          ${[0,1,2].map(offset => {
            const value = new Date().getFullYear() - offset
            return `<option value="${value}" ${value === year ? 'selected' : ''}>${value}</option>`
          }).join('')}
        </select>
      </label>
    `
  }

  const bindFilters = () => {
    document.getElementById('domus-filter-year').addEventListener('change', (event) => {
      Domus.State.filters.year = parseInt(event.target.value, 10)
      render()
    })
  }

  const renderKpi = (label, value) => `
    <div class="kpi-card">
      <span class="kpi-label">${label}</span>
      <strong class="kpi-value">${value ?? '–'}</strong>
    </div>
  `

  return { render }
})()

Domus.ViewsProperties = (() => {
  const render = async () => {
    const response = await Domus.Api.listProperties({ page: 1, limit: 50 })
    Domus.State.lists.properties = response.data
    const content = document.getElementById('app-content')
    content.innerHTML = `
      <div class="domus-header">
        <h2>${t('domus', 'Properties')}</h2>
        <button id="domus-property-create" class="primary">${t('domus', 'New property')}</button>
      </div>
      ${response.data.length ? `
        <table class="domus-table">
          <thead>
            <tr>
              <th>${t('domus', 'Name')}</th>
              <th>${t('domus', 'Address')}</th>
              <th>${t('domus', 'Role')}</th>
              <th>${t('domus', 'Actions')}</th>
            </tr>
          </thead>
          <tbody>
            ${response.data.map(renderRow).join('')}
          </tbody>
        </table>
      ` : Domus.UI.renderEmpty(t('domus', 'No properties yet'))}
    `
    bindEvents()
  }

  const renderRow = (property) => `
    <tr data-id="${property.id}">
      <td>${property.name}</td>
      <td>${property.street}, ${property.zipCode} ${property.city}</td>
      <td>${property.roleType}</td>
      <td>
        <button class="link" data-action="show">${t('domus', 'Details')}</button>
        <button class="link" data-action="edit">${t('domus', 'Edit')}</button>
        <button class="link negative" data-action="delete">${t('domus', 'Delete')}</button>
      </td>
    </tr>
  `

  const bindEvents = () => {
    document.getElementById('domus-property-create').addEventListener('click', () => openForm())
    document.querySelectorAll('.domus-table tr button[data-action]').forEach(button => {
      button.addEventListener('click', (event) => {
        const id = event.target.closest('tr').dataset.id
        const action = event.target.dataset.action
        handleAction(action, id)
      })
    })
  }

  const handleAction = async (action, id) => {
    const property = Domus.State.lists.properties.find(p => p.id == id)
    if (!property) {
      return
    }
    if (action === 'show') {
      showDetails(property)
    } else if (action === 'edit') {
      openForm(property)
    } else if (action === 'delete') {
      if (confirm(t('domus', 'Delete property?'))) {
        await Domus.Api.deleteProperty(id)
        render()
      }
    }
  }

  const showDetails = (property) => {
    const sidebar = document.getElementById('app-sidebar')
    sidebar.innerHTML = `
      <h3>${property.name}</h3>
      <p>${property.street}<br>${property.zipCode} ${property.city}</p>
      <p>${t('domus', 'Role')}: ${property.roleType}</p>
      <p>${property.notes || ''}</p>
    `
  }

  const openForm = (property = null) => {
    const isEdit = Boolean(property)
    Domus.Modals.open({
      title: isEdit ? t('domus', 'Edit property') : t('domus', 'New property'),
      content: `
        <form id="domus-property-form">
          <label>${t('domus', 'Name')}<input type="text" name="name" value="${property?.name || ''}" required></label>
          <label>${t('domus', 'Role')}<select name="roleType">
            <option value="manager" ${property?.roleType === 'manager' ? 'selected' : ''}>${t('domus', 'Manager')}</option>
            <option value="landlord" ${property?.roleType === 'landlord' ? 'selected' : ''}>${t('domus', 'Landlord')}</option>
          </select></label>
          <label>${t('domus', 'Street')}<input type="text" name="street" value="${property?.street || ''}" required></label>
          <label>${t('domus', 'ZIP code')}<input type="text" name="zipCode" value="${property?.zipCode || ''}" required></label>
          <label>${t('domus', 'City')}<input type="text" name="city" value="${property?.city || ''}" required></label>
          <label>${t('domus', 'Country')}<input type="text" name="country" value="${property?.country || ''}" required></label>
          <label>${t('domus', 'Notes')}<textarea name="notes">${property?.notes || ''}</textarea></label>
          <div class="modal-actions">
            <button type="button" class="secondary" data-action="cancel">${t('domus', 'Cancel')}</button>
            <button type="submit" class="primary">${t('domus', 'Save')}</button>
          </div>
        </form>
      `,
      onOpen: () => {
        const form = document.getElementById('domus-property-form')
        form.addEventListener('submit', async (event) => {
          event.preventDefault()
          const formData = new FormData(form)
          const payload = Object.fromEntries(formData.entries())
          try {
            if (isEdit) {
              await Domus.Api.updateProperty(property.id, payload)
            } else {
              await Domus.Api.createProperty(payload)
            }
            Domus.Modals.close()
            render()
          } catch (error) {
            Domus.UI.showError(error.message)
          }
        })
        form.querySelector('button[data-action="cancel"]').addEventListener('click', Domus.Modals.close)
      },
    })
  }

  return { render }
})()

/* Weitere View-Module (Units, Partners, Tenancies, Bookings, Reports) analog aufgebaut:
   - render(): lädt Daten via Domus.Api, schreibt in #app-content
   - bindEvents(): Buttons, Filter, CRUD-Aktionen
   - Detailanzeige optional im Sidebar oder via Modal
*/

Domus.Modals = (() => {
  let modal = null

  const open = ({ title, content, onOpen }) => {
    close()
    modal = document.createElement('div')
    modal.className = 'domus-modal-backdrop'
    modal.innerHTML = `
      <div class="domus-modal">
        <div class="domus-modal-header">
          <h3>${title}</h3>
          <button class="close">&times;</button>
        </div>
        <div class="domus-modal-body">${content}</div>
      </div>
    `
    modal.querySelector('.close').addEventListener('click', close)
    document.body.appendChild(modal)
    if (typeof onOpen === 'function') {
      onOpen()
    }
  }

  const close = () => {
    if (modal) {
      modal.remove()
      modal = null
    }
  }

  return { open, close }
})()

Domus.UI = (() => {
  const showLoading = () => {
    document.body.classList.add('domus-loading')
  }
  const hideLoading = () => {
    document.body.classList.remove('domus-loading')
  }
  const showError = (message) => {
    const content = document.getElementById('app-content')
    const banner = document.createElement('div')
    banner.className = 'domus-error'
    banner.textContent = message
    content.prepend(banner)
    setTimeout(() => banner.remove(), 6000)
  }
  const renderEmpty = (message) => `<div class="domus-empty">${message}</div>`
  return { showLoading, hideLoading, showError, renderEmpty }
})()

Domus.Util = {
  formatCurrency: (value) => new Intl.NumberFormat(undefined, { style: 'currency', currency: 'EUR' }).format(value || 0),
  formatNumber: (value) => new Intl.NumberFormat().format(value || 0),
}

/* Entry point */
document.addEventListener('DOMContentLoaded', () => {
  Domus.App.init()
})
```

*Hinweis:*  
- Alle UI-Texte werden über `t('domus', '...')` gesetzt.  
- Formularaktionen laufen ausschließlich über die definierten API-Endpunkte.  
- Jede View nutzt `#app-content` für Hauptinhalte und `#app-sidebar` für Filter/Details.  
- CRUD-Dialoge werden durch `Domus.Modals` als Overlay dargestellt.