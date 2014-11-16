<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\orm\Mapping as orm;

/**
 * Description of ParameterValue
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class ParameterValue extends \WebCMS\Entity\Entity
{
    /**
     * @orm\Column
     */
    private $value;

    /**
     * @orm\ManyToOne(targetEntity="Parameter", inversedBy="values")
     * @orm\JoinColumn(onDelete="CASCADE")
     */
    private $parameter;

    public function getValue()
    {
        return $this->value;
    }

    public function getParameter()
    {
        return $this->parameter;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param Parameter $parameter
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
    }
}
