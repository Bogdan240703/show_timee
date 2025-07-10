<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708075351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_festival_wishlist (user_id INT NOT NULL, festival_id INT NOT NULL, INDEX IDX_E6354D1A76ED395 (user_id), INDEX IDX_E6354D18AEBAF57 (festival_id), PRIMARY KEY(user_id, festival_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_festival_wishlist ADD CONSTRAINT FK_E6354D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_festival_wishlist ADD CONSTRAINT FK_E6354D18AEBAF57 FOREIGN KEY (festival_id) REFERENCES festival (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_festival_wishlist DROP FOREIGN KEY FK_E6354D1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_festival_wishlist DROP FOREIGN KEY FK_E6354D18AEBAF57
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_festival_wishlist
        SQL);
    }
}
