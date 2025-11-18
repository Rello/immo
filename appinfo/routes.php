<?php

return [
    'routes' => [
        ['name' => 'view#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'user#me', 'url' => '/api/me', 'verb' => 'GET'],
        ['name' => 'dashboard#stats', 'url' => '/api/dashboard/stats', 'verb' => 'GET'],

        ['name' => 'property#index', 'url' => '/api/properties', 'verb' => 'GET'],
        ['name' => 'property#show', 'url' => '/api/properties/{id}', 'verb' => 'GET'],
        ['name' => 'property#create', 'url' => '/api/properties', 'verb' => 'POST'],
        ['name' => 'property#update', 'url' => '/api/properties/{id}', 'verb' => 'PUT'],
        ['name' => 'property#destroy', 'url' => '/api/properties/{id}', 'verb' => 'DELETE'],

        ['name' => 'unit#index', 'url' => '/api/units', 'verb' => 'GET'],
        ['name' => 'unit#show', 'url' => '/api/units/{id}', 'verb' => 'GET'],
        ['name' => 'unit#create', 'url' => '/api/units', 'verb' => 'POST'],
        ['name' => 'unit#update', 'url' => '/api/units/{id}', 'verb' => 'PUT'],
        ['name' => 'unit#destroy', 'url' => '/api/units/{id}', 'verb' => 'DELETE'],

        ['name' => 'tenant#index', 'url' => '/api/tenants', 'verb' => 'GET'],
        ['name' => 'tenant#show', 'url' => '/api/tenants/{id}', 'verb' => 'GET'],
        ['name' => 'tenant#create', 'url' => '/api/tenants', 'verb' => 'POST'],
        ['name' => 'tenant#update', 'url' => '/api/tenants/{id}', 'verb' => 'PUT'],
        ['name' => 'tenant#destroy', 'url' => '/api/tenants/{id}', 'verb' => 'DELETE'],

        ['name' => 'tenancy#index', 'url' => '/api/tenancies', 'verb' => 'GET'],
        ['name' => 'tenancy#show', 'url' => '/api/tenancies/{id}', 'verb' => 'GET'],
        ['name' => 'tenancy#create', 'url' => '/api/tenancies', 'verb' => 'POST'],
        ['name' => 'tenancy#update', 'url' => '/api/tenancies/{id}', 'verb' => 'PUT'],
        ['name' => 'tenancy#destroy', 'url' => '/api/tenancies/{id}', 'verb' => 'DELETE'],

        ['name' => 'transaction#index', 'url' => '/api/transactions', 'verb' => 'GET'],
        ['name' => 'transaction#show', 'url' => '/api/transactions/{id}', 'verb' => 'GET'],
        ['name' => 'transaction#create', 'url' => '/api/transactions', 'verb' => 'POST'],
        ['name' => 'transaction#update', 'url' => '/api/transactions/{id}', 'verb' => 'PUT'],
        ['name' => 'transaction#destroy', 'url' => '/api/transactions/{id}', 'verb' => 'DELETE'],

        ['name' => 'doc_link#index', 'url' => '/api/doc-links/{entityType}/{entityId}', 'verb' => 'GET'],
        ['name' => 'doc_link#create', 'url' => '/api/doc-links', 'verb' => 'POST'],
        ['name' => 'doc_link#destroy', 'url' => '/api/doc-links/{id}', 'verb' => 'DELETE'],

        ['name' => 'accounting#index', 'url' => '/api/reports', 'verb' => 'GET'],
        ['name' => 'accounting#createReport', 'url' => '/api/reports', 'verb' => 'POST'],
        ['name' => 'accounting#show', 'url' => '/api/reports/{id}', 'verb' => 'GET'],
    ],
];
