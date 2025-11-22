**Komponenten**

1. **LayoutShell**
   - Initialisiert Grund-DOM (`#app-navigation`, `#app-content`, `#app-sidebar`).
   - Rendert linke Navigationsliste (Dashboard, Immobilien, Mietobjekte, Mieter, Mietverhältnisse, Buchungen, Abrechnungen). Filtert Menüpunkte abhängig von `userRole`.
   - Verteilt Klick-Events an `ViewRouter`.

2. **ViewRouter**
   - Hält Mapping `route -> viewModule`.
   - Lädt HTML-Partials per `Immo.Api.getHtml()` und tauscht `#app-content`.
   - Aktualisiert Browser-History (optional) und setzt aktiven Nav-Status.

3. **StateStore**
   - Zentrales JS-Objekt für:
     - `currentRoute`
     - `selectedYear`
     - `selectedPropId`
     - `userRole` (vom Server-Template via `data-*` Attribut).
   - Event-System (`subscribe/notify`) für abhängige Komponenten (z. B. Filterleiste, Dashboard-Kacheln).

4. **FilterBar**
   - UI-Element am Kopf von Listen/Dashboard.
   - Bietet Dropdowns für Jahr und Immobilie.
   - Triggert `StateStore` Updates; interessierte Views reloaden ihre Daten.

5. **View-Module**
   - `Immo.Views.Dashboard`
   - `Immo.Views.Properties`
   - `Immo.Views.Units`
   - `Immo.Views.Tenants`
   - `Immo.Views.Leases`
   - `Immo.Views.Bookings`
   - `Immo.Views.Reports`
   - `Immo.Views.Documents` (Detail-Sidebar für Filelinks)
   - Jedes Modul besitzt: `load(params)`, `bindEvents()`, optionale Helper (`renderList`, `renderForm`).
   - Formularmodule nutzen `FormHandler`-Utility für Serialize, Validation, Submit.

6. **FormHandler**
   - Nimmt `formElement`, `endpoint`, `method`.
   - Fügt Submit-Listener hinzu, zeigt Loading-State, sendet JSON.
   - On success -> Toast + Callback (z. B. `refreshList`).

7. **DocumentsPanel**
   - Lädt/verwalten Filelinks eines Objekts.
   - Nutzt `OC.dialogs.filepicker` für Dateiauswahl, danach POST an API.
   - Rendert Liste mit Download-Links und optional Remove-Buttons.

---

**States**

| State            | Beschreibung | Auslöser / Übergänge |
|------------------|--------------|----------------------|
| `loadingView`    | `#app-content` zeigt Spinner | Start `ViewRouter.load`; Ende bei erfolgreicher HTML-Response |
| `listEmpty`      | Tabellen ohne Daten → Hinweis-Panel | API liefert `[]`; View rendert CTA |
| `formDirty`      | Formular hat unveröffentlichte Änderungen | Input-Events; verlässt View → Warnung |
| `formSubmitting` | Submit läuft, Buttons disabled | FormHandler sendet Request; Ende bei Response/Fehler |
| `modalOpen`      | Filepicker/Bestätigungsdialog aktiv | Button „Datei verknüpfen“ / Delete |
| `filterApplied`  | FilterBar setzt Jahr/Immobilie ≠ default | Dropdown change; Views reloaden |
| `roleTenant`     | UI reduziert (Navigation, Buttons) | Initial `userRole` = `tenant`; LayoutShell blendet CRUD aus |

---

**Views**

1. **Dashboard**
   - HTML-Partial enthält Kachel-Container, FilterBar, zwei Listen (Offene Punkte, Jahresverteilung-Auszug).
   - JS ruft `GET /apps/immo/api/dashboard?year=...`.
   - Kacheln: `properties`, `units`, `activeLeases`, `coldRentYear`, `rentPerSqm`.
   - Links in Listen triggern `ViewRouter.load('leases', { filter: 'endingSoon' })`.

2. **Immobilien Liste**
   - Tabelle + Button „Neue Immobilie“.
   - Zeilenklick → Detail (HTML Partial `view/property-detail`).
   - Event-Handler für Bearbeiten/Löschen (JS-Dialog confirm -> DELETE).
   - Detail-View enthält Tabs (Stammdaten, Mietobjekte, Abrechnungen, Dokumente); Tabs wechseln zwischen eingebetteten Subviews.

3. **Mietobjekte**
   - Globale Liste oder per Immobilie gefiltert.
   - FilterBar (Immobilie, Status).
   - Inline-Badges für Vermietungsstatus (via Backend-Feld).
   - Detail-View zeigt Mietverhältnisse + Filelinks.

4. **Mieter**
   - Karteikartenliste mit Kontaktinfos.
   - Aktionen: „Neu“, „Bearbeiten“, „Mietverhältnisse anzeigen“.

5. **Mietverhältnisse**
   - Liste mit Spalten: Mieter, Objekt, Zeitraum, Status-Badge.
   - Form enthält Selects, die via API (`/api/unit`, `/api/tenant`) befüllt werden.
   - Detail zeigt cond, Beträge, Dokumente.

