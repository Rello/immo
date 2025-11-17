<?php
return [
    'routes' => [
        // Dashboard
        ['name' => 'dashboard#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'dashboard#index', 'url' => '/dashboard', 'verb' => 'GET'],
        // Properties
        ['name' => 'property#index', 'url' => '/properties', 'verb' => 'GET'],
        ['name' => 'property#show', 'url' => '/properties/{id}', 'verb' => 'GET'],
        ['name' => 'property#create', 'url' => '/properties', 'verb' => 'POST'],
        ['name' => 'property#edit', 'url' => '/properties/{id}/edit', 'verb' => 'GET'],
        ['name' => 'property#update', 'url' => '/properties/{id}', 'verb' => 'POST'],
        ['name' => 'property#delete', 'url' => '/properties/{id}/delete', 'verb' => 'POST'],
        // Units
        ['name' => 'unit#indexByProperty', 'url' => '/properties/{propertyId}/units', 'verb' => 'GET'],
        ['name' => 'unit#show', 'url' => '/units/{id}', 'verb' => 'GET'],
        ['name' => 'unit#create', 'url' => '/units', 'verb' => 'POST'],
        ['name' => 'unit#edit', 'url' => '/units/{id}/edit', 'verb' => 'GET'],
        ['name' => 'unit#update', 'url' => '/units/{id}', 'verb' => 'POST'],
        ['name' => 'unit#delete', 'url' => '/units/{id}/delete', 'verb' => 'POST'],
        // Tenants
        ['name' => 'tenant#index', 'url' => '/tenants', 'verb' => 'GET'],
        ['name' => 'tenant#show', 'url' => '/tenants/{id}', 'verb' => 'GET'],
        ['name' => 'tenant#create', 'url' => '/tenants', 'verb' => 'POST'],
        ['name' => 'tenant#edit', 'url' => '/tenants/{id}/edit', 'verb' => 'GET'],
        ['name' => 'tenant#update', 'url' => '/tenants/{id}', 'verb' => 'POST'],
        ['name' => 'tenant#delete', 'url' => '/tenants/{id}/delete', 'verb' => 'POST'],
        // Leases
        ['name' => 'lease#index', 'url' => '/leases', 'verb' => 'GET'],
        ['name' => 'lease#show', 'url' => '/leases/{id}', 'verb' => 'GET'],
        ['name' => 'lease#create', 'url' => '/leases', 'verb' => 'POST'],
        ['name' => 'lease#edit', 'url' => '/leases/{id}/edit', 'verb' => 'GET'],
        ['name' => 'lease#update', 'url' => '/leases/{id}', 'verb' => 'POST'],
        ['name' => 'lease#terminate', 'url' => '/leases/{id}/terminate', 'verb' => 'POST'],
        // Transactions
        ['name' => 'transaction#index', 'url' => '/transactions', 'verb' => 'GET'],
        ['name' => 'transaction#create', 'url' => '/transactions', 'verb' => 'POST'],
        ['name' => 'transaction#edit', 'url' => '/transactions/{id}/edit', 'verb' => 'GET'],
        ['name' => 'transaction#update', 'url' => '/transactions/{id}', 'verb' => 'POST'],
        ['name' => 'transaction#delete', 'url' => '/transactions/{id}/delete', 'verb' => 'POST'],
        // Statements
        ['name' => 'statement#index', 'url' => '/statements', 'verb' => 'GET'],
        ['name' => 'statement#wizard', 'url' => '/statements/new', 'verb' => 'GET'],
        ['name' => 'statement#generate', 'url' => '/statements/generate', 'verb' => 'POST'],
        ['name' => 'statement#show', 'url' => '/statements/{id}', 'verb' => 'GET'],
    ],
    'ocs' => [
        ['name' => 'api.stats#dashboard', 'url' => '/api/v1/stats/dashboard', 'verb' => 'GET', 'root' => '/ocs/v2.php/apps/immo'],
        ['name' => 'api.leases#validateOverlap', 'url' => '/api/v1/leases/validateOverlap', 'verb' => 'POST', 'root' => '/ocs/v2.php/apps/immo'],
        ['name' => 'api.documents#link', 'url' => '/api/v1/document-links', 'verb' => 'POST', 'root' => '/ocs/v2.php/apps/immo'],
        ['name' => 'api.documents#listByEntity', 'url' => '/api/v1/document-links', 'verb' => 'GET', 'root' => '/ocs/v2.php/apps/immo'],
    ],
];
