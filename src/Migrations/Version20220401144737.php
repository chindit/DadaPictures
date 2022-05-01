<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220401144737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update Tag name and visibility due to new «visible» field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE tag t
            INNER JOIN translated_tag tt ON t.id = tt.tag_id
            SET t.name =
            LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                TRIM(
                    tt.name
                ), ':', ''), ')', ''), '(', ''), ',', ''), '\\', ''), '\/', ''), '\"', ''), '?', ''),
                '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-'));
            SQL
        );
        $this->addSql('UPDATE tag SET visible=1');
    }

    public function down(Schema $schema): void
    {
        // Nothing to do here
    }
}
