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
	
	public function createComponentPaymentsGrid($name){
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Payment', NULL, array(
				'language = ' . $this->state->language->getId(),
			)
		);
		
		$grid->addColumn('title', 'Name')->setFilter();
		
		$grid->addColumn('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\SystemHelper::price($item->getPrice());
		});
		
		$grid->addAction("updatePayment", 'Edit', \Grido\Components\Actions\Action::TYPE_HREF, 'updatePayment', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deletePayment", 'Delete', \Grido\Components\Actions\Action::TYPE_HREF, 'deletePayment', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
	}
	
	public function createComponentShippingsGrid($name){
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Shipping', NULL, array(
				'language = ' . $this->state->language->getId(),
			)
		);
		
		$grid->addColumn('title', 'Name')->setFilter();
		
		$grid->addColumn('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\SystemHelper::price($item->getPrice());
		});
		
		$grid->addAction("updateShipping", 'Edit', \Grido\Components\Actions\Action::TYPE_HREF, 'updateShipping', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteShipping", 'Delete', \Grido\Components\Actions\Action::TYPE_HREF, 'deleteShipping', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
	}
}