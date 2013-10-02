<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\orm\Mapping as orm;
use Gedmo\Mapping\Annotation as gedmo;

/**
 * Description of Product
 * @orm\Entity
 * @orm\Table(name="eshopProduct")
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Product extends \AdminModule\Seo {
	/**
     * @orm\Column(length=64)
     */
    private $title;
	
    /**
     * @orm\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @gedmo\Slug(fields={"title"})
     * @orm\Column(length=64)
     */
    private $slug;
	
	/**
	 * @orm\OneToMany(targetEntity="Photo", mappedBy="Product")
	 */
	private $photos;
	
	/**
	 * @orm\ManyToMany(targetEntity="Category", cascade={"persist"})
	 * @orm\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $categories;
	
	/**
	 * @orm\ManyToOne(targetEntity="\AdminModule\Language")
	 * @orm\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $language;
	
	/**
	 * @orm\Column(type="decimal", precision=12, scale=4)
	 */
	private $price;
	
	/**
	 * @orm\Column(type="integer")
	 */
	private $vat;
	
	private $priceWithVat;
	
	public function __construct(){
		$this->categories = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	public function addCategory($category){
		$this->categories->add($category);
	}
	
	public function getTitle() {
		return $this->title;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function getPhotos() {
		return $this->photos;
	}

	public function getCategories() {
		return $this->categories;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function setSlug($slug) {
		$this->slug = $slug;
	}

	public function setPhotos($photos) {
		$this->photos = $photos;
	}

	public function setCategories($categories) {
		$this->categories = $categories;
	}
	
	public function getLanguage() {
		return $this->language;
	}

	public function setLanguage($language) {
		$this->language = $language;
	}
	
	public function getPrice() {
		return $this->price;
	}

	public function getVat() {
		return $this->vat;
	}

	public function setPrice($price) {
		$this->price = $price;
	}

	public function setVat($vat) {
		$this->vat = $vat;
	}
	
	public function getPriceWithVat() {
		return $this->price * (($this->vat / 100) + 1);
	}

	public function setPriceWithVat($priceWithVat) {
		$this->priceWithVat = $priceWithVat;
	}
}