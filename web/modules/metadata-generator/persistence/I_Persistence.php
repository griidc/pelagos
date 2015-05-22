<?php

namespace Persistence;
/**
 * I_Persistence
 * An Interface to define the contract for
 * persistent storage
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf of Mexico Research Initiative Information Data Cooperative
 */


interface  I_Persistence {
    //public function initialize();
    public function add(I_Persistable $obj);
    public function modify(I_Persistable $obj);
    public function delete(I_Persistable $obj);
    public function find(I_Persistable $obj);
    public function getAll($className);
    public function getName();
    public function isDuplicateAllowed(I_Persistable $obj);
}