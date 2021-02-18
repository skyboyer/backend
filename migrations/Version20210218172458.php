<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210218172458 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_63C9AEC3217BBB47');
        $this->addSql('DROP INDEX fk_person_like_product_product1_idx');
        $this->addSql('CREATE TEMPORARY TABLE __temp__person_like_product AS SELECT person_id, product_id FROM person_like_product');
        $this->addSql('DROP TABLE person_like_product');
        $this->addSql('CREATE TABLE person_like_product (person_id INTEGER UNSIGNED NOT NULL, product_id INTEGER UNSIGNED NOT NULL, PRIMARY KEY(person_id, product_id), CONSTRAINT FK_63C9AEC3217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_63C9AEC34584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO person_like_product (person_id, product_id) SELECT person_id, product_id FROM __temp__person_like_product');
        $this->addSql('DROP TABLE __temp__person_like_product');
        $this->addSql('CREATE INDEX IDX_63C9AEC3217BBB47 ON person_like_product (person_id)');
        $this->addSql('CREATE INDEX fk_person_like_product_product1_idx ON person_like_product (product_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_63C9AEC3217BBB47');
        $this->addSql('DROP INDEX fk_person_like_product_product1_idx');
        $this->addSql('CREATE TEMPORARY TABLE __temp__person_like_product AS SELECT person_id, product_id FROM person_like_product');
        $this->addSql('DROP TABLE person_like_product');
        $this->addSql('CREATE TABLE person_like_product (person_id INTEGER UNSIGNED NOT NULL, product_id INTEGER UNSIGNED NOT NULL, PRIMARY KEY(person_id, product_id))');
        $this->addSql('INSERT INTO person_like_product (person_id, product_id) SELECT person_id, product_id FROM __temp__person_like_product');
        $this->addSql('DROP TABLE __temp__person_like_product');
        $this->addSql('CREATE INDEX IDX_63C9AEC3217BBB47 ON person_like_product (person_id)');
        $this->addSql('CREATE INDEX fk_person_like_product_product1_idx ON person_like_product (product_id)');
    }
}
