<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\orm\Mapping as orm;

/**
 * Description of Parameter
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Parameter extends \AdminModule\Doctrine\Entity {
	/**
	 * @orm\Column
	 */
	private $name;
	
	/**
	 * @orm\OneToMany(targetEntity="ParameterValue", mappedBy="parameter", cascade={"persist"})
	 */
	private $values;
	
	function __construct() {
		$this->values = new ArrayCollection();
	}

	public function getName() {
		return $this->name;
	}

	public function getValues() {
		return $this->values;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setValues($values) {
		$this->values = $values;
	}
}
