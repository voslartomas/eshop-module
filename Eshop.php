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
			'name' => 'Basket',
			'frontend' => TRUE,
			'parameters' => FALSE
			)
	);
	
	protected $params = array(
		
	);
	
	public function __construct(){
		//$this->addBox('Page box', 'Page', 'textBox');
	}
	
}