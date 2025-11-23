<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        ['name' => 'view#dashboard', 'url' => '/view/dashboard', 'verb' => 'GET'],
        ['name' => 'view#propertyList', 'url' => '/view/property-list', 'verb' => 'GET'],
        ['name' => 'view#unitList', 'url' => '/view/unit-list', 'verb' => 'GET'],
        ['name' => 'view#tenantList', 'url' => '/view/tenant-list', 'verb' => 'GET'],
        ['name' => 'view#leaseList', 'url' => '/view/lease-list', 'verb' => 'GET'],
        ['name' => 'view#bookingList', 'url' => '/view/booking-list', 'verb' => 'GET'],
        ['name' => 'view#reportList', 'url' => '/view/report-list', 'verb' => 'GET'],

        ['name' => 'property#list', 'url' => '/api/prop', 'verb' => 'GET'],
        ['name' => 'property#get', 'url' => '/api/prop/{id}', 'verb' => 'GET'],
        ['name' => 'property#create', 'url' => '/api/prop', 'verb' => 'POST'],
        ['name' => 'property#update', 'url' => '/api/prop/{id}', 'verb' => 'PUT'],
        ['name' => 'property#delete', 'url' => '/api/prop/{id}', 'verb' => 'DELETE'],

        ['name' => 'unit#list', 'url' => '/api/unit', 'verb' => 'GET'],
        ['name' => 'unit#get', 'url' => '/api/unit/{id}', 'verb' => 'GET'],
        ['name' => 'unit#create', 'url' => '/api/unit', 'verb' => 'POST'],
        ['name' => 'unit#update', 'url' => '/api/unit/{id}', 'verb' => 'PUT'],
        ['name' => 'unit#delete', 'url' => '/api/unit/{id}', 'verb' => 'DELETE'],

        ['name' => 'tenant#list', 'url' => '/api/tenant', 'verb' => 'GET'],
        ['name' => 'tenant#get', 'url' => '/api/tenant/{id}', 'verb' => 'GET'],
        ['name' => 'tenant#create', 'url' => '/api/tenant', 'verb' => 'POST'],
        ['name' => 'tenant#update', 'url' => '/api/tenant/{id}', 'verb' => 'PUT'],
        ['name' => 'tenant#delete', 'url' => '/api/tenant/{id}', 'verb' => 'DELETE'],

        ['name' => 'lease#list', 'url' => '/api/lease', 'verb' => 'GET'],
        ['name' => 'lease#get', 'url' => '/api/lease/{id}', 'verb' => 'GET'],
        ['name' => 'lease#create', 'url' => '/api/lease', 'verb' => 'POST'],
        ['name' => 'lease#update', 'url' => '/api/lease/{id}', 'verb' => 'PUT'],
        ['name' => 'lease#delete', 'url' => '/api/lease/{id}', 'verb' => 'DELETE'],

        ['name' => 'booking#list', 'url' => '/api/booking', 'verb' => 'GET'],
        ['name' => 'booking#get', 'url' => '/api/booking/{id}', 'verb' => 'GET'],
        ['name' => 'booking#create', 'url' => '/api/booking', 'verb' => 'POST'],
        ['name' => 'booking#update', 'url' => '/api/booking/{id}', 'verb' => 'PUT'],
        ['name' => 'booking#delete', 'url' => '/api/booking/{id}', 'verb' => 'DELETE'],

        ['name' => 'report#list', 'url' => '/api/report', 'verb' => 'GET'],
        ['name' => 'report#create', 'url' => '/api/report', 'verb' => 'POST'],

        ['name' => 'fileLink#list', 'url' => '/api/filelink', 'verb' => 'GET'],
        ['name' => 'fileLink#create', 'url' => '/api/filelink', 'verb' => 'POST'],
        ['name' => 'fileLink#delete', 'url' => '/api/filelink/{id}', 'verb' => 'DELETE'],

        ['name' => 'dashboard#metrics', 'url' => '/api/dashboard', 'verb' => 'GET'],
        ['name' => 'stats#yearDistribution', 'url' => '/api/stats/distribution', 'verb' => 'GET'],
    ],
];
