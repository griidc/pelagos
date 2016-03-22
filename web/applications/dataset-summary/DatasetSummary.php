<?php

namespace DatasetSummary;

/**
 * Class for the Dataset Summary application.
 */
class DatasetSummary
{
    /**
     * Return an array of tables and WHERE clauses to select datasets from each table.
     *
     * @param resource $dbh A PDO database handle.
     * @param string   $udi The UDI to select.
     *
     * @return array
     */
    public static function getTables($dbh, $udi)
    {
        $tables = array(
            'datasets' => "dataset_udi = '$udi'",
            'registry' => "dataset_udi = '$udi'",
            'metadata' => "registry_id like '$udi%'",
            'dataset2publication_link_table' => "dataset_udi = '$udi'",
            'alt_datasets' => "primary_udi = '$udi' OR alternate_udi = '$udi'",
            'doi_regs' => "url LIKE '%$udi%'",
        );
        foreach ($tables as $table => $where) {
            $sth = $dbh->prepare("SELECT COUNT(*) FROM $table WHERE $where");
            $sth->execute();
            $count = $sth->fetchColumn();
            if ($count == 0) {
                unset($tables[$table]);
            }
        }
        return $tables;
    }

    /**
     * Check an UDI against a pattern for valid UDIs.
     *
     * @param string $udi The UDI to check.
     *
     * @return boolean
     */
    public static function validUdi($udi)
    {
        return preg_match('/^[RY][1-9]\.x\d{3}\.\d{3}:\d{4}$/', $udi);
    }

    /**
     * Check if a dataset exists.
     *
     * @param resource $dbh A PDO database handle.
     * @param string   $udi The UDI to check.
     *
     * @return boolean
     */
    public static function datasetExists($dbh, $udi)
    {
        return count(self::getTables($dbh, $udi)) > 0;
    }
}
