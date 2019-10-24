<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Util\DOIutil;

/**
 * This command updated GML field of datasets.
 *
 * @see ContainerAwareCommand
 */
class UpdateGMLCommand extends ContainerAwareCommand
{
    /**
     * The entityManger.
     *
     * @var integer
     */
    private $entityManager = 0;
    
    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface
     */
    protected $output;
    
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
        ->setName('dataset:update-gml')
        ->setDescription('Updates the GML for datasets.');
    }
    
    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception When something is wrong.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        $sql = "
            CREATE OR REPLACE FUNCTION pg_temp.getGML(text) RETURNS geometry AS
            $$
            BEGIN
            RETURN ST_GeomFromGML($1);
            EXCEPTION when internal_error then
            RETURN null;
            END;
            $$ language 'plpgsql';
        ";
        $this->executeSQL($sql);
        $output->writeln('Create Special Function');

        
        $sql = "
            /* updating polygons that are not proper GML3 */
            UPDATE dataset_submission 
            SET spatial_extent = ST_asGML(3,pg_temp.getGML(spatial_extent),5,17,null,'Polygon1')
            WHERE ST_asGML(3,pg_temp.getGML(spatial_extent),5,17,null,'Polygon1') <> spatial_extent
            AND pg_temp.getGML(spatial_extent::text) is not null
            AND GeometryType(pg_temp.getGML(spatial_extent)) = 'POLYGON'
            ;
        ";
        $this->executeSQL($sql);
        $output->writeln('Update Dataset Subs polygons');
        
        $sql = "
            /* updating polygons that are not proper GML3 */
            UPDATE dif
            SET spatial_extent_geometry = ST_asGML(3,ST_GeomFromGML(spatial_extent_geometry),5,17,null,'Polygon1')
            WHERE ST_asGML(3,ST_GeomFromGML(spatial_extent_geometry),5,17,null,'Polygon1') <> spatial_extent_geometry
            AND GeometryType(ST_GeomFromGML(spatial_extent_geometry)) = 'POLYGON'
            ;
        ";
        $this->executeSQL($sql);
        $output->writeln('Update DIF polygons');
        
        $sql = "
            /* updating points that are not proper GML3 */
            UPDATE dif
            SET spatial_extent_geometry = ST_asGML(3,ST_GeomFromGML(spatial_extent_geometry),5,17,null,'Point1')
            WHERE ST_asGML(3,ST_GeomFromGML(spatial_extent_geometry),5,17,null,'Point1') <> spatial_extent_geometry
            AND GeometryType(ST_GeomFromGML(spatial_extent_geometry)) = 'POINT'
            ;
        ";
        $this->executeSQL($sql);
        $output->writeln('Update DIF points');
        
        $sql = "
            /* updating lines that are not proper GML3 */
            UPDATE dif
            SET spatial_extent_geometry = ST_asGML(3,ST_GeomFromGML(spatial_extent_geometry),5,17,null,'Curve1')
            WHERE ST_asGML(3,ST_GeomFromGML(spatial_extent_geometry),5,17,null,'Curve1') <> spatial_extent_geometry
            AND GeometryType(ST_GeomFromGML(spatial_extent_geometry)) = 'LINESTRING'
            ;
        ";
        $this->executeSQL($sql);
        $output->writeln('Update DIF lines');
        
        $sql = "
            /* updating points that are not proper GML3 */
            UPDATE dataset_submission 
            SET spatial_extent = ST_asGML(3,pg_temp.getGML(spatial_extent),5,17,null,'Point1')
            WHERE ST_asGML(3,pg_temp.getGML(spatial_extent),5,17,null,'Point1') <> spatial_extent
            AND pg_temp.getGML(spatial_extent::text) is not null
            AND GeometryType(pg_temp.getGML(spatial_extent)) = 'POINT'
            ;
        ";
        $this->executeSQL($sql);
        $output->writeln('Update Dataset Subs Points');
        
        $sql = "
            /* updating lines that are not proper GML3 */
            UPDATE dataset_submission 
            SET spatial_extent = ST_asGML(3,pg_temp.getGML(spatial_extent),5,17,null,'Curve1')
            WHERE ST_asGML(3,pg_temp.getGML(spatial_extent),5,17,null,'Curve1') <> spatial_extent
            AND pg_temp.getGML(spatial_extent::text) is not null
            AND GeometryType(pg_temp.getGML(spatial_extent)) = 'LINESTRING'
            ;
        ";
        $this->executeSQL($sql);
        $output->writeln('Update Dataset Subs Lines');
        
        $sql = "
            /* counting multi's that don't have proper srsname */
            SELECT
            dif.id,
            dataset.udi,
            ST_asGML(3,ST_GeomFromGML(spatial_extent_geometry),5,17)::TEXT AS gml3
            FROM dif
            LEFT JOIN dataset ON dif.id = dataset.dif_id 
            WHERE spatial_extent_geometry <> ''
            AND ST_GeometryType(ST_GeomFromGML(spatial_extent_geometry)) = 'ST_MultiPoint'
            ;
        ";
        $results = $this->executeSQL($sql);
        
        foreach ($results as $result) {
            $udi = $result['udi'];
            $id = $result['id'];
            $gml3 = $result['gml3'];
            $difs = $this->entityManager
            ->getRepository('Pelagos\Entity\DIF')
            ->findBy(array('id' => $id));
            
            foreach ($difs as $dif) {
                $gml = $this->addGMLid($gml3);
                $dif->setSpatialExtentGeometry($gml);
                
                $this->entityManager->persist($dif);
                $this->entityManager->flush();
            }
            
            $output->writeln("Updated DIF GML for $udi");
        }
        
        $sql = "
            SELECT 
            dataset_submission.id,
            dataset.udi,
            ST_asGML(3,pg_temp.getGML(spatial_extent::text),5,17) as gml3
            FROM dataset_submission
            JOIN dataset ON dataset.id = dataset_submission.dataset_id
            WHERE spatial_extent <> ''
            AND pg_temp.getGML(spatial_extent::text) is not null
            AND ST_GeometryType(ST_GeomFromGML(spatial_extent)) = 'ST_MultiPoint'
            AND spatial_extent !~ 'srsName=\"urn:ogc:def:crs:EPSG::4326\"'
            ;
        ";
        $results = $this->executeSQL($sql);
        
        foreach ($results as $result) {
            $udi = $result['udi'];
            $id = $result['id'];
            $gml3 = $result['gml3'];
            $datasetSubmissions = $this->entityManager
                ->getRepository('Pelagos\Entity\DatasetSubmission')
                ->findBy(array('id' => $id));
            
            foreach ($datasetSubmissions as $datasetSubmission) {
                $gml = $this->addGMLid($gml3);
                $datasetSubmission->setSpatialExtent($gml);
                
                $this->entityManager->persist($datasetSubmission);
                $this->entityManager->flush();
            }
            
            $output->writeln("Updated submission GML for $udi");
        }

        $output->writeln('All done!');
        
        return 0;
    }
    
    /**
     * This function executes SQL.
     *
     * @param string $sql SQL that need to be executed.
     *
     * @return mixed
     */
    private function executeSQL($sql)
    {
        $sqlStatement = $this->entityManager->getConnection()->prepare($sql);
        $sqlStatement->execute();
        return $sqlStatement->fetchAll();
    }
    
    /**
     * This function add an ID to gml a feature.
     *
     * @param string $gml GML that needs ID added.
     *
     * @return string
     */
    private function addGMLid($gml)
    {
        $gmlNameSpace = 'xmlns:gml="http://www.opengis.net/gml/3.2"';
        $regEx = '/^(<gml\b[^><]*)>/';
        $gml = preg_replace($regEx, "$1 $gmlNameSpace>", $gml);
        
        $doc = new \DomDocument('1.0', 'UTF-8');
        $doc->loadXML($gml, LIBXML_NOERROR);
        
        foreach ($doc->childNodes as $node) {
            $topNode = $node->nodeName;
            switch ($topNode) {
                case 'gml:Polygon':
                    $node->setAttribute('gml:id', 'Polygon1');
                    break;
                case 'gml:Curve':
                    $node->setAttribute('gml:id', 'Curve1');
                    break;
                case 'gml:Point':
                    $node->setAttribute('gml:id', 'Point1');
                    break;
                case 'gml:MultiPoint':
                    $node->setAttribute('gml:id', 'Multipoint1');
                    $i = 0;
                    foreach ($node->childNodes as $child) {
                        $i++;
                        $child->firstChild->setAttribute('gml:id', "Point$i");
                    }
                    break;
                case 'gml:MultiCurve':
                    $node->setAttribute('gml:id', 'MultiCurve1');
                    $i = 0;
                    foreach ($node->childNodes as $child) {
                        $i++;
                        $child->firstChild->setAttribute('gml:id', "Curve$i");
                    }
                    break;
                case 'gml:MultiSurface':
                    $node->setAttribute('gml:id', 'MultiSurface');
                    $i = 0;
                    foreach ($node->childNodes as $child) {
                        $i++;
                        $child->firstChild->setAttribute('gml:id', "Polygon$i");
                    }
                    break;
            }
        }
        
        $gml = $doc->saveXML();
        $cleanXML = new \SimpleXMLElement($gml, LIBXML_NOERROR);
        $dom = dom_import_simplexml($cleanXML);
        $gml = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
        $gml = str_replace($gmlNameSpace, '', $gml);
        return $gml;
    }
}
