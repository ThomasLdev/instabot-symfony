<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240331074809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64959949888');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64959949888 FOREIGN KEY (settings_id) REFERENCES user_settings (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64959949888');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64959949888 FOREIGN KEY (settings_id) REFERENCES user_settings (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
