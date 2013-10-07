<?php

namespace WebCMS\EshopModule;

/**
 * Description of Page
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Eshop extends \WebCMS\Module {
	
	protected $name = 'Eshop';
	
	protected $author = 'Tomáš Voslař';
	
	protected $presenters = array(
		array(
			'name' => 'Eshop',
			'frontend' => TRUE,
			'parameters' => FALSE
			),
		array(
			'name' => 'Categories',
			'frontend' => TRUE,
			'parameters' => TRUE
			),
		array(
			'name' => 'Products',
			'frontend' => FALSE
			),
		array(
			'name' => 'Cart',
			'frontend' => TRUE,
			'parameters' => FALSE
			),
		array(
			'name' => 'Settings',
			'frontend' => FALSE
			)
	);
	
	protected $params = array(
		
	);
	
	public function __construct(){
		$this->addBox('Shopping cart', 'Cart', 'cartBox', 'Eshop');
		$this->addBox('Categories list box', 'Categories', 'listBox', 'Eshop');
	}
	
}