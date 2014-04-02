<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140331160134 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE TABLE follower (user_id INT NOT NULL, follower_user_id INT NOT NULL, PRIMARY KEY(user_id, follower_user_id))");
        $this->addSql("CREATE INDEX IDX_B9D60946A76ED395 ON follower (user_id)");
        $this->addSql("CREATE INDEX IDX_B9D6094670FC2906 ON follower (follower_user_id)");
        $this->addSql("ALTER TABLE follower ADD CONSTRAINT FK_B9D60946A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE follower ADD CONSTRAINT FK_B9D6094670FC2906 FOREIGN KEY (follower_user_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("DROP TABLE follower");
    }
}
