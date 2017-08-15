<?php

namespace Entity;

/**
 * Campo
 */
class Campo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $identificador;

    /**
     * @var string
     */
    private $informacion;

    /**
     * @var integer
     */
    private $id_section;


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
     * Set identificador
     *
     * @param string $identificador
     *
     * @return Campo
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * Get identificador
     *
     * @return string
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * Set informacion
     *
     * @param string $informacion
     *
     * @return Campo
     */
    public function setInformacion($informacion)
    {
        $this->informacion = $informacion;

        return $this;
    }

    /**
     * Get informacion
     *
     * @return string
     */
    public function getInformacion()
    {
        return $this->informacion;
    }

    /**
     * Set idSection
     *
     * @param integer $idSection
     *
     * @return Campo
     */
    public function setIdSection($idSection)
    {
        $this->id_section = $idSection;

        return $this;
    }

    /**
     * Get idSection
     *
     * @return integer
     */
    public function getIdSection()
    {
        return $this->id_section;
    }
}
