<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240331074430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_settings CHANGE google_drive_token google_drive_token VARCHAR(255) DEFAULT NULL, CHANGE google_drive_folder_id google_drive_folder_id VARCHAR(255) DEFAULT NULL, CHANGE instagram_token instagram_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_settings CHANGE google_drive_token google_drive_token VARCHAR(255) NOT NULL, CHANGE google_drive_folder_id google_drive_folder_id VARCHAR(255) NOT NULL, CHANGE instagram_token instagram_token VARCHAR(255) NOT NULL');
    }
}
