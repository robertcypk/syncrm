<?php

namespace Entity;

/**
 * Usuario_crm
 */
class Usuario_crm
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tipo;

    /**
     * @var string
     */
    private $crm;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $try;

    /**
     * @var string
     */
    private $dupli;

    /**
     * @var string
     */
    private $edupli;

    /**
     * @var integer
     */
    private $registro;

    /**
     * @var string
     */
    private $error;


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
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Usuario_crm
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set crm
     *
     * @param string $crm
     *
     * @return Usuario_crm
     */
    public function setCrm($crm)
    {
        $this->crm = $crm;

        return $this;
    }

    /**
     * Get crm
     *
     * @return string
     */
    public function getCrm()
    {
        return $this->crm;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Usuario_crm
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set try
     *
     * @param string $try
     *
     * @return Usuario_crm
     */
    public function setTry($try)
    {
        $this->try = $try;

        return $this;
    }

    /**
     * Get try
     *
     * @return string
     */
    public function getTry()
    {
        return $this->try;
    }

    /**
     * Set dupli
     *
     * @param string $dupli
     *
     * @return Usuario_crm
     */
    public function setDupli($dupli)
    {
        $this->dupli = $dupli;

        return $this;
    }

    /**
     * Get dupli
     *
     * @return string
     */
    public function getDupli()
    {
        return $this->dupli;
    }

    /**
     * Set edupli
     *
     * @param string $edupli
     *
     * @return Usuario_crm
     */
    public function setEdupli($edupli)
    {
        $this->edupli = $edupli;

        return $this;
    }

    /**
     * Get edupli
     *
     * @return string
     */
    public function getEdupli()
    {
        return $this->edupli;
    }

    /**
     * Set registro
     *
     * @param integer $registro
     *
     * @return Usuario_crm
     */
    public function setRegistro($registro)
    {
        $this->registro = $registro;

        return $this;
    }

    /**
     * Get registro
     *
     * @return integer
     */
    public function getRegistro()
    {
        return $this->registro;
    }

    /**
     * Set error
     *
     * @param string $error
     *
     * @return Usuario_crm
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    /**
     * @var string
     */
    private $procesado;


    /**
     * Set procesado
     *
     * @param string $procesado
     *
     * @return Usuario_crm
     */
    public function setProcesado($procesado)
    {
        $this->procesado = $procesado;

        return $this;
    }

    /**
     * Get procesado
     *
     * @return string
     */
    public function getProcesado()
    {
        return $this->procesado;
    }
    /**
     * @var string
     */
    private $encola;


    /**
     * Set encola
     *
     * @param string $encola
     *
     * @return Usuario_crm
     */
    public function setEncola($encola)
    {
        $this->encola = $encola;

        return $this;
    }

    /**
     * Get encola
     *
     * @return string
     */
    public function getEncola()
    {
        return $this->encola;
    }
    /**
     * @var string
     */
    private $cola;


    /**
     * Set cola
     *
     * @param string $cola
     *
     * @return Usuario_crm
     */
    public function setCola($cola)
    {
        $this->cola = $cola;

        return $this;
    }

    /**
     * Get cola
     *
     * @return string
     */
    public function getCola()
    {
        return $this->cola;
    }
    /**
     * @var string
     */
    private $procesando;


    /**
     * Set procesando
     *
     * @param string $procesando
     *
     * @return Usuario_crm
     */
    public function setProcesando($procesando)
    {
        $this->procesando = $procesando;

        return $this;
    }

    /**
     * Get procesando
     *
     * @return string
     */
    public function getProcesando()
    {
        return $this->procesando;
    }
}
