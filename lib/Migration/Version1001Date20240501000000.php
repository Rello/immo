<?php
namespace OCA\Immo\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1001Date20240501000000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('immo_prop')) {
            $table = $schema->createTable('immo_prop');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('uid_owner', 'string', ['length' => 64]);
            $table->addColumn('name', 'string', ['length' => 120]);
            $table->addColumn('street', 'string', ['length' => 120, 'notnull' => false]);
            $table->addColumn('zip', 'string', ['length' => 20, 'notnull' => false]);
            $table->addColumn('city', 'string', ['length' => 120, 'notnull' => false]);
            $table->addColumn('country', 'string', ['length' => 120, 'notnull' => false]);
            $table->addColumn('type', 'string', ['length' => 60, 'notnull' => false]);
            $table->addColumn('note', 'text', ['notnull' => false]);
            $table->addColumn('created_at', 'integer');
            $table->addColumn('updated_at', 'integer');
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('immo_unit')) {
            $table = $schema->createTable('immo_unit');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('prop_id', 'integer');
            $table->addColumn('label', 'string', ['length' => 120]);
            $table->addColumn('loc', 'string', ['length' => 60, 'notnull' => false]);
            $table->addColumn('gbook', 'string', ['length' => 120, 'notnull' => false]);
            $table->addColumn('area_res', 'float', ['notnull' => false]);
            $table->addColumn('area_use', 'float', ['notnull' => false]);
            $table->addColumn('type', 'string', ['length' => 60, 'notnull' => false]);
            $table->addColumn('note', 'text', ['notnull' => false]);
            $table->addColumn('created_at', 'integer');
            $table->addColumn('updated_at', 'integer');
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('immo_tenant')) {
            $table = $schema->createTable('immo_tenant');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('uid_owner', 'string', ['length' => 64]);
            $table->addColumn('uid_user', 'string', ['length' => 64, 'notnull' => false]);
            $table->addColumn('name', 'string', ['length' => 120]);
            $table->addColumn('addr', 'text', ['notnull' => false]);
            $table->addColumn('email', 'string', ['length' => 120, 'notnull' => false]);
            $table->addColumn('phone', 'string', ['length' => 60, 'notnull' => false]);
            $table->addColumn('cust_no', 'string', ['length' => 60, 'notnull' => false]);
            $table->addColumn('note', 'text', ['notnull' => false]);
            $table->addColumn('created_at', 'integer');
            $table->addColumn('updated_at', 'integer');
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('immo_lease')) {
            $table = $schema->createTable('immo_lease');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('unit_id', 'integer');
            $table->addColumn('tenant_id', 'integer');
            $table->addColumn('start', 'string', ['length' => 20]);
            $table->addColumn('end', 'string', ['length' => 20, 'notnull' => false]);
            $table->addColumn('rent_cold', 'string', ['length' => 20]);
            $table->addColumn('costs', 'string', ['length' => 20, 'notnull' => false]);
            $table->addColumn('costs_type', 'string', ['length' => 20, 'notnull' => false]);
            $table->addColumn('deposit', 'string', ['length' => 20, 'notnull' => false]);
            $table->addColumn('cond', 'text', ['notnull' => false]);
            $table->addColumn('status', 'string', ['length' => 20]);
            $table->addColumn('created_at', 'integer');
            $table->addColumn('updated_at', 'integer');
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('immo_book')) {
            $table = $schema->createTable('immo_book');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('type', 'string', ['length' => 4]);
            $table->addColumn('cat', 'string', ['length' => 60]);
            $table->addColumn('date', 'string', ['length' => 20]);
            $table->addColumn('amt', 'string', ['length' => 20]);
            $table->addColumn('desc', 'text', ['notnull' => false]);
            $table->addColumn('prop_id', 'integer');
            $table->addColumn('unit_id', 'integer', ['notnull' => false]);
            $table->addColumn('lease_id', 'integer', ['notnull' => false]);
            $table->addColumn('year', 'integer');
            $table->addColumn('is_yearly', 'boolean', ['notnull' => false]);
            $table->addColumn('created_at', 'integer');
            $table->addColumn('updated_at', 'integer');
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('immo_filelink')) {
            $table = $schema->createTable('immo_filelink');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('obj_type', 'string', ['length' => 20]);
            $table->addColumn('obj_id', 'integer');
            $table->addColumn('file_id', 'integer');
            $table->addColumn('path', 'string', ['length' => 255]);
            $table->addColumn('created_at', 'integer');
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('immo_report')) {
            $table = $schema->createTable('immo_report');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('prop_id', 'integer');
            $table->addColumn('year', 'integer');
            $table->addColumn('file_id', 'integer');
            $table->addColumn('path', 'string', ['length' => 255]);
            $table->addColumn('created_at', 'integer');
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('immo_role')) {
            $table = $schema->createTable('immo_role');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('uid', 'string', ['length' => 64]);
            $table->addColumn('role', 'string', ['length' => 20]);
            $table->addColumn('created_at', 'integer');
            $table->setPrimaryKey(['id']);
        }

        return $schema;
    }
}
