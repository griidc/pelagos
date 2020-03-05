<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200305163604 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // Will set to not-null after data is populated in.
        $this->addSql('ALTER TABLE publication ADD citation_text citext');

        // Migrate the attribute in: (we can safely assume there was only 1 Citation per PublicationCitation)
        $this->addSql('UPDATE publication SET citation_text = (SELECT citation_text FROM publication_citation WHERE publication_id = publication.id)');
        $this->addSql('ALTER TABLE publication ALTER citation_text SET NOT NULL');

        $this->addSql('DROP SEQUENCE publication_citation_id_seq CASCADE');
        $this->addSql('DROP TABLE publication_citation');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE publication_citation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE publication_citation (id INT NOT NULL, publication_id INT DEFAULT NULL, creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, citation_text citext NOT NULL, style citext NOT NULL, locale citext NOT NULL, creation_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, modification_time_stamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d5da692f38b217a7 ON publication_citation (publication_id)');
        $this->addSql('CREATE INDEX idx_d5da692f61220ea6 ON publication_citation (creator_id)');
        $this->addSql('CREATE INDEX idx_d5da692fd079f553 ON publication_citation (modifier_id)');
        $this->addSql('ALTER TABLE publication_citation ADD CONSTRAINT fk_d5da692f38b217a7 FOREIGN KEY (publication_id) REFERENCES publication (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE publication_citation ADD CONSTRAINT fk_d5da692f61220ea6 FOREIGN KEY (creator_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE publication_citation ADD CONSTRAINT fk_d5da692fd079f553 FOREIGN KEY (modifier_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Migrate attribute back. Creator/modifier and associated timestamps should have been the same anyway, even as separate entity.
        // Since only 1 Citation per Publication, and it's a new table, works without calling the sequence.
        $this->addSql('INSERT INTO publication_citation (id, publication_id, creator_id, modifier_id, citation_text, style, locale, creation_time_stamp, modification_time_stamp) SELECT id, id, creator_id, modifier_id, citation_text, \'apa\', \'UTF-8\', creation_time_stamp, modification_time_stamp FROM publication');
        $this->addSql('ALTER TABLE publication DROP citation_text');
    }
}