6. **Buchungen**
   - Gemeinsame View für Einnahmen/Ausgaben.
   - Filter: Typ, Jahr, Immobilie, Kategorie.
   - Summenzeile (berechnet in JS aus Response).
   - Formular mit Checkbox „Jahresbetrag“.

7. **Abrechnungen**
   - Liste pro Immobilie/Jahr mit Link zur Datei.
   - Button „Abrechnung erstellen“ öffnet kleines Formular (Select Immobilie, Jahr, Submit).
   - Nach Erfolg: Liste aktualisieren, NC-Toast.

8. **Dokumente Sidebar**
   - Wird von Detail-Views aktiviert.
   - Zeigt verknüpfte Dateien, Buttons `Öffnen`, `Entfernen`.
   - Bei Mietern nur `Öffnen`.

---

**API-Layer**

`Immo.Api` kapselt alle AJAX Calls (fetch). Wichtige Methoden:

- `request(method, url, data = null, expectHtml = false)`
  - Setzt Header:
    - `'OCS-APIREQUEST': 'true'`
    - `'requesttoken': OC.requestToken`
    - `'Content-Type': 'application/json'` (bei JSON)
  - Gibt Promise zurück (`response.json()` oder `response.text()`).

- Helfer:
  - `getJson(url)`
  - `postJson(url, data)`
  - `putJson(url, data)`
  - `delete(url)`
  - `getHtml(url)`

View-Module verwenden diese Funktionen, nicht direkt `fetch`.

---

**Beispielcode (Auszug `js/immo-main.js`)**

