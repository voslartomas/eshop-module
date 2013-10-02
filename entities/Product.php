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
	 * @orm\OneToMany(targetEntity="Category", mappedBy="Product")
	 */
	private $categories;
	
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
}