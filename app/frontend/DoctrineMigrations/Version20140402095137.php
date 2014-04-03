<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140402095137 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE TABLE activity (id SERIAL NOT NULL, actor_id INT NOT NULL, published TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, object_id INT NOT NULL, target_id INT DEFAULT NULL, verb VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_AC74095A10DAF24A ON activity (actor_id)");
        $this->addSql("CREATE INDEX IDX_AC74095A232D562B ON activity (object_id)");
        $this->addSql("ALTER TABLE activity ADD CONSTRAINT FK_AC74095A10DAF24A FOREIGN KEY (actor_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("DROP TABLE activity");
    }
}
