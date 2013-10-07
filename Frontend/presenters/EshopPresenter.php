<?php

namespace FrontendModule\EshopModule;

/**
 * Description of PagePresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class EshopPresenter extends BasePresenter{
	
	private $repositoryCategories;
	
	private $repositoryProducts;
	
	
	protected function startup() {
		parent::startup();
	
		$this->repositoryCategories = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Category');
		$this->repositoryProducts = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Product');
	}

	protected function beforeRender() {
		parent::beforeRender();
		
	}
	
	public function actionDefault($id){
		
	}
	
	public function renderDefault($id){
		
		$catPage = $this->em->getRepository('\AdminModule\Page')->findOneBy(array(
			'language' => $this->language,
			'moduleName' => 'Eshop',
			'presenter' => 'Categories'
		));
		
		$favouritesCategories = $this->repositoryCategories->findBy(array(
			'language' => $this->language,
			'favourite' => TRUE
		));
		
		$favouritesProducts = $this->repositoryProducts->findBy(array(
			'language' => $this->language,
			'favourite' => TRUE
		), array(
			'id' => 'ASC'
		), 5, 0);
		
		$actionProducts = $this->repositoryProducts->findBy(array(
			'language' => $this->language,
			'action' => TRUE
		));
		
		$this->setCategoriesLinks($favouritesCategories, $catPage);
		$this->setProductsLinks($favouritesProducts, $catPage);
		$this->setProductsLinks($actionProducts, $catPage);
		
		$this->template->limit = 5;
		$this->template->offset = 0;
		$this->template->favouriteCategories = $favouritesCategories;
		$this->template->favouriteProducts = $favouritesProducts;
		$this->template->actionProducts = $actionProducts;
		$this->template->id = $id;
	}
	
	public function actionLazyLoadFavouriteProducts($limit, $offset, $counter){
		if($this->isAjax()){
			$this->invalidateControl('lazyLoader');
		}
		
		$catPage = $this->em->getRepository('\AdminModule\Page')->findOneBy(array(
			'language' => $this->language,
			'moduleName' => 'Eshop',
			'presenter' => 'Categories'
		));
		
		$template = $this->createTemplate();
		$template->setFile('../app/templates/eshop-module/Eshop/lazyLoadFavouriteProducts.latte');
		$template->counter = $counter;
		$template->limit = $limit;
		$template->actualPage = $this->actualPage;
		$template->abbr = $this->abbr;
		$template->offset = $offset + $limit;
		$products = $this->repositoryProducts->findBy(array(
			'language' => $this->language,
			'favourite' => TRUE
		), array(
			'id' => 'ASC'
		), $limit, $offset);
		
		$this->setProductsLinks($products, $catPage);
		
		$template->products = $products;
		
		$template->render();
		$this->terminate();
	}
	
	private function setCategoriesLinks($categories, $catPage){
		foreach($categories as $c){
			$c->setLink($this->link(':Frontend:Eshop:Categories:default',
					array(
						'id' => $catPage->getId(),
						'path' => $catPage->getPath() . '/' . $c->getPath(),
						'abbr' => $this->abbr
					)
					));
		}
	}
	
	private function setProductsLinks($products, $catPage){
		foreach($products as $c){
			
			$category = $c->getCategories();
			$category = $category[0];
			
			$c->setLink($this->link(':Frontend:Eshop:Categories:default',
					array(
						'id' => $catPage->getId(),
						'path' => $catPage->getPath() . '/' . $category->getPath() . '/' . $c->getSlug(),
						'abbr' => $this->abbr
					)
					));
		}
	}
}