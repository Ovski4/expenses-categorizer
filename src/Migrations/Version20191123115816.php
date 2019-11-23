<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191123115816 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE top_category (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, transaction_type VARCHAR(255) NOT NULL, UNIQUE INDEX top_category_unique (name, transaction_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_category (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', top_category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, transaction_type VARCHAR(255) NOT NULL, INDEX IDX_BCE3F798C601200 (top_category_id), UNIQUE INDEX sub_category_unique (name, transaction_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_category_transaction_rule (id INT AUTO_INCREMENT NOT NULL, sub_category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', contains VARCHAR(255) NOT NULL, INDEX IDX_2761B910F7BFE87C (sub_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', sub_category_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', label VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, account VARCHAR(255) NOT NULL, INDEX IDX_723705D1F7BFE87C (sub_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F798C601200 FOREIGN KEY (top_category_id) REFERENCES top_category (id)');
        $this->addSql('ALTER TABLE sub_category_transaction_rule ADD CONSTRAINT FK_2761B910F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sub_category DROP FOREIGN KEY FK_BCE3F798C601200');
        $this->addSql('ALTER TABLE sub_category_transaction_rule DROP FOREIGN KEY FK_2761B910F7BFE87C');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F7BFE87C');
        $this->addSql('DROP TABLE top_category');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('DROP TABLE sub_category_transaction_rule');
        $this->addSql('DROP TABLE transaction');
    }
}
