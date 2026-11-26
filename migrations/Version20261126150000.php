<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;
use Phil\FeatureFlagsBundle\Infrastructure\Doctrine\FlagTables;

/**
 * The tables of phil/feature-flags-bundle: the flags created in the administration and the states of every flag.
 * They are described by the bundle and written in the SQL of the platform the migration runs on, so that the same
 * migration runs on SQLite, PostgreSQL and MySQL.
 */
final class Version20261126150000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'The tables of the feature flags bundle';
    }

    public function up(Schema $schema): void
    {
        $difference = $this->connection->createSchemaManager()->createComparator()->compareSchemas(
            Schema::editor()->create(),
            FlagTables::configure(Schema::editor()->create()),
        );

        foreach ($this->platform->getAlterSchemaSQL($difference) as $sql) {
            $this->addSql($sql);
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        foreach ([FlagTables::STATES, FlagTables::DEFINITIONS] as $table) {
            $this->addSql($this->platform->getDropTableSQL($table));
        }
    }
}
