<?php

namespace AdminModule\EshopModule;

/**
 * Description of ProductsPresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class ProductsPresenter extends BasePresenter{
	
	protected function beforeRender() {
		parent::beforeRender();
		
	}
	
	public function renderDefault($idPage){
		$this->reloadContent();
		
		$this->template->idPage = $idPage;
	}
}
