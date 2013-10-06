<?php

namespace AdminModule\EshopModule;

/**
 * Description of SettingsPresenter
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class SettingsPresenter extends \AdminModule\BasePresenter {
			
	protected function startup() {
		parent::startup();
		
	}

	protected function beforeRender() {
		parent::beforeRender();
		
	}
	
	public function actionDefault($idPage){

	}
	
	public function createComponentSettingsForm(){
		
		$settings = array();
		$settings[] = $this->settings->get('Order saved email', 'eshopModule', 'textarea', array());
		
		return $this->createSettingsForm($settings);
	}
	
	public function renderDefault($idPage){
		$this->reloadContent();
		
		$this->template->config = $this->settings->getSection('eshopModule');
		$this->template->idPage = $idPage;
	}
	
	
}