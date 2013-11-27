<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\orm\Mapping as orm;
use Gedmo\Mapping\Annotation as gedmo;

/**
 * Description of Product
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class ProductVariant extends \AdminModule\Doctrine\Entity {
	/**
     * @orm\Column(length=64)
     */
    private $title;
	
	/**
	 * @orm\Column(type="decimal", precision=12, scale=4)
	 */
	private $price;
	
	/**
	 * @orm\Column(type="integer")
	 */
	private $store;
	
	/**
	 * @orm\ManyToOne(targetEntity="Product", inversedBy="variants")
	 * @orm\JoinColumn(onDelete="CASCADE")
	 */
	private $product;
	
	private $priceWithVat;
	
	private $link;
	
	public function __construct(){
	}
	
	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getPrice() {
		return $this->price;
	}

	public function setPrice($price) {
		$this->price = $price;
	}
	
	public function getPriceWithVat() {
		return $this->price * (($this->product->getVat() / 100) + 1);
	}

	public function getLink() {
		return $this->link;
	}

	public function setLink($link) {
		$this->link = $link;
	}
	
	public function getStore() {
		return $this->store;
	}

	public function setStore($store) {
		$this->store = $store;
	}
	
	public function getProduct() {
		return $this->product;
	}

	public function setProduct($product) {
		$this->product = $product;
	}
}