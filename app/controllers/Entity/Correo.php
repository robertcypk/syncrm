<?php

namespace Entity;

/**
 * Correo
 */
class Correo
{
    /**
     * @var integer
     */
    private $idcorreo;

    /**
     * @var string
     */
    private $co_codigo_programa;

    /**
     * @var string
     */
    private $co_estado;


    /**
     * Get idcorreo
     *
     * @return integer
     */
    public function getIdcorreo()
    {
        return $this->idcorreo;
    }

    /**
     * Set coCodigoPrograma
     *
     * @param string $coCodigoPrograma
     *
     * @return Correo
     */
    public function setCoCodigoPrograma($coCodigoPrograma)
    {
        $this->co_codigo_programa = $coCodigoPrograma;

        return $this;
    }

    /**
     * Get coCodigoPrograma
     *
     * @return string
     */
    public function getCoCodigoPrograma()
    {
        return $this->co_codigo_programa;
    }

    /**
     * Set coEstado
     *
     * @param string $coEstado
     *
     * @return Correo
     */
    public function setCoEstado($coEstado)
    {
        $this->co_estado = $coEstado;

        return $this;
    }

    /**
     * Get coEstado
     *
     * @return string
     */
    public function getCoEstado()
    {
        return $this->co_estado;
    }
    /**
     * @var string
     */
    private $co_correo;


    /**
     * Set coCorreo
     *
     * @param string $coCorreo
     *
     * @return Correo
     */
    public function setCoCorreo($coCorreo)
    {
        $this->co_correo = $coCorreo;

        return $this;
    }

    /**
     * Get coCorreo
     *
     * @return string
     */
    public function getCoCorreo()
    {
        return $this->co_correo;
    }
}
