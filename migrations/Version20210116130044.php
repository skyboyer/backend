<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210116130044 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE person_like_product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, person_id INTEGER NOT NULL, product_id INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_63C9AEC3217BBB47 ON person_like_product (person_id)');
        $this->addSql('CREATE INDEX IDX_63C9AEC34584665A ON person_like_product (product_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE person_like_product');
    }
}
