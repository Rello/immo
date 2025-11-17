**04 Frontend Design**

## **Komponenten**

### **1. Globales App-Namespace**

JavaScript Namespace: [OCA.Immo](http://OCA.Immo)

Unter-Namespaces:

- [OCA.Immo.App](http://OCA.Immo.App)
  - Bootstrapping pro View
  - Routing im Frontend anhand data-immo-view Attribut
- [OCA.Immo](http://OCA.Immo).Api
  - Zentrale AJAX Schicht mit fetch
  - CSRF Header, Fehlerbehandlung, JSON-Parsing
- [OCA.Immo](http://OCA.Immo).UI
  - Wiederverwendbare Helfer: Tabellen, Filterleisten, Paginierung, Flash-Messages, Loader
- [OCA.Immo](http://OCA.Immo).Components
  - Screen- und Formular-Logik pro View

### **2. Komponenten Verwalter-UI**

Unter [OCA.Immo](http://OCA.Immo).Components.Admin:

- Dashboard
  - Bindet an #immo-dashboard
  - Lädt Kennzahlen und offene Punkte per AJAX
  - Steuert Jahres-Filter
- PropertiesList
  - Tabelle mit Immobilien
  - Suchfeld, Filter Jahr, Ort
- PropertyDetail
  - Tabs: Stammdaten, Mietobjekte, Kennzahlen, Dokumente
  - Lädt Teilbereiche per AJAX nach Tab-Wechsel
- UnitsList (optional eigenständige View)
- UnitDetail
  - Tabs: Mietverhältnisse, Einnahmen/Ausgaben, Dokumente
  - Button „Neues Mietverhältnis“
- TenantsList
- TenantDetail
  - Tabs: Mietverhältnisse, Abrechnungen, Dokumente
- LeaseForm
  - Anlage/Bearbeitung Mietverhältnis
  - Frontend-Validierung und AJAX-Validierung Überschneidungen
- TransactionsList
  - Gemeinsame Liste Einnahmen/Ausgaben mit Filterleiste
- TransactionForm
  - Formular Einnahme/Ausgabe mit abhängigen Drop-downs
- CostAllocationView
  - Tabelle mit Verteilungsdaten pro Mietverhältnis
- StatementsWizard
  - Schritt-Assistent (Jahr/Scope → Checkliste → Zusammenfassung → Bestätigung)
- FileLinker
  - Integration Nextcloud File Picker
  - Anzeige und Entfernen von verknüpften Pfaden

### **3. Komponenten Mieter-UI**

Unter [OCA.Immo](http://OCA.Immo).Components.Tenant:

- Dashboard
  - Liste eigener aktueller Mietverhältnisse
- LeaseDetail
  - Stammdaten und Tabs Abrechnungen / Dokumente
- StatementsList
  - Liste aller Abrechnungen des Mieters mit Jahres-Filter

### **4. Shared UI Komponenten**

Unter [OCA.Immo](http://OCA.Immo).UI:

- FilterBar
  - Initialisierung von <form data-immo-filter> mit AJAX-Submit in Zielcontainer
- TableSorter
  - Klick auf Spaltenköpfe, Sortierzustand in URL-Params oder Datenattributen
- Paginator
  - Klick auf Seitenlinks per AJAX
- Flash
  - Anzeige von Erfolgs- und Fehlermeldungen
- Loader
  - Overlay im Content-Bereich während AJAX Requests

---

## **Datenlogik**

### **1. Frontend State**

Pro View:

- kleines State-Objekt im jeweiligen Komponenten-Modul
  - z. B. currentYear, currentPropertyId, currentPage, currentSort
- State synchron zur URL-Query (für Deep Links)
- State Update:
  - bei Filter-Submit
  - bei Tab-Wechsel
  - bei Paginierung

Beispiel Dashboard State:

```
const state = {
  year: new Date().getFullYear(),
};
```

### **2. Lade-Flow pro View**

Grundprinzip:

1. Server liefert Grund-Template mit minimalen Daten
2. JS Modul liest data-\* Attribute, setzt initialen State
3. JS ruft passende JSON-Endpoints
4. JS rendert Inhalte in Container (meistens <tbody> oder <div data-immo-content>)

Beispiele:

- Dashboard:
  - HTML Template rendert leere Kennzahl-Kacheln
  - JS füllt Zahlen nach AJAX Request
- Listen:
  - HTML Template rendert erste Seite serverseitig
  - Filter/Paginierung anschließend per AJAX

### **3. Formulare**

- Standard: POST zum Controller, serverseitige Validierung, Redirect mit Flash-Message
- Ergänzend: leichte JS-Validierung
  - Pflichtfelder nicht leer
  - Zahlen > 0
  - Datums-Formate
- Speziell LeaseForm:
  - OnChange von Start-/Enddatum und Mietobjekt
  - AJAX Request /ocs/v2.php/apps/immo/api/v1/leases/validateOverlap
  - Anzeige von Konflikten im Formular

### **4. Datenformate Frontend ↔ Backend**

JSON Standard:

- Listen:

```
{
  "items": [...],
  "page": 1,
  "pageSize": 25,
  "total": 120
}
```

- 
- Dashboard Stats:

```
{
  "year": 2025,
  "propertiesCount": 5,
  "unitsCount": 40,
  "leasesCount": 35,
  "occupancyByProperty": [
    { "propertyId": 1, "name": "Haus A", "occupancy": 0.95 },
    ...
  ],
  "openIssues": [
    { "key": "leasesWithoutContract", "count": 3 },
    ...
  ]
}
```

- 
- Lease Overlap Validation:

```
{
  "hasOverlap": true,
  "conflicts": [
    {
      "leaseId": 12,
      "tenantName": "Max Mustermann",
      "startDate": "2024-01-01",
      "endDate": "2024-12-31"
    }
  ]
}
```

---

## **Schnittstellen**

### **1. HTML Views (klassische Routen)**

Wie angegeben, z. B.:

- GET /apps/immo/dashboard
- GET /apps/immo/properties
- GET /apps/immo/properties/{id}
- GET /apps/immo/tenants, GET /apps/immo/tenants/{id}
- GET /apps/immo/transactions
- GET /apps/immo/statements/wizard

Diese liefern PHP-Templates, binden immo-main.js und setzen data-immo-view.

### **2. JSON / AJAX Endpoints**

Vorschlag interne Routen (ohne OCS):

- GET /apps/immo/api/v1/properties
  - Query: page, pageSize, year, search
- GET /apps/immo/api/v1/properties/{id}/stats
  - Kennzahlen je Jahr
- GET /apps/immo/api/v1/properties/{id}/units
- GET /apps/immo/api/v1/units/{id}/leases
- GET /apps/immo/api/v1/units/{id}/transactions
- GET /apps/immo/api/v1/tenants
- GET /apps/immo/api/v1/tenants/{id}/leases
- GET /apps/immo/api/v1/tenants/{id}/statements
- GET /apps/immo/api/v1/transactions
  - Filter: year, propertyId, unitId, leaseId, category, type, page
- GET /apps/immo/api/v1/costAllocations
  - Filter: propertyId, year
- POST /apps/immo/api/v1/statements/preview
  - Rückgabe: Preview-Summen für Assistent Schritt 3

Vorschlag OCS-Endpoints (wie im Input):

- GET /ocs/v2.php/apps/immo/api/v1/stats/dashboard?year=YYYY
- POST /ocs/v2.php/apps/immo/api/v1/leases/validateOverlap

Beide liefern JSON und nutzen OCS Response Wrapper. Frontend liest [data.ocs.data](http://data.ocs.data).

### **3. Nextcloud APIs im Frontend**

- t('immo', 'Text')
  - Übersetzungen
- OC.webroot, OC.linkTo
  - Pfade aufbauen
- OC.requestToken
  - CSRF Schutz im Header
- File Picker:
  - historisch:

```
OC.dialogs.filepicker(
  t('immo', 'Datei wählen'),
  function (filePath, type) { ... },
  false,
  'file',
  true
);
```

---

## **Beispielcode**

Die Beispiele sind bewusst reduziert, damit du das Muster siehst.

### **1. appinfo/routes.php**

```
<?php

return [
    'routes' => [
        // HTML Views
        ['name' => 'dashboard#index', 'url' => '/dashboard', 'verb' => 'GET'],
        ['name' => 'property#index', 'url' => '/properties', 'verb' => 'GET'],
        ['name' => 'property#show', 'url' => '/properties/{id}', 'verb' => 'GET'],
        ['name' => 'tenant#index', 'url' => '/tenants', 'verb' => 'GET'],
        ['name' => 'tenant#show', 'url' => '/tenants/{id}', 'verb' => 'GET'],
        ['name' => 'transaction#index', 'url' => '/transactions', 'verb' => 'GET'],

        // JSON API
        ['name' => 'api#dashboardStats', 'url' => '/api/v1/stats/dashboard', 'verb' => 'GET'],
        ['name' => 'api#transactions', 'url' => '/api/v1/transactions', 'verb' => 'GET'],
        ['name' => 'api#costAllocations', 'url' => '/api/v1/costAllocations', 'verb' => 'GET'],
        ['name' => 'api#tenantStatements', 'url' => '/api/v1/tenants/{id}/statements', 'verb' => 'GET'],
    ],
    'ocs' => [
        ['name' => 'ocs#validateLeaseOverlap', 'url' => '/api/v1/leases/validateOverlap', 'verb' => 'POST'],
        ['name' => 'ocs#dashboardStats', 'url' => '/api/v1/stats/dashboard', 'verb' => 'GET'],
    ],
];
```

### **2. DashboardController (Auszug)**

```
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCA\Immo\Service\StatsService;
use OCA\Immo\Service\PermissionService;

class DashboardController extends Controller {

    /** @var StatsService */
    private $statsService;
    /** @var PermissionService */
    private $permissionService;
    /** @var string */
    private $userId;

    public function __construct(
        string $appName,
        IRequest $request,
        StatsService $statsService,
        PermissionService $permissionService,
        string $userId
    ) {
        parent::__construct($appName, $request);
        $this->statsService = $statsService;
        $this->permissionService = $permissionService;
        $this->userId = $userId;
    }

    /**
     * @NoAdminRequired
     */
    public function index(): TemplateResponse {
        if (!$this->permissionService->isAdmin($this->userId) && !$this->permissionService->isTenant($this->userId)) {
            // Nextcloud Standard Fehlerseite
            return new TemplateResponse('core', '403', [], 'blank');
        }

        $view = $this->permissionService->isAdmin($this->userId)
            ? 'admin-dashboard'
            : 'tenant-dashboard';

        return new TemplateResponse('immo', $view, [
            'pageTitle' => $this->permissionService->isAdmin($this->userId)
                ? $this->l10n->t('Immo Dashboard')
                : $this->l10n->t('Meine Mietverhältnisse'),
        ]);
    }
}
```

### **3. APIController für Dashboard Stats**

```
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\Immo\Service\StatsService;
use OCA\Immo\Service\PermissionService;

class ApiController extends Controller {

    private $statsService;
    private $permissionService;
    private $userId;

    public function __construct(
        string $appName,
        IRequest $request,
        StatsService $statsService,
        PermissionService $permissionService,
        string $userId
    ) {
        parent::__construct($appName, $request);
        $this->statsService = $statsService;
        $this->permissionService = $permissionService;
        $this->userId = $userId;
    }

    /**
     * @NoAdminRequired
     */
    public function dashboardStats(): JSONResponse {
        if (!$this->permissionService->isAdmin($this->userId)) {
            return new JSONResponse(['message' => 'Forbidden'], 403);
        }

        $year = (int) $this->request->getParam('year', (int) date('Y'));

        $data = $this->statsService->getDashboardStats($year);

        return new JSONResponse($data);
    }
}
```

### **4. Template**

### **templates/admin-dashboard.php**

```
<?php
script('immo', 'immo-main');
style('immo', 'style');
?>

<div id="immo-app" data-immo-view="admin-dashboard">
    <div class="section">
        <h2><?php p($l->t('Dashboard')); ?></h2>
        <div class="filters">
            <label>
                <?php p($l->t('Year')); ?>
                <select id="immo-dashboard-year">
                    <?php for ($y = date('Y') - 3; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?php p($y); ?>" <?php if ($y === (int) date('Y')) { print_unescaped('selected'); } ?>>
                            <?php p($y); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </label>
        </div>

        <div id="immo-dashboard-cards" data-immo-dashboard-cards>
            <div class="card" data-immo-card="properties">
                <div class="card-title"><?php p($l->t('Properties')); ?></div>
                <div class="card-value">-</div>
            </div>
            <div class="card" data-immo-card="units">
                <div class="card-title"><?php p($l->t('Units')); ?></div>
                <div class="card-value">-</div>
            </div>
            <div class="card" data-immo-card="leases">
                <div class="card-title"><?php p($l->t('Leases')); ?></div>
                <div class="card-value">-</div>
            </div>
        </div>

        <div id="immo-dashboard-open-issues" data-immo-open-issues>
            <!-- JS rendert offene Punkte -->
        </div>
    </div>
</div>
```

### **5. Globales JS**

### **js/immo-main.js**

```
/* global OC, t */

window.OCA = window.OCA || {};
OCA.Immo = OCA.Immo || {};

(function () {
    'use strict';

    OCA.Immo.Api = (function () {
        function buildUrl(path, params) {
            const base = OC.webroot + '/index.php/apps/immo' + path;
            if (!params) {
                return base;
            }
            const searchParams = new URLSearchParams(params);
            return base + '?' + searchParams.toString();
        }

        function get(path, params = {}) {
            const url = buildUrl(path, params);
            return fetch(url, {
                method: 'GET',
                headers: {
                    'requesttoken': OC.requestToken,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            }).then(handleResponse);
        }

        function post(path, data = {}) {
            const url = buildUrl(path);
            return fetch(url, {
                method: 'POST',
                headers: {
                    'requesttoken': OC.requestToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify(data),
            }).then(handleResponse);
        }

        function handleResponse(response) {
            if (!response.ok) {
                return response.json().catch(() => {
                    throw new Error('HTTP ' + response.status);
                }).then(function (json) {
                    const message = json && json.message ? json.message : ('HTTP ' + response.status);
                    throw new Error(message);
                });
            }
            return response.json();
        }

        return {
            get: get,
            post: post,
        };
    }());

    OCA.Immo.UI = OCA.Immo.UI || {};
    OCA.Immo.UI.Flash = (function () {
        function show(message, type) {
            var container = document.getElementById('immo-flash');
            if (!container) {
                container = document.createElement('div');
                container.id = 'immo-flash';
                document.body.appendChild(container);
            }
            container.textContent = message;
            container.className = 'immo-flash immo-flash-' + (type || 'info');
        }

        return {
            show: show,
        };
    }());

    OCA.Immo.Components = OCA.Immo.Components || {};
    OCA.Immo.Components.Admin = OCA.Immo.Components.Admin || {};
    OCA.Immo.Components.Tenant = OCA.Immo.Components.Tenant || {};

    // Dashboard Modul (Admin)
    OCA.Immo.Components.Admin.Dashboard = (function () {
        var state = {
            year: new Date().getFullYear(),
        };

        function init(rootEl) {
            var yearSelect = rootEl.querySelector('#immo-dashboard-year');
            if (yearSelect) {
                state.year = parseInt(yearSelect.value, 10);
                yearSelect.addEventListener('change', function () {
                    state.year = parseInt(yearSelect.value, 10);
                    loadData();
                });
            }
            loadData();
        }

        function loadData() {
            OCA.Immo.Api.get('/api/v1/stats/dashboard', { year: state.year })
                .then(function (data) {
                    renderCards(data);
                    renderOpenIssues(data.openIssues || []);
                })
                .catch(function (error) {
                    console.error(error);
                    OCA.Immo.UI.Flash.show(t('immo', 'Error loading dashboard data'), 'error');
                });
        }

        function renderCards(data) {
            var cardsRoot = document.querySelector('[data-immo-dashboard-cards]');
            if (!cardsRoot) {
                return;
            }
            updateCard(cardsRoot, 'properties', data.propertiesCount);
            updateCard(cardsRoot, 'units', data.unitsCount);
            updateCard(cardsRoot, 'leases', data.leasesCount);
        }

        function updateCard(root, key, value) {
            var card = root.querySelector('[data-immo-card="' + key + '"] .card-value');
            if (card) {
                card.textContent = typeof value === 'number' ? String(value) : '-';
            }
        }

        function renderOpenIssues(issues) {
            var container = document.querySelector('[data-immo-open-issues]');
            if (!container) {
                return;
            }
            container.innerHTML = '';
            if (!issues.length) {
                container.textContent = t('immo', 'No open issues for this year');
                return;
            }
            var list = document.createElement('ul');
            issues.forEach(function (issue) {
                var li = document.createElement('li');
                li.textContent = translateIssueKey(issue.key) + ': ' + issue.count;
                list.appendChild(li);
            });
            container.appendChild(list);
        }

        function translateIssueKey(key) {
            switch (key) {
                case 'leasesWithoutContract':
                    return t('immo', 'Leases without linked contract');
                case 'leasesWithoutStatement':
                    return t('immo', 'Leases without statement in selected year');
                case 'transactionsWithoutYear':
                    return t('immo', 'Transactions without year');
                default:
                    return key;
            }
        }

        return {
            init: init,
        };
    }());

    // Lease Form Modul (Überschneidungsprüfung)
    OCA.Immo.Components.Admin.LeaseForm = (function () {
        var form;
        var overlapContainer;

        function init(rootEl) {
            form = rootEl.querySelector('form[data-immo-lease-form]');
            if (!form) {
                return;
            }
            overlapContainer = rootEl.querySelector('[data-immo-lease-overlap]');
            var unitEl = form.querySelector('[name="unitId"]');
            var startEl = form.querySelector('[name="startDate"]');
            var endEl = form.querySelector('[name="endDate"]');

            [unitEl, startEl, endEl].forEach(function (el) {
                if (el) {
                    el.addEventListener('change', debounce(checkOverlap, 300));
                }
            });
        }

        function checkOverlap() {
            if (!form) {
                return;
            }
            var unitId = form.querySelector('[name="unitId"]').value;
            var startDate = form.querySelector('[name="startDate"]').value;
            var endDate = form.querySelector('[name="endDate"]').value;

            if (!unitId || !startDate) {
                return;
            }

            var payload = {
                unitId: unitId,
                startDate: startDate,
                endDate: endDate || null,
            };

            // OCS Endpoint
            var url = OC.linkToOCS('apps/immo/api/v1/leases/validateOverlap', 2);
            fetch(url, {
                method: 'POST',
                headers: {
                    'requesttoken': OC.requestToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (json) {
                    var data = json.ocs && json.ocs.data ? json.ocs.data : json;
                    renderOverlap(data);
                })
                .catch(function (error) {
                    console.error(error);
                });
        }

        function renderOverlap(data) {
            if (!overlapContainer) {
                return;
            }
            overlapContainer.innerHTML = '';
            if (!data.hasOverlap) {
                return;
            }

            var title = document.createElement('div');
            title.className = 'immo-warning';
            title.textContent = t('immo', 'The selected period overlaps with existing leases');
            overlapContainer.appendChild(title);

            var list = document.createElement('ul');
            data.conflicts.forEach(function (lease) {
                var li = document.createElement('li');
                li.textContent = lease.tenantName + ' (' + lease.startDate + ' – ' + (lease.endDate || t('immo', 'open ended')) + ')';
                list.appendChild(li);
            });
            overlapContainer.appendChild(list);
        }

        function debounce(fn, delay) {
            var timeout;
            return function () {
                var args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    fn.apply(null, args);
                }, delay);
            };
        }

        return {
            init: init,
        };
    }());

    // File Picker Modul
    OCA.Immo.Components.FileLinker = (function () {
        function init(rootEl) {
            var button = rootEl.querySelector('[data-immo-filepicker-button]');
            if (!button) {
                return;
            }
            var input = rootEl.querySelector('[data-immo-filepicker-input]');
            var label = rootEl.querySelector('[data-immo-filepicker-label]');

            button.addEventListener('click', function () {
                OC.dialogs.filepicker(
                    t('immo', 'Select file'),
                    function (filePath) {
                        input.value = filePath;
                        if (label) {
                            label.textContent = filePath;
                        }
                    },
                    false,
                    'file',
                    true
                );
            });
        }

        return {
            init: init,
        };
    }());

    // Bootstrap
    OCA.Immo.App = (function () {
        function init() {
            var appRoot = document.getElementById('immo-app');
            if (!appRoot) {
                return;
            }
            var view = appRoot.getAttribute('data-immo-view');

            switch (view) {
                case 'admin-dashboard':
                    OCA.Immo.Components.Admin.Dashboard.init(appRoot);
                    break;
                case 'admin-lease-form':
                    OCA.Immo.Components.Admin.LeaseForm.init(appRoot);
                    OCA.Immo.Components.FileLinker.init(appRoot);
                    break;
                case 'tenant-dashboard':
                    // eigenes Modul für Mieter
                    if (OCA.Immo.Components.Tenant.Dashboard) {
                        OCA.Immo.Components.Tenant.Dashboard.init(appRoot);
                    }
                    break;
                default:
                    break;
            }
        }

        document.addEventListener('DOMContentLoaded', init);

        return {
            init: init,
        };
    }());
}());
```

### **6. Lease Formular Template (Auszug)**

```
<?php
script('immo', 'immo-main');
?>

<div id="immo-app" data-immo-view="admin-lease-form">
    <h2><?php p($l->t('Lease')); ?></h2>

    <form method="post" action="<?php p($_['formAction']); ?>" data-immo-lease-form>
        <?php print_unescaped($_['requesttoken']); ?>

        <label>
            <?php p($l->t('Unit')); ?>
            <select name="unitId" required>
                <?php foreach ($_['units'] as $unit): ?>
                    <option value="<?php p($unit->getId()); ?>">
                        <?php p($unit->getLabel()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <?php p($l->t('Tenant')); ?>
            <select name="tenantId" required>
                <?php foreach ($_['tenants'] as $tenant): ?>
                    <option value="<?php p($tenant->getId()); ?>">
                        <?php p($tenant->getName()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <?php p($l->t('Start date')); ?>
            <input type="date" name="startDate" required>
        </label>

        <label>
            <?php p($l->t('End date')); ?>
            <input type="date" name="endDate">
        </label>

        <label>
            <?php p($l->t('Base rent')); ?>
            <input type="number" name="baseRent" step="0.01" min="0" required>
        </label>

        <label>
            <?php p($l->t('Service charge')); ?>
            <input type="number" name="serviceCharge" step="0.01" min="0" required>
        </label>

        <div data-immo-lease-overlap></div>

        <div class="immo-filepicker" data-immo-filepicker>
            <input type="hidden" name="contractFilePath" data-immo-filepicker-input>
            <button type="button" data-immo-filepicker-button>
                <?php p($l->t('Link contract document')); ?>
            </button>
            <span data-immo-filepicker-label><?php p($l->t('No document selected')); ?></span>
        </div>

        <button type="submit"><?php p($l->t('Save')); ?></button>
    </form>
</div>
```

Mit diesem Plan hast du:

- klare JS-Namespaces
- Zuordnung Admin/Mieter Komponenten
- saubere Trennung HTML-View und JSON-API
- Beispiele für Dashboard, Formularlogik, File Picker und Übersetzungen