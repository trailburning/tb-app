<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140213155416 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE fos_user ADD username VARCHAR(255) NULL");
        $this->addSql("ALTER TABLE fos_user ADD username_canonical VARCHAR(255) NULL");
        $this->addSql("ALTER TABLE fos_user ADD email VARCHAR(255) NULL");
        $this->addSql("ALTER TABLE fos_user ADD email_canonical VARCHAR(255) NULL");
        $this->addSql("ALTER TABLE fos_user ADD enabled BOOLEAN NULL");
        $this->addSql("ALTER TABLE fos_user ADD salt VARCHAR(255) NULL");
        $this->addSql("ALTER TABLE fos_user ADD password VARCHAR(255) NULL");
        $this->addSql("ALTER TABLE fos_user ADD last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL");
        $this->addSql("ALTER TABLE fos_user ADD locked BOOLEAN NULL");
        $this->addSql("ALTER TABLE fos_user ADD expired BOOLEAN NULL");
        $this->addSql("ALTER TABLE fos_user ADD expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL");
        $this->addSql("ALTER TABLE fos_user ADD confirmation_token VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE fos_user ADD password_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL");
        $this->addSql("ALTER TABLE fos_user ADD roles TEXT NULL");
        $this->addSql("ALTER TABLE fos_user ADD credentials_expired BOOLEAN NULL");
        $this->addSql("ALTER TABLE fos_user ADD credentials_expire_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_957A647992FC23A8 ON fos_user (username_canonical)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_957A6479A0D96FBF ON fos_user (email_canonical)");
        $this->addSql("COMMENT ON COLUMN fos_user.roles IS '(DC2Type:array)'");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("DROP INDEX UNIQ_957A647992FC23A8");
        $this->addSql("DROP INDEX UNIQ_957A6479A0D96FBF");
        $this->addSql("ALTER TABLE fos_user DROP username");
        $this->addSql("ALTER TABLE fos_user DROP username_canonical");
        $this->addSql("ALTER TABLE fos_user DROP email");
        $this->addSql("ALTER TABLE fos_user DROP email_canonical");
        $this->addSql("ALTER TABLE fos_user DROP enabled");
        $this->addSql("ALTER TABLE fos_user DROP salt");
        $this->addSql("ALTER TABLE fos_user DROP password");
        $this->addSql("ALTER TABLE fos_user DROP last_login");
        $this->addSql("ALTER TABLE fos_user DROP locked");
        $this->addSql("ALTER TABLE fos_user DROP expired");
        $this->addSql("ALTER TABLE fos_user DROP expires_at");
        $this->addSql("ALTER TABLE fos_user DROP confirmation_token");
        $this->addSql("ALTER TABLE fos_user DROP password_requested_at");
        $this->addSql("ALTER TABLE fos_user DROP roles");
        $this->addSql("ALTER TABLE fos_user DROP credentials_expired");
        $this->addSql("ALTER TABLE fos_user DROP credentials_expire_at");
    }
}
