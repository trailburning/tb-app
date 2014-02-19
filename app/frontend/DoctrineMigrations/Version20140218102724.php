<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140218102724 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql('UPDATE fos_user SET first_name="name" WHERE first_name IS NULL');
        $this->addSql('UPDATE fos_user SET last_name="name" WHERE last_name IS NULL');
        $this->addSql("UPDATE fos_user SET location='0101000020E6100000FD15325706414A408A8F4FC8CE832A40' WHERE location IS NULL");
        
        $this->addSql("ALTER TABLE fos_user ALTER first_name SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER last_name SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER location SET NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE fos_user ALTER first_name DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER last_name DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER location DROP NOT NULL");
    }
}
