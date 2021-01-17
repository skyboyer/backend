<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210117153550 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE person (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, login VARCHAR(10) NOT NULL, i_name VARCHAR(100) NOT NULL, f_name VARCHAR(100) NOT NULL, state SMALLINT UNSIGNED NOT NULL)');
        $this->addSql('CREATE TABLE person_like_product (person_id INTEGER UNSIGNED NOT NULL, product_id INTEGER UNSIGNED NOT NULL, PRIMARY KEY(person_id, product_id))');
        ;
        $this->addSql('CREATE INDEX fk_person_like_product_product1_idx ON person_like_product (product_id ASC)');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, info CLOB DEFAULT NULL, public_date DATE NOT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE person_like_product');
        $this->addSql('DROP TABLE product');
    }
}
