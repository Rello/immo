<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0001Date20250101 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('immo_properties')) {
            $table = $schema->createTable('immo_properties');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => true]);
            $table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('street', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('zip', 'string', ['length' => 32, 'notnull' => false]);
            $table->addColumn('city', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('country', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('type', 'string', ['length' => 64, 'notnull' => false]);
            $table->addColumn('notes', 'text', ['notnull' => false]);
            $table->addColumn('created_at', 'integer', ['notnull' => true, 'unsigned' => true]);
            $table->addColumn('updated_at', 'integer', ['notnull' => true, 'unsigned' => true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['owner_uid'], 'immo_prop_owner_idx');
        }

        if (!$schema->hasTable('immo_units')) {
            $table = $schema->createTable('immo_units');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('property_id', 'integer', ['notnull' => true]);
            $table->addColumn('label', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('unit_number', 'string', ['length' => 128, 'notnull' => false]);
            $table->addColumn('land_register', 'string', ['length' => 128, 'notnull' => false]);
            $table->addColumn('living_area', 'float', ['notnull' => false]);
            $table->addColumn('usable_area', 'float', ['notnull' => false]);
            $table->addColumn('type', 'string', ['length' => 64, 'notnull' => false]);
            $table->addColumn('notes', 'text', ['notnull' => false]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['property_id'], 'immo_unit_property_idx');
        }

        if (!$schema->hasTable('immo_tenants')) {
            $table = $schema->createTable('immo_tenants');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => true]);
            $table->addColumn('nc_user_id', 'string', ['length' => 64, 'notnull' => false]);
            $table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('address', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('email', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('phone', 'string', ['length' => 64, 'notnull' => false]);
            $table->addColumn('customer_ref', 'string', ['length' => 128, 'notnull' => false]);
            $table->addColumn('notes', 'text', ['notnull' => false]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['owner_uid'], 'immo_tenant_owner_idx');
        }

        if (!$schema->hasTable('immo_tenancies')) {
            $table = $schema->createTable('immo_tenancies');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('property_id', 'integer', ['notnull' => true]);
            $table->addColumn('unit_id', 'integer', ['notnull' => true]);
            $table->addColumn('tenant_id', 'integer', ['notnull' => true]);
            $table->addColumn('start_date', 'string', ['length' => 32, 'notnull' => true]);
            $table->addColumn('end_date', 'string', ['length' => 32, 'notnull' => false]);
            $table->addColumn('rent_cold', 'float', ['notnull' => true]);
            $table->addColumn('service_charge', 'float', ['notnull' => false]);
            $table->addColumn('service_charge_is_prepayment', 'boolean', ['default' => false, 'notnull' => true]);
            $table->addColumn('deposit', 'float', ['notnull' => false]);
            $table->addColumn('conditions', 'text', ['notnull' => false]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['property_id'], 'immo_tenancy_property_idx');
        }

        if (!$schema->hasTable('immo_transactions')) {
            $table = $schema->createTable('immo_transactions');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => true]);
            $table->addColumn('property_id', 'integer', ['notnull' => true]);
            $table->addColumn('unit_id', 'integer', ['notnull' => false]);
            $table->addColumn('tenancy_id', 'integer', ['notnull' => false]);
            $table->addColumn('type', 'string', ['length' => 16, 'notnull' => true]);
            $table->addColumn('category', 'string', ['length' => 128, 'notnull' => false]);
            $table->addColumn('date', 'string', ['length' => 32, 'notnull' => true]);
            $table->addColumn('amount', 'float', ['notnull' => true]);
            $table->addColumn('description', 'text', ['notnull' => false]);
            $table->addColumn('year', 'integer', ['notnull' => true]);
            $table->addColumn('is_annual', 'boolean', ['default' => false, 'notnull' => true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['owner_uid'], 'immo_tx_owner_idx');
            $table->addIndex(['property_id'], 'immo_tx_property_idx');
            $table->addIndex(['year'], 'immo_tx_year_idx');
        }

        if (!$schema->hasTable('immo_doc_links')) {
            $table = $schema->createTable('immo_doc_links');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => true]);
            $table->addColumn('entity_type', 'string', ['length' => 64, 'notnull' => true]);
            $table->addColumn('entity_id', 'integer', ['notnull' => true]);
            $table->addColumn('file_id', 'integer', ['notnull' => true]);
            $table->addColumn('path', 'string', ['length' => 512, 'notnull' => true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['owner_uid'], 'immo_doc_owner_idx');
        }

        if (!$schema->hasTable('immo_reports')) {
            $table = $schema->createTable('immo_reports');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => true]);
            $table->addColumn('property_id', 'integer', ['notnull' => true]);
            $table->addColumn('tenancy_id', 'integer', ['notnull' => false]);
            $table->addColumn('tenant_id', 'integer', ['notnull' => false]);
            $table->addColumn('year', 'integer', ['notnull' => true]);
            $table->addColumn('file_id', 'integer', ['notnull' => true]);
            $table->addColumn('path', 'string', ['length' => 512, 'notnull' => true]);
            $table->addColumn('created_at', 'integer', ['notnull' => true, 'unsigned' => true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['owner_uid'], 'immo_report_owner_idx');
        }

        if (!$schema->hasTable('immo_annual_distribution')) {
            $table = $schema->createTable('immo_annual_distribution');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('transaction_id', 'integer', ['notnull' => true]);
            $table->addColumn('tenancy_id', 'integer', ['notnull' => true]);
            $table->addColumn('year', 'integer', ['notnull' => true]);
            $table->addColumn('months', 'integer', ['notnull' => true]);
            $table->addColumn('allocated_amount', 'float', ['notnull' => true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['transaction_id'], 'immo_distribution_tx_idx');
        }

        return $schema;
    }
}
