<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260412193533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE activity_type ADD points_target VARCHAR(20) NOT NULL DEFAULT 'both'");
        $this->addSql("UPDATE activity_type SET points_target = 'planet' WHERE slug LIKE 'challenge_%'");
        $this->addSql("UPDATE activity_type SET points_target = 'astronaut' WHERE slug = 'seniority'");
        $this->addSql('ALTER TABLE activity_type ALTER points_target DROP DEFAULT');
        $this->addSql('ALTER TABLE activity_type DROP planet_only');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_type ADD planet_only BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE activity_type DROP points_target');
    }
}
