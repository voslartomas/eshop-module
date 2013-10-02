<?php

namespace AdminModule\EshopModule;

use Nette\Application\UI;

/**
 * Description of ProductsPresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class ProductsPresenter extends BasePresenter{
	
	private $categoryRepository;
	
	private $repository;
	
	/* @var \WebCMS\EshopModule\Doctrine\Product */
	private $product;
	
	protected function beforeRender() {
		parent::beforeRender();	
	}
	
	public function renderDefault($idPage){
		$this->reloadContent();
		
		$this->template->idPage = $idPage;
	}
	
	protected function startup(){		
		parent::startup();
		
		$this->categoryRepository = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Category');
		$this->repository = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Product');
	}
	
	protected function createComponentProductForm(){
		
		$hierarchy = $this->categoryRepository->getTreeForSelect(array(
			array('by' => 'root', 'dir' => 'ASC'), 
			array('by' => 'lft', 'dir' => 'ASC')
			),
			array(
				'language = ' . $this->state->language->getId()
		));
		
		$hierarchy = $hierarchy;
		
		$form = $this->createForm();
		$form->addText('title', 'Name')->setAttribute('class', 'form-control');
		$form->addText('price', 'Price')->setAttribute('class', 'form-control');
		$form->addText('vat', 'Vat')->setAttribute('class', 'form-control');
		$form->addMultiSelect('categories', 'Categories')->setTranslator(NULL)->setItems($hierarchy)->setAttribute('class', 'form-control');
		
		$form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');
		
		$form->onSuccess[] = callback($this, 'productFormSubmitted');
		
		if($this->product){
			$defaults = $this->product->toArray();
			
			$defaultCategories = array();
			foreach($this->product->getCategories() as $c){
				$defaultCategories[] = $c->getId();
			}
			
			$defaults['categories'] = $defaultCategories;
			$form->setDefaults($defaults);
		}
		
		return $form;
	}
	
	public function actionUpdateProduct($idPage, $id){
		if($id) $this->product = $this->repository->find($id);
		else $this->product = new \WebCMS\EshopModule\Doctrine\Product();
	}
	
	public function productFormSubmitted(UI\Form $form){
		$values = $form->getValues();

		$this->product->setTitle($values->title);
		$this->product->setLanguage($this->state->language);
		$this->product->setPrice($values->price);
		$this->product->setVat($values->vat);
		
		// delete old categories
		$this->product->setCategories(new \Doctrine\Common\Collections\ArrayCollection());
		
		// set categories
		foreach($values->categories as $c){
			$category = $this->categoryRepository->find($c);
			$this->product->addCategory($category);
		}
		
		if(!$this->product->getId()) $this->em->persist($this->product); // FIXME only if is new we have to persist entity, otherway it can be just flushed
		$this->em->flush();
		
		$this->flashMessage($this->translation['Product has been added.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Products:default', array('idPage' => $this->actualPage->getId()));
	} 
	
	public function actionDefault($idPage) {}
	
	protected function createComponentProductsGrid($name){
				
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Product', array(),
			array(
				'language = ' . $this->state->language->getId(),
			)
		);
		
		$grid->addColumn('title', 'Name')->setSortable()->setFilter();
		$grid->addColumn('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\PriceFormatter::format($item->getPrice());
		})->setSortable()->setFilter();
		$grid->addColumn('vat', 'Vat')->setSortable()->setFilter();
		$grid->addColumn('priceWithVat', 'Price with vat')->setCustomRender(function($item){
			return \WebCMS\PriceFormatter::format($item->getPriceWithVat());
		})->setSortable()->setFilter();
				
		$grid->addAction("updateProduct", 'Edit', \Grido\Components\Actions\Action::TYPE_HREF, 'updateProduct', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteProduct", 'Delete', \Grido\Components\Actions\Action::TYPE_HREF, 'deleteProduct', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
	}
	
	public function actionDeleteProduct($idPage, $id){
		$this->product = $this->repository->find($id);
		$this->em->remove($this->product);
		$this->em->flush();
		
		$this->flashMessage($this->translation['Product has been removed.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Products:default', array('idPage' => $idPage));
	}
		
	public function renderUpdateProduct($idPage){
		$this->reloadModalContent();
		
		$this->template->product = $this->product;
		$this->template->idPage = $idPage;
	}
}
