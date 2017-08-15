<?php

namespace Entity;

/**
 * Campo_crm
 */
class Campo_crm
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $user_id;

    /**
     * @var string
     */
    private $campo;

    /**
     * @var string
     */
    private $valor;

    /**
     * @var string
     */
    private $uid;

    /**
     * @var string
     */
    private $pag;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param string $userId
     *
     * @return Campo_crm
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set campo
     *
     * @param string $campo
     *
     * @return Campo_crm
     */
    public function setCampo($campo)
    {
        $this->campo = $campo;

        return $this;
    }

    /**
     * Get campo
     *
     * @return string
     */
    public function getCampo()
    {
        return $this->campo;
    }

    /**
     * Set valor
     *
     * @param string $valor
     *
     * @return Campo_crm
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set uid
     *
     * @param string $uid
     *
     * @return Campo_crm
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get uid
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set pag
     *
     * @param string $pag
     *
     * @return Campo_crm
     */
    public function setPag($pag)
    {
        $this->pag = $pag;

        return $this;
    }

    /**
     * Get pag
     *
     * @return string
     */
    public function getPag()
    {
        return $this->pag;
    }
    /**
     * @var string
     */
    private $respaldo;


    /**
     * Set respaldo
     *
     * @param string $respaldo
     *
     * @return Campo_crm
     */
    public function setRespaldo($respaldo)
    {
        $this->respaldo = $respaldo;

        return $this;
    }

    /**
     * Get respaldo
     *
     * @return string
     */
    public function getRespaldo()
    {
        return $this->respaldo;
    }
}
