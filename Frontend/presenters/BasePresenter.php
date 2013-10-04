<?php

namespace FrontendModule\EshopModule;

/**
 * Description of BasePresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class BasePresenter extends \FrontendModule\BasePresenter{
	
	protected function startup(){
		parent::startup();
		
		$cart = $this->em->getRepository('AdminModule\Page')->findBy(array(
			'language' => $this->language,
			'presenter' => 'Cart'
		));
		
		if(count($cart) > 0){
			$cart = $cart[0];
		
			$this->template->cartUrl = $this->link(':Frontend:Eshop:Cart:default', array(
				'id' => $cart->getId(),
				'path' => $cart->getPath(),
				'abbr' => $this->abbr
			));
		}
	}
}
