<?php
/**
 * This Interface serves as a marker and a contract
 * for the minimum (or nearly minimum) functionality
 * needed for the I_Persistence concrete implementation
 * to store instances of this (I_Persistable) type.
 * @see I_Persistence.php
 */
namespace Persistence;

interface I_Persistable {

    /** return the class name of this instance */
    public static function  getClassName();

    /**
     * Return a unique identifier for this instance
     * @return mixed the key
     */
    public function getKey();

    /**
     * Assign the unique identifier this instance
     * @param $key
     * @return nothing
     */
    public function setKey($key);

    /**
     * Compare for equality to another object of this
     * same type. Retrun true if equal false if not.
     * Equality is te be defined by implementation
     * and is intended to symantic or "deep" equal not
     * merely referring or point to the same object
     * @param $otherObj
     * @return bool
     */

    public function isEqual($otherObj);

    /**
     * Compare this object to the one offered as target.
     * The question being if this object "matches" in the
     * sense that any fields provided in the target instance
     * match those same fields in this object return true.
     * Return false if no match occurs.
     * @param I_Persistable $target
     * @return bool
     */

    public function matchesTarget(I_Persistable $target);

}