```javascript
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
			selectedPropId: 'all',
			userRole: document.body.dataset.userRole || 'manager'
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
			'dashboard': { view: Immo.Views.Dashboard, url: '/apps/immo/view/dashboard' },
			'properties': { view: Immo.Views.Properties, url: '/apps/immo/view/property-list' },
			'units': { view: Immo.Views.Units, url: '/apps/immo/view/unit-list' },
			'tenants': { view: Immo.Views.Tenants, url: '/apps/immo/view/tenant-list' },
			'leases': { view: Immo.Views.Leases, url: '/apps/immo/view/lease-list' },
			'bookings': { view: Immo.Views.Bookings, url: '/apps/immo/view/booking-list' },
			'reports': { view: Immo.Views.Reports, url: '/apps/immo/view/report-list' }
		};

		const setActiveNav = (route) => {
			document.querySelectorAll('#app-navigation li').forEach(li => {
				li.classList.toggle('active', li.dataset.route === route);
			});
		};

		const load = async (route, params = {}) => {
			const config = routes[route];
			if (!config) {
				console.error('route missing', route);
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
			} catch (err) {
				content.innerHTML = `<div class="error">${t('immo', 'Failed to load view')}</div>`;
				console.error(err);
			}
		};

		return { load };
	})();

	Immo.Views = Immo.Views || {};

	Immo.Views.Dashboard = (() => {
		const bindEvents = () => {
			loadStats();
			setupFilters();
		};

		const setupFilters = () => {
			const yearSelect = document.querySelector('[data-dashboard-year]');
			if (yearSelect) {
				yearSelect.value = Immo.StateStore.get('selectedYear');
				yearSelect.addEventListener('change', (e) => {
					Immo.StateStore.set('selectedYear', parseInt(e.target.value, 10));
					loadStats();
				});
			}
		};

		const loadStats = async () => {
			const year = Immo.StateStore.get('selectedYear');
			const target = document.querySelector('#dashboard-stats');
			if (!target) return;
			target.classList.add('loading');
			try {
				const data = await Immo.Api.getJson(`/apps/immo/api/dashboard?year=${year}`);
				target.querySelector('[data-metric="properties"]').textContent = data.properties;
				target.querySelector('[data-metric="units"]').textContent = data.units;
				target.querySelector('[data-metric="leases"]').textContent = data.activeLeases;
				target.querySelector('[data-metric="rent"]').textContent = `${data.coldRentYear} €`;
				target.querySelector('[data-metric="rentPerSqm"]').textContent = `${data.rentPerSqm} €`;
			} catch (err) {
				OC.Notification.showTemporary(t('immo', 'Could not load dashboard'));
				console.error(err);
			} finally {
				target.classList.remove('loading');
			}
		};

		return { bindEvents };
	})();

	Immo.Views.Properties = (() => {
		const bindEvents = () => {
			const addBtn = document.querySelector('[data-action="prop-add"]');
			addBtn && addBtn.addEventListener('click', () => openForm());
			document.querySelectorAll('[data-action="prop-edit"]').forEach(btn => {
				btn.addEventListener('click', (e) => openForm(e.currentTarget.dataset.id));
			});
			document.querySelectorAll('[data-action="prop-delete"]').forEach(btn => {
				btn.addEventListener('click', (e) => deleteProp(e.currentTarget.dataset.id));
			});
		};

		const openForm = (propId = null) => {
			const modal = document.getElementById('prop-form-modal');
			if (!modal) return;
			modal.classList.add('open');
			if (propId) {
				populateForm(propId);
			} else {
				modal.querySelector('form').reset();
			}
			modal.querySelector('form').onsubmit = (event) => {
				event.preventDefault();
				save(propId, new FormData(event.target));
			};
		};

		const populateForm = async (propId) => {
			try {
				const data = await Immo.Api.getJson(`/apps/immo/api/prop/${propId}`);
				const form = document.querySelector('#prop-form-modal form');
				Object.keys(data).forEach(key => {
					const input = form.querySelector(`[name="${key}"]`);
					if (input) input.value = data[key] || '';
				});
			} catch (err) {
				OC.Notification.showTemporary(t('immo', 'Failed to load property'));
			}
		};

		const serializeFormData = (formData) => {
			const obj = {};
			formData.forEach((value, key) => {
				obj[key] = value.trim();
			});
			return obj;
		};

		const save = async (propId, formData) => {
			const payload = serializeFormData(formData);
			const url = propId ? `/apps/immo/api/prop/${propId}` : '/apps/immo/api/prop';
			const method = propId ? Immo.Api.putJson : Immo.Api.postJson;
			const submitBtn = document.querySelector('#prop-form-modal button[type="submit"]');
			submitBtn.disabled = true;
			try {
				await method(url, payload);
				OC.Notification.showTemporary(t('immo', 'Property saved'));
				Immo.ViewRouter.load('properties');
			} catch (err) {
				OC.Notification.showTemporary(t('immo', 'Could not save property'));
				console.error(err);
			} finally {
				submitBtn.disabled = false;
			}
		};

		const deleteProp = (id) => {
			OC.dialogs.confirm(
				t('immo', 'Do you really want to delete this property?'),
				t('immo', 'Delete property'),
				async (confirmed) => {
					if (!confirmed) return;
					try {
						await Immo.Api.delete(`/apps/immo/api/prop/${id}`);
						OC.Notification.showTemporary(t('immo', 'Property deleted'));
						Immo.ViewRouter.load('properties');
					} catch (err) {
						OC.Notification.showTemporary(t('immo', 'Delete failed'));
						console.error(err);
					}
				},
				true
			);
		};

		return { bindEvents };
	})();

	Immo.Views.Reports = (() => {
		const bindEvents = () => {
			const form = document.querySelector('#report-create-form');
			if (form) {
				form.addEventListener('submit', (e) => {
					e.preventDefault();
					createReport(new FormData(form));
				});
			}
		};

		const createReport = async (formData) => {
			const payload = {
				propId: parseInt(formData.get('propId'), 10),
				year: parseInt(formData.get('year'), 10)
			};
			const submitBtn = document.querySelector('#report-create-form button[type="submit"]');
			submitBtn.disabled = true;
			submitBtn.textContent = t('immo', 'Creating...');
			try {
				await Immo.Api.postJson('/apps/immo/api/report', payload);
				OC.Notification.showTemporary(t('immo', 'Report created'));
				Immo.ViewRouter.load('reports', { propId: payload.propId });
			} catch (err) {
				OC.Notification.showTemporary(t('immo', 'Report creation failed'));
				console.error(err);
			} finally {
				submitBtn.disabled = false;
				submitBtn.textContent = t('immo', 'Create report');
			}
		};

		return { bindEvents };
	})();

	Immo.LayoutShell = (() => {
		const buildNavigation = () => {
			const nav = document.getElementById('app-navigation');
			const role = Immo.StateStore.get('userRole');
			const navItems = [
				{ route: 'dashboard', label: t('immo', 'Dashboard'), roles: ['manager'] },
				{ route: 'properties', label: t('immo', 'Properties'), roles: ['manager'] },
				{ route: 'units', label: t('immo', 'Units'), roles: ['manager'] },
				{ route: 'tenants', label: t('immo', 'Tenants'), roles: ['manager'] },
				{ route: 'leases', label: t('immo', 'Leases'), roles: ['manager', 'tenant'] },
				{ route: 'bookings', label: t('immo', 'Bookings'), roles: ['manager'] },
				{ route: 'reports', label: t('immo', 'Reports'), roles: ['manager', 'tenant'] }
			];
			const ul = document.createElement('ul');
			navItems
				.filter(item => item.roles.includes(role))
				.forEach(item => {
					const li = document.createElement('li');
					li.dataset.route = item.route;
					li.textContent = item.label;
					li.addEventListener('click', () => Immo.ViewRouter.load(item.route));
					ul.appendChild(li);
				});
			nav.innerHTML = '';
			nav.appendChild(ul);
		};

		const init = () => {
			buildNavigation();
			const defaultRoute = Immo.StateStore.get('userRole') === 'tenant' ? 'leases' : 'dashboard';
			Immo.ViewRouter.load(defaultRoute);
		};

		return { init };
	})();

	document.addEventListener('DOMContentLoaded', () => {
		Immo.LayoutShell.init();
	});
})();
```

Dieser Code demonstriert Navigation, State-Handling, AJAX-Layer sowie exemplarische View-Module (Dashboard, Immobilien, Abrechnungen). Alle sichtbaren Strings nutzen `t('immo', '…')`, alle Requests setzen Nextcloud-Header.