<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Index\IndexType;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Override;
use Phil\ErrorTrackingBundle\Infrastructure\Doctrine\ErrorTables;
use Phil\PrivacyBundle\Infrastructure\Database\PrivacyTables;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * The accounts, the tables of phil/privacy-bundle and those of phil/error-tracking-bundle. Each table is described
 * through the schema API and written in the SQL of the platform the migration runs on, so that the same migration runs
 * on SQLite, PostgreSQL and MySQL.
 */
final class Version20261125190000 extends AbstractMigration
{
    private const string USER = 'user';

    #[Override]
    public function getDescription(): string
    {
        return 'The accounts, the tables of the privacy bundle and those of the error tracking bundle';
    }

    public function up(Schema $schema): void
    {
        $difference = $this->connection->createSchemaManager()->createComparator()->compareSchemas(Schema::editor()->create(), $this->tables());

        foreach ($this->platform->getAlterSchemaSQL($difference) as $sql) {
            $this->addSql($sql);
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        foreach (array_reverse($this->tables()->getTables()) as $table) {
            $this->addSql($this->platform->getDropTableSQL($table->getObjectName()->toSQL($this->platform)));
        }
    }

    /**
     * The tables this migration creates: the accounts, then those of the two bundles.
     */
    private function tables(): Schema
    {
        return ErrorTables::configure(PrivacyTables::configure(Schema::editor()->setTables($this->userTable())->create(), $this->platform));
    }

    private function userTable(): Table
    {
        return Table::editor()
            ->setQuotedName(self::USER)
            ->setColumns(
                Column::editor()->setUnquotedName('id')->setTypeName(UuidType::NAME)->create(),
                Column::editor()->setUnquotedName('username')->setTypeName(Types::STRING)->setLength(180)->create(),
                Column::editor()->setUnquotedName('email')->setTypeName(Types::STRING)->setLength(255)->create(),
                Column::editor()->setUnquotedName('password')->setTypeName(Types::STRING)->setLength(255)->create(),
                Column::editor()->setUnquotedName('roles')->setTypeName(Types::JSON)->create(),
                Column::editor()->setUnquotedName('created_at')->setTypeName(Types::DATETIME_UTC_IMMUTABLE)->create(),
                Column::editor()->setUnquotedName('updated_at')->setTypeName(Types::DATETIME_UTC_IMMUTABLE)->create(),
            )
            ->setPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create())
            ->setIndexes(
                Index::editor()->setUnquotedName('UNIQ_8D93D649F85E0677')->setType(IndexType::UNIQUE)->setUnquotedColumnNames('username')->create(),
                Index::editor()->setUnquotedName('UNIQ_8D93D649E7927C74')->setType(IndexType::UNIQUE)->setUnquotedColumnNames('email')->create(),
            )
            ->create();
    }
}
