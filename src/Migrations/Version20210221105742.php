<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210221105742 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag_translated_tag (tag_id INT NOT NULL, translated_tag_id INT NOT NULL, INDEX IDX_2F6D0CECBAD26311 (tag_id), INDEX IDX_2F6D0CECE250A646 (translated_tag_id), PRIMARY KEY(tag_id, translated_tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE translated_tag (id INT AUTO_INCREMENT NOT NULL, language VARCHAR(2) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tag_translated_tag ADD CONSTRAINT FK_2F6D0CECBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_translated_tag ADD CONSTRAINT FK_2F6D0CECE250A646 FOREIGN KEY (translated_tag_id) REFERENCES translated_tag (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE IF EXISTS fos_user');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag_translated_tag DROP FOREIGN KEY FK_2F6D0CECE250A646');
        $this->addSql('DROP TABLE tag_translated_tag');
        $this->addSql('DROP TABLE translated_tag');
    }

	public function isTransactional(): bool
	{
		return false;
	}
}
