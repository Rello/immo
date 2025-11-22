<?php
namespace OCA\Immo\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1002Date20251122000000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('immo_alloc')) {
            $table = $schema->createTable('immo_alloc');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('transaction_id', 'integer');
            $table->addColumn('lease_id', 'integer');
            $table->addColumn('year', 'integer');
            $table->addColumn('month', 'integer', ['notnull' => false]);
            $table->addColumn('amt', 'string', ['length' => 20]);
            $table->addColumn('created_at', 'integer');
            $table->setPrimaryKey(['id']);
        }

        return $schema;
    }
}
