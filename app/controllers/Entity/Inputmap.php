<?php

namespace Entity;

/**
 * Inputmap
 */
class Inputmap
{
    /**
     * @var integer
     */
    private $idinput;

    /**
     * @var string
     */
    private $input;

    /**
     * @var string
     */
    private $en;

    /**
     * @var string
     */
    private $es;


    /**
     * Get idinput
     *
     * @return integer
     */
    public function getIdinput()
    {
        return $this->idinput;
    }

    /**
     * Set input
     *
     * @param string $input
     *
     * @return Inputmap
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get input
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set en
     *
     * @param string $en
     *
     * @return Inputmap
     */
    public function setEn($en)
    {
        $this->en = $en;

        return $this;
    }

    /**
     * Get en
     *
     * @return string
     */
    public function getEn()
    {
        return $this->en;
    }

    /**
     * Set es
     *
     * @param string $es
     *
     * @return Inputmap
     */
    public function setEs($es)
    {
        $this->es = $es;

        return $this;
    }

    /**
     * Get es
     *
     * @return string
     */
    public function getEs()
    {
        return $this->es;
    }
}
