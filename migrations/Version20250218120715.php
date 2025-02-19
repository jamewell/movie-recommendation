<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218120715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'new table for user favorite genres';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_favorite_genres (user_id INT NOT NULL, genre_id INT NOT NULL, INDEX IDX_8682E563A76ED395 (user_id), INDEX IDX_8682E5634296D31F (genre_id), PRIMARY KEY(user_id, genre_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_favorite_genres ADD CONSTRAINT FK_8682E563A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_genres ADD CONSTRAINT FK_8682E5634296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP favorite_genres');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_favorite_genres DROP FOREIGN KEY FK_8682E563A76ED395');
        $this->addSql('ALTER TABLE user_favorite_genres DROP FOREIGN KEY FK_8682E5634296D31F');
        $this->addSql('DROP TABLE user_favorite_genres');
        $this->addSql('ALTER TABLE user ADD favorite_genres JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
