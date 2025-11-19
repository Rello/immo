## Komponenten

### Globale Struktur

- `Immo.App`
  - Initialisiert die App
  - Baut Navigation in `#app-navigation`
  - Lädt Views per AJAX in `#app-content`
  - Rollenabhängige Navigation (verwalter vs. mieter)

- `Immo.Api`
  - Zentrale AJAX-Helfer mit:
    - `request(method, url, data, expectHtml)`
    - spezialisierte Wrapper: `getProps`, `createProp`, `updateProp`, `deleteProp`, etc.
  - Setzt Header:
    - `OCS-APIREQUEST: true`
    - `requesttoken: OC.requestToken`

- `Immo.Router` (optional leichtgewichtig)
  - Verknüpft Navigationseinträge mit View-Loadern
  - Kann später `window.history.pushState` nutzen

- `Immo.UI`
  - Kleine UI-Helfer:
    - `showLoading(targetEl)`
    - `showError(targetEl, message)`
    - `showSuccess(message)` (NC-Toast via `OC.Notification` falls vorhanden)
    - `confirm(message, onOk)`

- `Immo.Views.*`
  - Je View-Modul ist für Rendern, Events und Interaktion mit `Immo.Api` zuständig.
  - Kern-Views für MVP:
    - `Immo.Views.Dashboard`
    - `Immo.Views.Properties`
    - `Immo.Views.PropertyDetail`
    - `Immo.Views.Units`
    - `Immo.Views.Tenants`
    - `Immo.Views.Leases`
    - `Immo.Views.Bookings`
    - `Immo.Views.Reports`
    - `Immo.Views.TenantHome` (Mieter-Startseite light)

---

## States

- `Immo.State`
  - `currentView` – Kennung der aktiven View (`'dashboard'`, `'props'`, …)
  - `currentUserRole` – `'verwalter'` oder `'mieter'` (vom Backend z. B. als JS-Var im Template übergeben)
  - `filters` – einfache Filterobjekte:
    - `filters.year`
    - `filters.propId`
    - ggf. `filters.status` etc.
  - `cache` (optional, einfaches Objekt) für zuletzt geladene Listen (z. B. `props`, `unitsByProp`)

View-spezifische UI-States werden pro View-Modul gehalten (lokale Variablen in IIFE), etwa:

- `selectedPropId` in `Immo.Views.PropertyDetail`
- `editingId` in Formularen
- `isLoading`-Flags pro View (meist implizit über Spinner im DOM)

---

## Views

### 1. Dashboard (`Immo.Views.Dashboard`)

- Container: `#app-content`
- Bereiche:
  - Filter-Leiste:
    - Jahr-Auswahl (Dropdown oder Input)
    - Immobilie-Auswahl (Dropdown, optional)
  - Kennzahlen-Kacheln:
    - Anzahl Immobilien
    - Anzahl Mietobjekte
    - Aktive Mietverhältnisse
    - Summe Soll-Kaltmiete (Jahr)
    - Beispiel Miete/m²
  - „Offene Punkte“ (für V1: einfache Liste oder Platzhalter)

- Daten:
  - `GET /apps/immo/api/dashboard?year=YYYY`

### 2. Immobilien-Liste (`Immo.Views.Properties`)

- Tabelle:
  - Spalten: Name, Adresse (kurz), Aktionen
  - Zeile klickbar → Detail
- Header:
  - Button „Neue Immobilie“

- Daten:
  - `GET /apps/immo/api/prop`

- Aktionen:
  - Neu (Modal/Inline Formular)
  - Bearbeiten (Inline oder in Detail)
  - Löschen (Confirm)

### 3. Immobilien-Detail (`Immo.Views.PropertyDetail`)

- Tabs/Sektionen (ohne Lib, z. B. einfache CSS-Tabs):
  - Stammdaten (Form)
  - Mietobjekte (eingebettete Liste + „Neues Mietobjekt“)
  - Abrechnungen (Liste mit Jahr, Link)
  - Dokumente (Liste verknüpfter Files + „Datei verknüpfen“)

