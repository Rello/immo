<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        ['name' => 'view#dashboard', 'url' => '/view/dashboard', 'verb' => 'GET'],
        ['name' => 'view#props', 'url' => '/view/props', 'verb' => 'GET'],
        ['name' => 'view#propDetail', 'url' => '/view/prop/{id}', 'verb' => 'GET'],
        ['name' => 'view#units', 'url' => '/view/units', 'verb' => 'GET'],
        ['name' => 'view#unitDetail', 'url' => '/view/unit/{id}', 'verb' => 'GET'],
        ['name' => 'view#tenants', 'url' => '/view/tenants', 'verb' => 'GET'],
        ['name' => 'view#tenantDetail', 'url' => '/view/tenant/{id}', 'verb' => 'GET'],
        ['name' => 'view#leases', 'url' => '/view/leases', 'verb' => 'GET'],
        ['name' => 'view#leaseDetail', 'url' => '/view/lease/{id}', 'verb' => 'GET'],
        ['name' => 'view#books', 'url' => '/view/books', 'verb' => 'GET'],
        ['name' => 'view#reports', 'url' => '/view/reports', 'verb' => 'GET'],

        ['name' => 'property#index', 'url' => '/api/prop', 'verb' => 'GET'],
        ['name' => 'property#show', 'url' => '/api/prop/{id}', 'verb' => 'GET'],
        ['name' => 'property#create', 'url' => '/api/prop', 'verb' => 'POST'],
        ['name' => 'property#update', 'url' => '/api/prop/{id}', 'verb' => 'PUT'],
        ['name' => 'property#destroy', 'url' => '/api/prop/{id}', 'verb' => 'DELETE'],

        ['name' => 'unit#index', 'url' => '/api/unit', 'verb' => 'GET'],
        ['name' => 'unit#show', 'url' => '/api/unit/{id}', 'verb' => 'GET'],
        ['name' => 'unit#create', 'url' => '/api/unit', 'verb' => 'POST'],
        ['name' => 'unit#update', 'url' => '/api/unit/{id}', 'verb' => 'PUT'],
        ['name' => 'unit#destroy', 'url' => '/api/unit/{id}', 'verb' => 'DELETE'],

        ['name' => 'tenant#index', 'url' => '/api/tenant', 'verb' => 'GET'],
        ['name' => 'tenant#show', 'url' => '/api/tenant/{id}', 'verb' => 'GET'],
        ['name' => 'tenant#create', 'url' => '/api/tenant', 'verb' => 'POST'],
        ['name' => 'tenant#update', 'url' => '/api/tenant/{id}', 'verb' => 'PUT'],
        ['name' => 'tenant#destroy', 'url' => '/api/tenant/{id}', 'verb' => 'DELETE'],

        ['name' => 'lease#index', 'url' => '/api/lease', 'verb' => 'GET'],
        ['name' => 'lease#show', 'url' => '/api/lease/{id}', 'verb' => 'GET'],
        ['name' => 'lease#create', 'url' => '/api/lease', 'verb' => 'POST'],
        ['name' => 'lease#update', 'url' => '/api/lease/{id}', 'verb' => 'PUT'],
        ['name' => 'lease#destroy', 'url' => '/api/lease/{id}', 'verb' => 'DELETE'],

        ['name' => 'booking#index', 'url' => '/api/book', 'verb' => 'GET'],
        ['name' => 'booking#show', 'url' => '/api/book/{id}', 'verb' => 'GET'],
        ['name' => 'booking#create', 'url' => '/api/book', 'verb' => 'POST'],
        ['name' => 'booking#update', 'url' => '/api/book/{id}', 'verb' => 'PUT'],
        ['name' => 'booking#destroy', 'url' => '/api/book/{id}', 'verb' => 'DELETE'],

        ['name' => 'filelink#index', 'url' => '/api/filelink', 'verb' => 'GET'],
        ['name' => 'filelink#create', 'url' => '/api/filelink', 'verb' => 'POST'],
        ['name' => 'filelink#destroy', 'url' => '/api/filelink/{id}', 'verb' => 'DELETE'],

        ['name' => 'report#index', 'url' => '/api/report', 'verb' => 'GET'],
        ['name' => 'report#show', 'url' => '/api/report/{id}', 'verb' => 'GET'],
        ['name' => 'report#create', 'url' => '/api/report', 'verb' => 'POST'],
        ['name' => 'report#destroy', 'url' => '/api/report/{id}', 'verb' => 'DELETE'],

        ['name' => 'dashboard#stats', 'url' => '/api/dashboard', 'verb' => 'GET'],
        ['name' => 'report#distribution', 'url' => '/api/stats/distribution', 'verb' => 'GET'],
    ],
];
