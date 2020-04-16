<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use CapCollectif\IdToUuid\IdToUuidMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200416103458 extends IdToUuidMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function postUp(Schema $schema) : void
    {
        $this->migrate('account');
        $this->migrate('sub_category_transaction_rule');
    }
}
