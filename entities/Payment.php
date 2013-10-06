<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\orm\Mapping as orm;

/**
 * Description of Payment
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Payment extends \AdminModule\Doctrine\Entity{
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
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $paymentGate;
	
	/**
	 * @orm\ManyToOne(targetEntity="\AdminModule\Language")
	 * @orm\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $language;
	
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
	
	public function getPaymentGate() {
		return $this->paymentGate;
	}

	public function setPaymentGate($paymentGate) {
		$this->paymentGate = $paymentGate;
	}
	
	public function getLanguage() {
		return $this->language;
	}

	public function setLanguage($language) {
		$this->language = $language;
	}
}
