<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200625131822 extends AbstractMigration
{
    
    public function getDescription() : string
    {
        return 'This migration will move file name, size, and hash256 to Files entity!';
    }
    
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $statement = "SELECT id FROM dataset_submission WHERE dataset_file_uri LIKE 'file://%';";
        $datasetSubmissions = $this->connection->fetchAll($statement);
                
        foreach ($datasetSubmissions as $submission) {
            $id = $submission['id'];
            $this->addSql("INSERT INTO fileset (id, creator_id, modifier_id, creation_time_stamp, modification_time_stamp) VALUES (nextval('public.fileset_id_seq'), 0, 0, CURRENT_TIMESTAMP(0), CURRENT_TIMESTAMP(0))");
            $this->addSql("UPDATE dataset_submission SET fileset_id = currval('public.fileset_id_seq') WHERE id = $id");
            $this->addSql("INSERT INTO file (id, fileset_id, creation_time_stamp, creator_id, modification_time_stamp, modifier_id, file_name, file_size, file_sha256_hash, file_path, status) SELECT nextval('file_id_seq'), currval('public.fileset_id_seq'), CURRENT_TIMESTAMP(0), 0, CURRENT_TIMESTAMP(0), 0, dataset_file_name, dataset_file_size, dataset_file_sha256_hash, SUBSTRING(dataset_file_uri FROM '\/[^\/].*$'), 'processed' FROM dataset_submission WHERE id = $id");
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();

    }
}
