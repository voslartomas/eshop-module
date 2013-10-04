<?php

namespace AdminModule\EshopModule;

use Nette\Application\UI;

/**
 * Description of CartPresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class CartPresenter extends BasePresenter{
	
	private $repository;
	
	public function startup() {
		parent::startup();
		
		$this->repository = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Order');
	}
	
	public function actionDefault($idPage) {}
	
	protected function beforeRender() {
		parent::beforeRender();	
	}
	
	public function renderDefault($idPage){
		$this->reloadContent();
		
		$this->template->idPage = $idPage;
	}
	
	protected function createComponentOrdersGrid($name){
				
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Order');
		
		$grid->addColumn('firstname', 'Firstname')->setSortable()->setFilter();
		$grid->addColumn('lastname', 'Lastname')->setSortable()->setFilter();
		$grid->addColumn('email', 'Email')->setSortable()->setFilter();
		$grid->addColumn('street', 'Street')->setSortable()->setFilter();
		$grid->addColumn('city', 'City')->setSortable()->setFilter();
		$grid->addColumn('postcode', 'Postcode')->setSortable()->setFilterNumber();
		
		$grid->addColumn('priceTotal', 'Price total')->setCustomRender(function($item){
			return \WebCMS\PriceFormatter::format($item->getPriceTotal());
		})->setSortable()->setFilterNumber();
				
		$grid->addAction("editOrder", 'Edit', \Grido\Components\Actions\Action::TYPE_HREF, 'editOrder', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax'));
		$grid->addAction("deleteOrder", 'Delete', \Grido\Components\Actions\Action::TYPE_HREF, 'deleteOrder', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary btn-danger'));
		
		return $grid;
	}
	
	public function actionDeleteOrder($id, $idPage){
		
		$order = $this->repository->find($id);
		$this->em->remove($order);
		$this->em->flush();
		
		$this->flashMessage('Order has been deleted.', 'success');
		if(!$this->isAjax())
			$this->redirect('Cart:default', array('idPage' => $idPage));
	}
}
