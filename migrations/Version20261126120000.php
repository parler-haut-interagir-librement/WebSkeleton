<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;
use Phil\PrivacyBundle\Infrastructure\Database\PrivacyTables;

/**
 * The tables of phil/privacy-bundle whose features the site leaves off: the requests without an account, the consent to
 * tracked emails and the erasure workflow. From 0.4, the bundle maps only the tables of the features in use, and
 * `doctrine:schema:validate` asks for the others to go. A site that turns one of these features on writes the
 * migration that brings its table back.
 */
final class Version20261126120000 extends AbstractMigration
{
    /**
     * The tables of the features that are off, in the order they are dropped.
     */
    private const array UNUSED = [
        PrivacyTables::ERASURE_STEPS,
        PrivacyTables::ERASURE_REQUESTS,
        PrivacyTables::EMAIL_TRACKING,
        PrivacyTables::GUEST_REQUESTS,
    ];

    #[Override]
    public function getDescription(): string
    {
        return 'The tables of the privacy bundle whose features are off';
    }

    public function up(Schema $schema): void
    {
        foreach (self::UNUSED as $table) {
            $this->addSql($this->platform->getDropTableSQL($table));
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $difference = $this->connection->createSchemaManager()->createComparator()->compareSchemas(
            Schema::editor()->create(),
            PrivacyTables::configure(Schema::editor()->create(), $this->platform, array_reverse(self::UNUSED)),
        );

        foreach ($this->platform->getAlterSchemaSQL($difference) as $sql) {
            $this->addSql($sql);
        }
    }
}
