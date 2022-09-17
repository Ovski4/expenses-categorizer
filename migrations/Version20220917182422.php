<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220917182422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_transaction (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', transaction_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_6EFE7F31BAD26311 (tag_id), INDEX IDX_6EFE7F312FC0CB0F (transaction_id), PRIMARY KEY(tag_id, transaction_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tag_transaction ADD CONSTRAINT FK_6EFE7F31BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_transaction ADD CONSTRAINT FK_6EFE7F312FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sub_category_transaction_rule DROP FOREIGN KEY FK_2761B910F7BFE87C');
        $this->addSql('ALTER TABLE sub_category_transaction_rule ADD CONSTRAINT FK_2761B910F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F7BFE87C');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag_transaction DROP FOREIGN KEY FK_6EFE7F31BAD26311');
        $this->addSql('ALTER TABLE tag_transaction DROP FOREIGN KEY FK_6EFE7F312FC0CB0F');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_transaction');
        $this->addSql('ALTER TABLE sub_category_transaction_rule DROP FOREIGN KEY FK_2761B910F7BFE87C');
        $this->addSql('ALTER TABLE sub_category_transaction_rule ADD CONSTRAINT FK_2761B910F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F7BFE87C');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
    }
}
