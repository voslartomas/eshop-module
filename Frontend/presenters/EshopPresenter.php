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
		));
		
		$actionProducts = $this->repositoryProducts->findBy(array(
			'language' => $this->language,
			'action' => TRUE
		));
		
		$this->setCategoriesLinks($favouritesCategories, $catPage);
		$this->setProductsLinks($favouritesProducts, $catPage);
		$this->setProductsLinks($actionProducts, $catPage);
		
		$this->template->favouriteCategories = $favouritesCategories;
		$this->template->favouriteProducts = $favouritesProducts;
		$this->template->actionProducts = $actionProducts;
		$this->template->id = $id;
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

?>
