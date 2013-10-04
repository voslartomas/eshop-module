<?php

namespace AdminModule\EshopModule;

use Nette\Application\UI;

/**
 * Description of CartPresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class CartPresenter extends BasePresenter{
	
	public function actionDefault($idPage) {}
	
	protected function createComponentOrderssGrid($name){
				
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Order', array());
		
		$grid->addColumn('title', 'Name')->setSortable()->setFilter();
		$grid->addColumn('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\PriceFormatter::format($item->getPrice()) . ' (' .\WebCMS\PriceFormatter::format($item->getPriceWithVat()) . ')';
		})->setSortable()->setFilterNumber();
		$grid->addColumn('vat', 'Vat')->setCustomRender(function($item){
			return $item->getVat() . '%';
		})->setSortable()->setFilterNumber();
				
		$grid->addAction("updateProduct", 'Edit', \Grido\Components\Actions\Action::TYPE_HREF, 'updateProduct', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax'));
		$grid->addAction("deleteProduct", 'Delete', \Grido\Components\Actions\Action::TYPE_HREF, 'deleteProduct', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
	}
}
