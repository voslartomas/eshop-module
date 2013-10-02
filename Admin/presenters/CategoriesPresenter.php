<?php

namespace AdminModule\EshopModule;

/**
 * Description of CategoriesPresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class CategoriesPresenter extends BasePresenter{
	
	protected function beforeRender() {
		parent::beforeRender();
		
	}
	
	public function renderDefault($id){
		$this->reloadContent();
		
		$this->template->id = $id;
	}
}
