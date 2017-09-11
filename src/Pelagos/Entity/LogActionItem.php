<?php
class LogActionItem extends Entity {
    /**
     * PROPDESCRIPTION
     * 
     * @access public
     * @var PROPTYPE
     */
    public $actionName;

    /**
     * PROPDESCRIPTION
     * 
     * @access public
     * @var PROPTYPE
     */
    public $subjectEntityName;

    /**
     * PROPDESCRIPTION
     * 
     * @access public
     * @var PROPTYPE
     */
    public $subjectEntityID;

    /**
     * PROPDESCRIPTION
     * 
     * @access public
     * @var PROPTYPE
     */
    public $payLoad;

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getActionName() {
        return $this->actionName;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $actionName ARGDESCRIPTION
     */
    public function setActionName($actionName) {
        $this->actionName = $actionName;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getSubjectEntityName() {
        return $this->subjectEntityName;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $subjectEntityName ARGDESCRIPTION
     */
    public function setSubjectEntityName($subjectEntityName) {
        $this->subjectEntityName = $subjectEntityName;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getSubjectEntityID() {
        return $this->subjectEntityID;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $subjectEntityID ARGDESCRIPTION
     */
    public function setSubjectEntityID($subjectEntityID) {
        $this->subjectEntityID = $subjectEntityID;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getPayLoad() {
        return $this->payLoad;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $payLoad ARGDESCRIPTION
     */
    public function setPayLoad($payLoad) {
        $this->payLoad = $payLoad;
    }
}