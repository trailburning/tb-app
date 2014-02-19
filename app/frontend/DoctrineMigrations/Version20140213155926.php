<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140213155926 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
                
        $this->addSql("UPDATE fos_user SET username=name, username_canonical=name, email='email@' || name, email_canonical='email@' || name, enabled=false, salt='null', password='null', locked=false, expired=false, roles='" . serialize(array('')) . "', credentials_expired=false");
                
        $this->addSql("UPDATE fos_user SET email='patrick@trailburning.com', email_canonical=email, enabled=true, salt='null', password='null', locked=false, expired=false, roles='" . serialize(array('ROLE_ADMIN')) . "', credentials_expired=false WHERE name='admin'");
        
        $this->addSql("UPDATE fos_user SET username='matt@trailburning.com', username_canonical='matt@trailburning.com', email='matt@trailburning.com', email_canonical=email, enabled=true, salt='null', password='null', locked=false, expired=false, roles='" . serialize(array('ROLE_USER')) . "', credentials_expired=false WHERE name='mattallbeury'");
        
        $this->addSql("UPDATE fos_user SET username='marianne@trailburning.com', username_canonical='marianne@trailburning.com', email='marianne@trailburning.com', email_canonical=email, enabled=true, salt='null', password='null', locked=false, expired=false, roles='" . serialize(array('ROLE_USER')) . "', credentials_expired=false WHERE name='mariannenicolas'");
        
        $this->addSql("UPDATE fos_user SET username='magdalena@trailburning.com', username_canonical='magdalena@trailburning.com', email='magdalena@trailburning.com', email_canonical=email, enabled=true, salt='null', password='null', locked=false, expired=false, roles='" . serialize(array('ROLE_USER')) . "', credentials_expired=false WHERE name='magdalenazeslawska'");
        
        $this->addSql("UPDATE fos_user SET username='justin@trailburning.com', username_canonical='justin@trailburning.com', email='justin@trailburning.com', email_canonical=email, enabled=true, salt='null', password='null', locked=false, expired=false, roles='" . serialize(array('ROLE_USER')) . "', credentials_expired=false WHERE name='justinwilden'");
        
        $this->addSql("ALTER TABLE fos_user ALTER username SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER username_canonical SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER email SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER email_canonical SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER enabled SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER salt SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER password SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER locked SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER expired SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER roles SET NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER credentials_expired SET NOT NULL");
        
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE fos_user ALTER username DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER username_canonical DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER email DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER email_canonical DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER enabled DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER salt DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER password DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER locked DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER expired DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER roles DROP NOT NULL");
        $this->addSql("ALTER TABLE fos_user ALTER credentials_expired DROP NOT NULL");
    }
}