- Daten:
  - `GET /apps/immo/api/prop/{id}` (Stammdaten)
  - `GET /apps/immo/api/unit?propId={id}` (Units)
  - `GET /apps/immo/api/report?propId={id}` (Reports)
  - `GET /apps/immo/api/filelink?objType=prop&objId={id}` (Dokumente)

### 4. Mietobjekte-Liste (`Immo.Views.Units`)

- Globale Liste oder gefiltert nach Immobilie
- Spalten: Bezeichnung, Immobilie, Fläche, Status
- Daten:
  - `GET /apps/immo/api/unit` (optional mit `propId`)

### 5. Mieter-Liste & Detail (`Immo.Views.Tenants`)

- Liste:
  - Name, Kontakt, Kundennr.
  - „Neuer Mieter“
- Detail:
  - Stammdaten
  - Zugeordnete Mietverhältnisse (optional über Lease-Filter)
  - Dokumente (`filelink` mit `objType=tenant`)

- Daten:
  - `GET /apps/immo/api/tenant`
  - (Leases via `GET /apps/immo/api/lease?tenantId=...`)
  - Filelinks via `GET /apps/immo/api/filelink?objType=tenant&objId=...`

### 6. Mietverhältnisse (`Immo.Views.Leases`)

- Filter:
  - Immobilie, Status, Jahr
- Tabelle:
  - Mieter, Objekt, Zeitraum, Kaltmiete, Status
- Daten:
  - `GET /apps/immo/api/lease?propId=&status=&year=`

### 7. Buchungen (`Immo.Views.Bookings`)

- Eine View, Filter „Typ: Einnahmen/Ausgaben“
- Filter:
  - Jahr (Default: aktuelles)
  - Immobilie
  - Kategorie
  - Typ (in/out)
- Tabelle:
  - Datum, Immobilie, Objekt, Mietverhältnis, Kategorie, Betrag
- Aktionen:
  - Neue Buchung
  - Bearbeiten / Löschen
- Daten:
  - `GET /apps/immo/api/book?propId=&year=&type=&cat=`

### 8. Abrechnungen (`Immo.Views.Reports`)

- Filter:
  - Jahr, Immobilie
- Tabelle:
  - Immobilie, Jahr, Pfad / Link
- Aktionen:
  - Abrechnung erstellen (Formular mit Immobilie + Jahr)
- Daten:
  - `GET /apps/immo/api/report?propId=&year=`
  - `POST /apps/immo/api/report` für Erstellung

### 9. Tenant-Start (`Immo.Views.TenantHome` – für Mieterrolle)

- Einfache Liste:
  - Eigene Mietverhältnisse (aktuell zuerst)
- Links:
  - Zu Abrechnungen (gefiltert)
- Daten:
  - `GET /apps/immo/api/lease` (Backend filtert auf `uid_user=currentUser`)
  - `GET /apps/immo/api/report` mit passenden Filtern (Backend begrenzt)

---

## API-Layer (Frontend)

Alle Requests gehen über `Immo.Api.request`. Pro Hauptressource einfache Wrapper:

- Properties:
  - `Immo.Api.getProps()`
  - `Immo.Api.getProp(id)`
  - `Immo.Api.createProp(data)`
  - `Immo.Api.updateProp(id, data)`
  - `Immo.Api.deleteProp(id)`

- Units, Tenants, Leases, Bookings, Reports, Filelinks analog.

Die URLs entsprechen den im Input beschriebenen Endpunkten, z. B.:

- `/apps/immo/api/prop`
- `/apps/immo/api/prop/{id}`
- `/apps/immo/api/book?propId=1&year=2024&type=in`

---

## Beispielcode (Vanilla JS, Modul-Pattern)

