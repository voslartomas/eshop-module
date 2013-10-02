<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\orm\Mapping as orm;

/**
 * Description of Photo
 * @orm\Entity
 * @orm\Table(name="eshopPhoto")
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Photo extends \AdminModule\Doctrine\Entity {
	/**
	 * @orm\Column
	 */
	private $path;
	
	/**
	 * @orm\Column
	 */
	private $title;
	
	/**
	 * @orm\ManyToOne(targetEntity="Product")
	 * @orm\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $product;
}
