<?php

namespace Entity;

/**
 * Resourceb
 */
class Resourceb
{
    /**
     * @var integer
     */
    private $idrsc;

    /**
     * @var integer
     */
    private $Id;

    /**
     * @var integer
     */
    private $ResourceId;

    /**
     * @var string
     */
    private $ResourceName;

    /**
     * @var string
     */
    private $CTRRuleta_c;

    /**
     * @var string
     */
    private $Username;

    /**
     * @var string
     */
    private $CTROrdenRuleta_c;

    /**
     * @var string
     */
    private $EmailAddress;


    /**
     * Get idrsc
     *
     * @return integer
     */
    public function getIdrsc()
    {
        return $this->idrsc;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Resourceb
     */
    public function setId($id)
    {
        $this->Id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     * Set resourceId
     *
     * @param integer $resourceId
     *
     * @return Resourceb
     */
    public function setResourceId($resourceId)
    {
        $this->ResourceId = $resourceId;

        return $this;
    }

    /**
     * Get resourceId
     *
     * @return integer
     */
    public function getResourceId()
    {
        return $this->ResourceId;
    }

    /**
     * Set resourceName
     *
     * @param string $resourceName
     *
     * @return Resourceb
     */
    public function setResourceName($resourceName)
    {
        $this->ResourceName = $resourceName;

        return $this;
    }

    /**
     * Get resourceName
     *
     * @return string
     */
    public function getResourceName()
    {
        return $this->ResourceName;
    }

    /**
     * Set cTRRuletaC
     *
     * @param string $cTRRuletaC
     *
     * @return Resourceb
     */
    public function setCTRRuletaC($cTRRuletaC)
    {
        $this->CTRRuleta_c = $cTRRuletaC;

        return $this;
    }

    /**
     * Get cTRRuletaC
     *
     * @return string
     */
    public function getCTRRuletaC()
    {
        return $this->CTRRuleta_c;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Resourceb
     */
    public function setUsername($username)
    {
        $this->Username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->Username;
    }

    /**
     * Set cTROrdenRuletaC
     *
     * @param string $cTROrdenRuletaC
     *
     * @return Resourceb
     */
    public function setCTROrdenRuletaC($cTROrdenRuletaC)
    {
        $this->CTROrdenRuleta_c = $cTROrdenRuletaC;

        return $this;
    }

    /**
     * Get cTROrdenRuletaC
     *
     * @return string
     */
    public function getCTROrdenRuletaC()
    {
        return $this->CTROrdenRuleta_c;
    }

    /**
     * Set emailAddress
     *
     * @param string $emailAddress
     *
     * @return Resourceb
     */
    public function setEmailAddress($emailAddress)
    {
        $this->EmailAddress = $emailAddress;

        return $this;
    }

    /**
     * Get emailAddress
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->EmailAddress;
    }
}