```js
/* global OC, t */

window.Immo = window.Immo || {};

/**
 * API-Layer
 */
(function (ns) {
	'use strict';

	const baseUrl = OC.generateUrl('/apps/immo');

	const request = (method, path, data = null, expectHtml = false) => {
		const url = baseUrl + path;
		const headers = {
			'OCS-APIREQUEST': 'true',
			'requesttoken': OC.requestToken,
		};
		const options = {
			method,
			headers,
		};
		if (data) {
			headers['Content-Type'] = 'application/json';
			options.body = JSON.stringify(data);
		}
		return fetch(url, options).then(response => {
			if (!response.ok) {
				return response.text().then(text => {
					let msg = text || t('immo', 'Request failed');
					throw new Error(msg);
				});
			}
			if (expectHtml) {
				return response.text();
			}
			return response.json();
		});
	};

	ns.Api = {
		request,

		// Properties
		getProps() {
			return request('GET', '/api/prop');
		},
		getProp(id) {
			return request('GET', '/api/prop/' + encodeURIComponent(id));
		},
		createProp(data) {
			return request('POST', '/api/prop', data);
		},
		updateProp(id, data) {
			return request('PUT', '/api/prop/' + encodeURIComponent(id), data);
		},
		deleteProp(id) {
			return request('DELETE', '/api/prop/' + encodeURIComponent(id));
		},

		// Units
		getUnits(filter = {}) {
			const params = [];
			if (filter.propId) {
				params.push('propId=' + encodeURIComponent(filter.propId));
			}
			const qs = params.length ? '?' + params.join('&') : '';
			return request('GET', '/api/unit' + qs);
		},

		// Dashboard
		getDashboard(year) {
			const qs = year ? '?year=' + encodeURIComponent(year) : '';
			return request('GET', '/api/dashboard' + qs);
		},

		// Reports
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

		// Filelinks
		getFilelinks(objType, objId) {
			const qs = '?objType=' + encodeURIComponent(objType) +
				'&objId=' + encodeURIComponent(objId);
			return request('GET', '/api/filelink' + qs);
		},
		createFilelink(data) {
			return request('POST', '/api/filelink', data);
		},
	};
}(window.Immo));

/**
 * UI Helper
 */
(function (ns) {
	'use strict';

	const showLoading = (target) => {
		target.innerHTML = '<div class="immo-loading">' +
			t('immo', 'Loading…') +
		'</div>';
	};

	const showError = (target, message) => {
		target.innerHTML = '<div class="immo-error">' +
			OC.Utilities.escapeHTML(message) +
		'</div>';
	};

	const showSuccess = (message) => {
		if (OC.Notification && OC.Notification.showTemporary) {
			OC.Notification.showTemporary(message);
		} else {
			// Fallback: simple alert
			window.alert(message);
		}
	};

	const confirmDialog = (message, onOk) => {
		if (window.confirm(message)) {
			onOk();
		}
	};

	ns.UI = {
		showLoading,
		showError,
		showSuccess,
		confirm: confirmDialog,
	};
}(window.Immo));

/**
 * State
 */
(function (ns) {
	'use strict';

	ns.State = {
		currentView: null,
		currentUserRole: window.ImmoCurrentUserRole || 'verwalter', // vom PHP-Template gesetzt
		filters: {
			year: (new Date()).getFullYear(),
			propId: null,
		},
	};
}(window.Immo));

/**
 * Dashboard View
 */
(function (ns) {
	'use strict';

	const State = ns.State;
	const Api = ns.Api;
	const UI = ns.UI;

	const render = (container, data) => {
		const year = data.year;
		let html = '';

		html += '<div class="immo-dashboard">';
		html += '<div class="immo-dashboard-filters">';
		html += '<label>' + t('immo', 'Year') + ': ';
		html += '<input type="number" id="immo-dashboard-year" value="' + OC.Utilities.escapeHTML('' + year) + '">';
		html += '</label>';
		html += '<button id="immo-dashboard-refresh" class="primary">';
		html += OC.Utilities.escapeHTML(t('immo', 'Update'));
		html += '</button>';
		html += '</div>';

		html += '<div class="immo-dashboard-cards">';
		html += card(t('immo', 'Properties'), data.propCount);
		html += card(t('immo', 'Units'), data.unitCount);
		html += card(t('immo', 'Active leases'), data.activeLeaseCount);
		html += card(t('immo', 'Annual rent (cold)'), data.annualRentSum);
		if (data.sampleRentPerSqm) {
			html += card(t('immo', 'Sample rent per m²'), data.sampleRentPerSqm.value);
		}
		html += '</div>';

		// Platzhalter Offene Punkte
		html += '<div class="immo-dashboard-open">';
		html += '<h3>' + OC.Utilities.escapeHTML(t('immo', 'Open items')) + '</h3>';
		html += '<p>' + OC.Utilities.escapeHTML(t('immo', 'This section will show upcoming lease changes and uncategorized bookings.')) + '</p>';
		html += '</div>';

		html += '</div>';

		container.innerHTML = html;

		bindEvents(container);
	};

	const card = (title, value) => {
		return '<div class="immo-card">' +
			'<div class="immo-card-title">' + OC.Utilities.escapeHTML(title) + '</div>' +
			'<div class="immo-card-value">' + OC.Utilities.escapeHTML(value + '') + '</div>' +
		'</div>';
	};

	const bindEvents = (container) => {
		const yearInput = container.querySelector('#immo-dashboard-year');
		const refreshBtn = container.querySelector('#immo-dashboard-refresh');
		if (refreshBtn && yearInput) {
			refreshBtn.addEventListener('click', () => {
				const y = parseInt(yearInput.value, 10);
				if (!isNaN(y)) {
					State.filters.year = y;
					load(container);
				}
			});
		}
	};

	const load = (container) => {
		UI.showLoading(container);
		Api.getDashboard(State.filters.year)
			.then(data => {
				render(container, data);
			})
			.catch(err => {
				UI.showError(container, err.message);
			});
	};

	ns.Views = ns.Views || {};
	ns.Views.Dashboard = {
		load,
	};

}(window.Immo));

/**
 * Properties List View
 */
(function (ns) {
	'use strict';

	const Api = ns.Api;
	const UI = ns.UI;

	let lastData = [];

	const load = (container) => {
		UI.showLoading(container);
		Api.getProps()
			.then(data => {
				lastData = data;
				render(container);
			})
			.catch(err => {
				UI.showError(container, err.message);
			});
	};

	const render = (container) => {
		let html = '';
		html += '<div class="immo-props">';
		html += '<div class="immo-props-header">';
		html += '<h2>' + OC.Utilities.escapeHTML(t('immo', 'Properties')) + '</h2>';
		html += '<button id="immo-prop-new" class="primary">' +
			OC.Utilities.escapeHTML(t('immo', 'New property')) +
		'</button>';
		html += '</div>';

		if (!lastData.length) {
			html += '<p>' + OC.Utilities.escapeHTML(t('immo', 'No properties yet.')) + '</p>';
			html += '</div>';
			container.innerHTML = html;
			bindEvents(container);
			return;
		}

		html += '<table class="immo-table">';
		html += '<thead><tr>';
		html += '<th>' + OC.Utilities.escapeHTML(t('immo', 'Name')) + '</th>';
		html += '<th>' + OC.Utilities.escapeHTML(t('immo', 'Address')) + '</th>';
		html += '<th></th>';
		html += '</tr></thead>';
		html += '<tbody>';
		lastData.forEach(p => {
			const addr = [p.street, p.zip, p.city].filter(Boolean).join(', ');
			html += '<tr data-id="' + OC.Utilities.escapeHTML('' + p.id) + '">';
			html += '<td class="immo-prop-name">' + OC.Utilities.escapeHTML(p.name) + '</td>';
			html += '<td>' + OC.Utilities.escapeHTML(addr) + '</td>';
			html += '<td>';
			html += '<button class="immo-prop-edit">' + OC.Utilities.escapeHTML(t('immo', 'Edit')) + '</button> ';
			html += '<button class="immo-prop-delete">' + OC.Utilities.escapeHTML(t('immo', 'Delete')) + '</button>';
			html += '</td>';
			html += '</tr>';
		});
		html += '</tbody></table>';
		html += '</div>';

		container.innerHTML = html;
		bindEvents(container);
	};

	const bindEvents = (container) => {
		const newBtn = container.querySelector('#immo-prop-new');
		if (newBtn) {
			newBtn.addEventListener('click', () => {
				showNewForm(container);
			});
		}

		container.querySelectorAll('tr[data-id]').forEach(tr => {
			const id = tr.getAttribute('data-id');
			const nameCell = tr.querySelector('.immo-prop-name');
			nameCell.addEventListener('click', () => {
				// Detail-View laden
				if (ns.Views && ns.Views.PropertyDetail) {
					ns.Views.PropertyDetail.load(document.getElementById('app-content'), id);
				}
			});

			const editBtn = tr.querySelector('.immo-prop-edit');
			editBtn.addEventListener('click', (e) => {
				e.stopPropagation();
				showEditForm(container, id);
			});

			const delBtn = tr.querySelector('.immo-prop-delete');
			delBtn.addEventListener('click', (e) => {
				e.stopPropagation();
				UI.confirm(t('immo', 'Delete this property?'), () => {
					Api.deleteProp(id)
						.then(() => {
							UI.showSuccess(t('immo', 'Property deleted'));
							load(container);
						})
						.catch(err => UI.showError(container, err.message));
				});
			});
		});
	};

	const showNewForm = (container) => {
		const formHtml = buildFormHtml({
			title: t('immo', 'Create property'),
		});
		container.innerHTML = formHtml;
		bindFormEvents(container, null);
	};

	const showEditForm = (container, id) => {
		const prop = lastData.find(p => '' + p.id === '' + id);
		if (!prop) {
			UI.showError(container, t('immo', 'Property not found'));
			return;
		}
		const formHtml = buildFormHtml({
			title: t('immo', 'Edit property'),
			data: prop,
		});
		container.innerHTML = formHtml;
		bindFormEvents(container, id);
	};

	const buildFormHtml = (opts) => {
		const d = opts.data || {};
		let html = '';
		html += '<div class="immo-prop-form">';
		html += '<h2>' + OC.Utilities.escapeHTML(opts.title) + '</h2>';
		html += '<form id="immo-prop-form">';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Name')) + ' *<br>';
		html += '<input type="text" name="name" required value="' + (d.name ? OC.Utilities.escapeHTML(d.name) : '') + '">';
		html += '</label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Street')) + '<br>';
		html += '<input type="text" name="street" value="' + (d.street ? OC.Utilities.escapeHTML(d.street) : '') + '">';
		html += '</label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'ZIP')) + '<br>';
		html += '<input type="text" name="zip" value="' + (d.zip ? OC.Utilities.escapeHTML(d.zip) : '') + '">';
		html += '</label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'City')) + '<br>';
		html += '<input type="text" name="city" value="' + (d.city ? OC.Utilities.escapeHTML(d.city) : '') + '">';
		html += '</label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Country')) + '<br>';
		html += '<input type="text" name="country" value="' + (d.country ? OC.Utilities.escapeHTML(d.country) : '') + '">';
		html += '</label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Type')) + '<br>';
		html += '<input type="text" name="type" value="' + (d.type ? OC.Utilities.escapeHTML(d.type) : '') + '">';
		html += '</label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Note')) + '<br>';
		html += '<textarea name="note">' + (d.note ? OC.Utilities.escapeHTML(d.note) : '') + '</textarea>';
		html += '</label><br>';
		html += '<button type="submit" class="primary">' + OC.Utilities.escapeHTML(t('immo', 'Save')) + '</button> ';
		html += '<button type="button" id="immo-prop-cancel">' + OC.Utilities.escapeHTML(t('immo', 'Cancel')) + '</button>';
		html += '</form>';
		html += '</div>';
		return html;
	};

	const bindFormEvents = (container, id) => {
		const form = container.querySelector('#immo-prop-form');
		const cancelBtn = container.querySelector('#immo-prop-cancel');

		form.addEventListener('submit', (e) => {
			e.preventDefault();
			const formData = new window.FormData(form);
			const data = {};
			['name', 'street', 'zip', 'city', 'country', 'type', 'note']
				.forEach(field => data[field] = formData.get(field) || '');

			if (!data.name.trim()) {
				UI.showError(container, t('immo', 'Name is required'));
				return;
			}

			const op = id ? Api.updateProp(id, data) : Api.createProp(data);

			op.then(() => {
				UI.showSuccess(t('immo', 'Property saved'));
				load(container);
			})
				.catch(err => UI.showError(container, err.message));
		});

		cancelBtn.addEventListener('click', () => {
			load(container);
		});
	};

	ns.Views = ns.Views || {};
	ns.Views.Properties = {
		load,
	};
}(window.Immo));

/**
 * Property Detail View (Stammdaten + Units + Reports + Docs)
 * – Beispielhaft nur Stammdaten + Units-Gerüst
 */
(function (ns) {
	'use strict';

	const Api = ns.Api;
	const UI = ns.UI;

	const load = (container, id) => {
		UI.showLoading(container);

		Promise.all([
			Api.getProp(id),
			Api.getUnits({ propId: id }),
			Api.getReports({ propId: id }),
			Api.getFilelinks('prop', id),
		]).then(([prop, units, reports, files]) => {
			render(container, prop, units, reports, files);
		}).catch(err => {
			UI.showError(container, err.message);
		});
	};

	const render = (container, prop, units, reports, files) => {
		let html = '';
		html += '<div class="immo-prop-detail" data-id="' + OC.Utilities.escapeHTML('' + prop.id) + '">';
		html += '<h2>' + OC.Utilities.escapeHTML(prop.name) + '</h2>';

		// Tab-Header
		html += '<div class="immo-tabs">';
		html += '<button class="immo-tab active" data-tab="main">' + OC.Utilities.escapeHTML(t('immo', 'Details')) + '</button>';
		html += '<button class="immo-tab" data-tab="units">' + OC.Utilities.escapeHTML(t('immo', 'Units')) + '</button>';
		html += '<button class="immo-tab" data-tab="reports">' + OC.Utilities.escapeHTML(t('immo', 'Reports')) + '</button>';
		html += '<button class="immo-tab" data-tab="files">' + OC.Utilities.escapeHTML(t('immo', 'Documents')) + '</button>';
		html += '</div>';

		// Details Tab
		html += '<div class="immo-tab-content" data-tab="main">';
		html += '<form id="immo-prop-detail-form">';
		// (vereinfachte Wiederholung der Felder; in Realität: DRY mit Helferfunktion)
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Name')) + '<br>';
		html += '<input type="text" name="name" value="' + OC.Utilities.escapeHTML(prop.name || '') + '"></label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Street')) + '<br>';
		html += '<input type="text" name="street" value="' + OC.Utilities.escapeHTML(prop.street || '') + '"></label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'ZIP')) + '<br>';
		html += '<input type="text" name="zip" value="' + OC.Utilities.escapeHTML(prop.zip || '') + '"></label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'City')) + '<br>';
		html += '<input type="text" name="city" value="' + OC.Utilities.escapeHTML(prop.city || '') + '"></label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Country')) + '<br>';
		html += '<input type="text" name="country" value="' + OC.Utilities.escapeHTML(prop.country || '') + '"></label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Type')) + '<br>';
		html += '<input type="text" name="type" value="' + OC.Utilities.escapeHTML(prop.type || '') + '"></label><br>';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Note')) + '<br>';
		html += '<textarea name="note">' + OC.Utilities.escapeHTML(prop.note || '') + '</textarea></label><br>';
		html += '<button type="submit" class="primary">' + OC.Utilities.escapeHTML(t('immo', 'Save')) + '</button>';
		html += '</form>';
		html += '</div>';

		// Units Tab
		html += '<div class="immo-tab-content hidden" data-tab="units">';
		html += '<div class="immo-prop-units-header">';
		html += '<h3>' + OC.Utilities.escapeHTML(t('immo', 'Units')) + '</h3>';
		html += '<button id="immo-unit-new" class="primary">' + OC.Utilities.escapeHTML(t('immo', 'New unit')) + '</button>';
		html += '</div>';
		if (!units.length) {
			html += '<p>' + OC.Utilities.escapeHTML(t('immo', 'No units yet.')) + '</p>';
		} else {
			html += '<table class="immo-table"><thead><tr>';
			html += '<th>' + OC.Utilities.escapeHTML(t('immo', 'Label')) + '</th>';
			html += '<th>' + OC.Utilities.escapeHTML(t('immo', 'Location')) + '</th>';
			html += '<th>' + OC.Utilities.escapeHTML(t('immo', 'Living area')) + '</th>';
			html += '</tr></thead><tbody>';
			units.forEach(u => {
				html += '<tr data-unit-id="' + OC.Utilities.escapeHTML('' + u.id) + '">';
				html += '<td>' + OC.Utilities.escapeHTML(u.label || '') + '</td>';
				html += '<td>' + OC.Utilities.escapeHTML(u.loc || '') + '</td>';
				html += '<td>' + OC.Utilities.escapeHTML((u.areaRes || 0) + '') + '</td>';
				html += '</tr>';
			});
			html += '</tbody></table>';
		}
		html += '</div>';

		// Reports Tab (einfaches Listing)
		html += '<div class="immo-tab-content hidden" data-tab="reports">';
		html += '<div class="immo-prop-reports-header">';
		html += '<h3>' + OC.Utilities.escapeHTML(t('immo', 'Reports')) + '</h3>';
		html += '<form id="immo-report-create-form">';
		html += '<label>' + OC.Utilities.escapeHTML(t('immo', 'Year')) + ': ';
		html += '<input type="number" name="year" value="' + OC.Utilities.escapeHTML('' + (new Date()).getFullYear()) + '">';
		html += '</label> ';
		html += '<button type="submit" class="primary">' + OC.Utilities.escapeHTML(t('immo', 'Create report')) + '</button>';
		html += '</form>';
		html += '</div>';
		if (!reports.length) {
			html += '<p>' + OC.Utilities.escapeHTML(t('immo', 'No reports yet.')) + '</p>';
		} else {
			html += '<ul>';
			reports.forEach(r => {
				html += '<li>' +
					OC.Utilities.escapeHTML('' + r.year) +
					' – ' +
					'<a href="' + OC.generateUrl('/f/' + r.fileId) + '" target="_blank">' +
						OC.Utilities.escapeHTML(t('immo', 'Open file')) +
					'</a>' +
				'</li>';
			});
			html += '</ul>';
		}
		html += '</div>';

		// Files Tab (Dokumente)
		html += '<div class="immo-tab-content hidden" data-tab="files">';
		html += '<h3>' + OC.Utilities.escapeHTML(t('immo', 'Documents')) + '</h3>';
		if (!files.length) {
			html += '<p>' + OC.Utilities.escapeHTML(t('immo', 'No documents linked yet.')) + '</p>';
		} else {
			html += '<ul>';
			files.forEach(f => {
				html += '<li>';
				html += '<a href="' + OC.generateUrl('/f/' + f.fileId) + '" target="_blank">';
				html += OC.Utilities.escapeHTML(f.path || ('#' + f.fileId));
				html += '</a>';
				html += '</li>';
			});
			html += '</ul>';
		}
		// Button "Datei verknüpfen" – in V1 nur Platzhalter (Filepicker-Integration später)
		html += '<button id="immo-filelink-add" class="primary">' +
			OC.Utilities.escapeHTML(t('immo', 'Link file')) +
		'</button>';
		html += '</div>';

		html += '</div>'; // .immo-prop-detail

		container.innerHTML = html;
		bindEvents(container, prop);
	};

	const bindEvents = (container, prop) => {
		// Tabs
		container.querySelectorAll('.immo-tab').forEach(btn => {
			btn.addEventListener('click', () => {
				const tab = btn.getAttribute('data-tab');
				container.querySelectorAll('.immo-tab').forEach(b => b.classList.remove('active'));
				btn.classList.add('active');
				container.querySelectorAll('.immo-tab-content').forEach(c => {
					if (c.getAttribute('data-tab') === tab) {
						c.classList.remove('hidden');
					} else {
						c.classList.add('hidden');
					}
				});
			});
		});

		// Save details
		const form = container.querySelector('#immo-prop-detail-form');
		form.addEventListener('submit', (e) => {
			e.preventDefault();
			const fd = new window.FormData(form);
			const data = {};
			['name', 'street', 'zip', 'city', 'country', 'type', 'note'].forEach(f => {
				data[f] = fd.get(f) || '';
			});
			Api.updateProp(prop.id, data)
				.then(() => UI.showSuccess(t('immo', 'Property saved')))
				.catch(err => UI.showError(container, err.message));
		});

		// Reports create
		const reportForm = container.querySelector('#immo-report-create-form');
		reportForm.addEventListener('submit', (e) => {
			e.preventDefault();
			const fd = new window.FormData(reportForm);
			const year = parseInt(fd.get('year'), 10);
			if (isNaN(year)) {
				UI.showError(container, t('immo', 'Year is required'));
				return;
			}
			Api.createReport({ propId: prop.id, year })
				.then(() => {
					UI.showSuccess(t('immo', 'Report created'));
					load(container, prop.id); // reload detail to update list
				})
				.catch(err => UI.showError(container, err.message));
		});

		// Filelink Button – Placeholder
		const fileBtn = container.querySelector('#immo-filelink-add');
		fileBtn.addEventListener('click', () => {
			// Hier später: OC.dialogs.filepicker integrieren, dann Api.createFilelink callen.
			UI.showError(container, t('immo', 'File linking is not implemented in this prototype.'));
		});
	};

	ns.Views = ns.Views || {};
	ns.Views.PropertyDetail = {
		load,
	};
}(window.Immo));

/**
 * Navigation + App Init
 */
(function (ns) {
	'use strict';

	const Views = ns.Views || {};
	const State = ns.State;

	const buildNavigation = () => {
		const nav = document.getElementById('app-navigation');
		if (!nav) {
			return;
		}
		let html = '<ul class="immo-nav">';

		// Rollenabhängig
		if (State.currentUserRole === 'verwalter') {
			html += navItem('dashboard', t('immo', 'Dashboard'));
			html += navItem('props', t('immo', 'Properties'));
			html += navItem('units', t('immo', 'Units'));
			html += navItem('tenants', t('immo', 'Tenants'));
			html += navItem('leases', t('immo', 'Leases'));
			html += navItem('bookings', t('immo', 'Bookings'));
			html += navItem('reports', t('immo', 'Reports'));
		} else {
			// Mieter
			html += navItem('tenantHome', t('immo', 'My rentals'));
			html += navItem('reports', t('immo', 'Reports'));
		}

		html += '</ul>';
		nav.innerHTML = html;

		nav.querySelectorAll('li[data-view]').forEach(li => {
			li.addEventListener('click', () => {
				const view = li.getAttribute('data-view');
				nav.querySelectorAll('li').forEach(x => x.classList.remove('active'));
				li.classList.add('active');
				loadView(view);
			});
		});
	};

	const navItem = (view, label) => {
		return '<li data-view="' + view + '">' +
			OC.Utilities.escapeHTML(label) +
		'</li>';
	};

	const loadView = (view) => {
		const content = document.getElementById('app-content');
		State.currentView = view;

		if (view === 'dashboard' && Views.Dashboard) {
			Views.Dashboard.load(content);
		} else if (view === 'props' && Views.Properties) {
			Views.Properties.load(content);
		} else if (view === 'reports' && Views.Reports) {
			Views.Reports.load(content);
		} else if (view === 'tenantHome' && Views.TenantHome) {
			Views.TenantHome.load(content);
		} else {
			// Placeholder: einfache Info
			content.innerHTML = '<p>' + OC.Utilities.escapeHTML(t('immo', 'View not implemented yet.')) + '</p>';
		}
	};

	ns.App = {
		init() {
			buildNavigation();
			// initial view
			const initialView = (State.currentUserRole === 'verwalter') ? 'dashboard' : 'tenantHome';
			const nav = document.getElementById('app-navigation');
			const activeLi = nav.querySelector('li[data-view="' + initialView + '"]');
			if (activeLi) {
				activeLi.classList.add('active');
			}
			loadView(initialView);
		},
	};
}(window.Immo));

// DOM-Ready
document.addEventListener('DOMContentLoaded', function () {
	if (window.Immo && window.Immo.App) {
		window.Immo.App.init();
	}
});
```