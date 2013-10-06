<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\orm\Mapping as orm;

/**
 * Description of OrderState
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class OrderState extends \AdminModule\Doctrine\Entity{
	/**
	 * @orm\Column
	 */
	private $title;
	
	/**
	 * @orm\Column(name="`default`", type="boolean")
	 */
	private $default;
	
	/**
	 * @orm\ManyToOne(targetEntity="\AdminModule\Language")
	 * @orm\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $language;
	
	public function getTitle() {
		return $this->title;
	}

	public function getDefault() {
		return $this->default;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setDefault($default) {
		$this->default = $default;
	}
	
	public function getLanguage() {
		return $this->language;
	}

	public function setLanguage($language) {
		$this->language = $language;
	}
}
