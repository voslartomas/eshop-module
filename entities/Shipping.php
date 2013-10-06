<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\orm\Mapping as orm;

/**
 * Description of Shipping
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Shipping extends \AdminModule\Doctrine\Entity{
	/**
	 * @orm\Column(type="decimal", precision=12, scale=4)
	 */
	private $price;
	
	/**
	 * @orm\Column(type="integer")
	 */
	private $vat;
	
	/**
	 * @orm\Column
	 */
	private $title;
	
	public function getPrice() {
		return $this->price;
	}

	public function getVat() {
		return $this->vat;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setPrice($price) {
		$this->price = $price;
	}

	public function setVat($vat) {
		$this->vat = $vat;
	}

	public function setTitle($title) {
		$this->title = $title;
	}
}
