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
        
        $statement = "SELECT dataset_submission.dataset_id AS id, dataset.udi AS udi FROM dataset_submission JOIN dataset ON dataset.dataset_submission_id = dataset_submission.id WHERE dataset.dataset_status NOT IN ('None');";
        $datasetSubmissions = $this->connection->fetchAll($statement);
                
        foreach ($datasetSubmissions as $submission) {
            $id = $submission['id'];
            $udi = $submission['udi'];
            if (file_exists("/san/data/store/$udi/$udi.dat")) {
                $this->addSql("INSERT INTO fileset (id, creator_id, modifier_id, creation_time_stamp, modification_time_stamp) VALUES (nextval('public.fileset_id_seq'), 0, 0, CURRENT_TIMESTAMP(0), CURRENT_TIMESTAMP(0))");
                $this->addSql("UPDATE dataset_submission SET fileset_id = currval('public.fileset_id_seq') WHERE id = $id");
                $this->addSql("INSERT INTO file (id, fileset_id, creation_time_stamp, creator_id, modification_time_stamp, modifier_id, file_path_name, file_size, file_sha256_hash, physical_file_path  , status) SELECT nextval('file_id_seq'), currval('public.fileset_id_seq'), CURRENT_TIMESTAMP(0), 0, CURRENT_TIMESTAMP(0), 0, dataset_file_name, dataset_file_size, dataset_file_sha256_hash, '$udi/$udi.dat', 'done' FROM dataset_submission WHERE id = $id");
            }
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();

    }
}
