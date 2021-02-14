<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210214134632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pack ADD views INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE picture ADD views INT UNSIGNED NOT NULL, ADD thumbnail VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16DB4F89C35726E6 ON picture (thumbnail)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pack DROP views');
        $this->addSql('DROP INDEX UNIQ_16DB4F89C35726E6 ON picture');
        $this->addSql('ALTER TABLE picture DROP views, DROP thumbnail');
    }
}
