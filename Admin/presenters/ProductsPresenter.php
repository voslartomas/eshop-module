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
	
	private $photos;
	
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
		
		$form = $this->createForm();
		$form->addText('title', 'Name')->setAttribute('class', 'form-control')->setRequired('Please fill in a name.');
		$form->addCheckbox('favourite', 'Favourite')->setAttribute('class', 'form-control');
		$form->addCheckbox('action', 'Action')->setAttribute('class', 'form-control');
		$form->addCheckbox('hide', 'Hide')->setAttribute('class', 'form-control');
		$form->addText('store', 'Store')->setAttribute('class', 'form-control');
		//$form->addText('price', 'Price')->setAttribute('class', 'form-control');
		$form->addText('vat', 'Vat')->setAttribute('class', 'form-control');
		$form->addText('priceWithVat', 'Price with VAT')->setAttribute('class', 'form-control');
		$form->addMultiSelect('categories', 'Categories')->setTranslator(NULL)->setItems($hierarchy)->setAttribute('class', 'form-control');
		$form->addTextArea('description')->setAttribute('class', 'form-control editor');
		
		$form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');
		
		$form->onSuccess[] = callback($this, 'productFormSubmitted');
		
		if($this->product){
			$defaults = $this->product->toArray();
			$defaults['priceWithVat'] = round($defaults['priceWithVat'], 4);
			
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
		
		if($this->product->getId()) $this->photos = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Photo')->findBy(array(
			'product' => $this->product
		));
	else 
		$this->photos = array();
	
	}
	
	public function productFormSubmitted(UI\Form $form){
		$values = $form->getValues();

		$this->product->setTitle($values->title);
		$this->product->setLanguage($this->state->language);
		$this->product->setHide($values->hide);
		$this->product->setStore($values->store);
		$this->product->setPrice($values->priceWithVat - $values->priceWithVat * ($values->vat / ($values->vat + 100)));
		$this->product->setVat($values->vat);
		$this->product->setFavourite($values->favourite);
		$this->product->setAction($values->action);
		$this->product->setDescription($values->description);
		
		// delete old categories
		$this->product->setCategories(new \Doctrine\Common\Collections\ArrayCollection());
		
		// set categories
		foreach($values->categories as $c){
			$category = $this->categoryRepository->find($c);
			$this->product->addCategory($category);
		}
		
		// delete old photos and save new ones
		if($this->product->getId()){
			$qb = $this->em->createQueryBuilder();
			$qb->delete('WebCMS\EshopModule\Doctrine\Photo', 'l')
					->where('l.product = ?1')
					->setParameter(1, $this->product)
					->getQuery()
					->execute();
			
		}else{
			$this->product->setDefaultPicture('');
		}
		
		if(array_key_exists('files', $_POST)){
			$counter = 0;
			if(array_key_exists('fileDefault', $_POST)) $default = intval($_POST['fileDefault'][0]) - 1;
			else $default = -1;
			
			foreach($_POST['files'] as $path){

				$photo = new \WebCMS\EshopModule\Doctrine\Photo;
				$photo->setTitle($_POST['fileNames'][$counter]);
				
				if($default === $counter){
					$photo->setDefault(TRUE);
					$this->product->setDefaultPicture($path);
				}else
					$photo->setDefault(FALSE);
					
				$photo->setPath($path);
				$photo->setProduct($this->product);

				$this->em->persist($photo);

				$counter++;
			}
		}
		
		if(!$this->product->getId()) $this->em->persist($this->product); // FIXME only if is new we have to persist entity, otherway it can be just flushed
		$this->em->flush();
		
		$this->flashMessage($this->translation['Product has been added.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('this');
	} 
	
	public function actionDefault($idPage) {}
	
	protected function createComponentProductsGrid($name){
				
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Product', NULL,
			array(
				'language = ' . $this->state->language->getId(),
			)
		);
		
		$grid->addColumnText('title', 'Name')->setSortable()->setFilterText();
		$grid->addColumnNumber('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\PriceFormatter::format($item->getPrice()) . ' (' .\WebCMS\PriceFormatter::format($item->getPriceWithVat()) . ')';
		})->setSortable()->setFilterNumber();
		$grid->addColumnNumber('vat', 'Vat')->setCustomRender(function($item){
			return $item->getVat() . '%';
		})->setSortable()->setFilterNumber();
				
		$grid->addActionHref("updateProduct", 'Edit', 'updateProduct', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax'));
		$grid->addActionHref("deleteProduct", 'Delete', 'deleteProduct', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

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
		$this->reloadContent();
		
		$this->template->photos = $this->photos;
		$this->template->product = $this->product;
		$this->template->idPage = $idPage;
	}
	
	protected function createComponentProductsVariantGrid($name){
				
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\ProductVariant', NULL,
			array(
				'product = ' . $this->product
			)
		);
		
		$grid->addColumnText('title', 'Name')->setSortable()->setFilterText();
		$grid->addColumnNumber('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\PriceFormatter::format($item->getPrice()) . ' (' .\WebCMS\PriceFormatter::format($item->getPriceWithVat()) . ')';
		})->setSortable()->setFilterNumber();
		
		$grid->addActionHref("updateVariant", 'Edit', 'updateVariant', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax'));
		$grid->addActionHref("deleteVariant", 'Delete', 'deleteVariant', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
	}
}
