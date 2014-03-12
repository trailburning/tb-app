<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140312092645 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("DROP SEQUENCE media_versions_id_seq CASCADE");
        ;
        $this->addSql("ALTER TABLE medias ADD route_id INT NULL");
        $this->addSql("ALTER TABLE medias ADD path VARCHAR(100) DEFAULT NULL");
        $this->addSql("ALTER TABLE medias ADD original_name VARCHAR(100) DEFAULT NULL");

        $this->addSql("ALTER TABLE medias ADD CONSTRAINT FK_12D2AF8134ECB4E6 FOREIGN KEY (route_id) REFERENCES routes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        
        $this->addSql("UPDATE medias as m SET route_id = rm.route_id FROM route_medias AS rm WHERE m.id=rm.media_id");
        $this->addSql("UPDATE medias as m SET path = mv.path FROM media_versions AS mv WHERE m.id=mv.media_id");
        $this->addSql("UPDATE medias SET original_name = substring(path, 20)");
        
        $this->addSql("ALTER TABLE medias ALTER route_id SET NOT NULL");
        $this->addSql("ALTER TABLE medias ALTER path SET NOT NULL");
        $this->addSql("ALTER TABLE medias ALTER original_name SET NOT NULL");
        $this->addSql("CREATE INDEX IDX_12D2AF8134ECB4E6 ON medias (route_id)");
        $this->addSql("DROP TABLE route_medias");
        $this->addSql("DROP TABLE media_versions");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE SEQUENCE editorial_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE event_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE fos_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE gpx_files_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE media_versions_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE medias_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE region_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE route_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE route_points_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE route_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE routes_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE tz_world_mp_gid_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE TABLE media_versions (id SERIAL NOT NULL, media_id INT DEFAULT NULL, version_size SMALLINT DEFAULT NULL, path VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_16BD6E60EA9FDD75 ON media_versions (media_id)");
        $this->addSql("CREATE TABLE route_medias (route_id INT NOT NULL, media_id INT NOT NULL, linear_position DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(route_id, media_id))");
        $this->addSql("CREATE INDEX IDX_23215E5CEA9FDD75 ON route_medias (media_id)");
        $this->addSql("CREATE INDEX IDX_23215E5C34ECB4E6 ON route_medias (route_id)");
        $this->addSql("ALTER TABLE media_versions ADD CONSTRAINT media_versions_media_id_fkey FOREIGN KEY (media_id) REFERENCES medias (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE route_medias ADD CONSTRAINT fk_23215e5cea9fdd75 FOREIGN KEY (media_id) REFERENCES medias (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE route_medias ADD CONSTRAINT route_medias_route_id_fkey FOREIGN KEY (route_id) REFERENCES routes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE medias DROP CONSTRAINT FK_12D2AF8134ECB4E6");
        $this->addSql("DROP INDEX IDX_12D2AF8134ECB4E6");
        $this->addSql("ALTER TABLE medias DROP route_id");
        $this->addSql("ALTER TABLE medias DROP path");
        $this->addSql("ALTER TABLE medias DROP original_name");
    }
}
