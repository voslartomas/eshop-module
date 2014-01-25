<?php

    namespace AdminModule\EshopModule;

    /**
     * Description of PagePresenter
     *
     * @author Tomáš Voslař <tomas.voslar at webcook.cz>
     */
    class BasePresenter extends \AdminModule\BasePresenter {

	private $repository;
	private $page;

	protected function startup() {
	    parent::startup();
	}

	protected function beforeRender() {
	    parent::beforeRender();
	}

	public function actionDefault($id) {
	    
	}

	public function renderDefault($id) {
	    $this->reloadContent();

	    $this->template->id = $id;
	}

	public function handleGenerateXml($force = false) {

	    if (!file_exists('./upload/exports')) {
		mkdir('./upload/exports');
	    }

	    $catPage = $this->em->getRepository('\AdminModule\Page')->findOneBy(array(
		'language' => $this->state->language,
		'moduleName' => 'Eshop',
		'presenter' => 'Categories'
	    ));

	    $products = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Product')->findBy(array(
		'language' => $this->state->language
	    ));

	    $this->setProductsLinks($products, $catPage);
	    
	    if($this->settings->get('Save zbozi.cz XML file after product update', 'eshopModule', 'checkbox')->getValue() || $force){
		$template = $this->createTemplate();
		$template->registerHelperLoader('\WebCMS\SystemHelper::loader');
		$template->setFile('../app/templates/eshop-module/exports/zbozicz.latte');
		$template->products = $products;
		$template->save('./upload/exports/export-zbozicz-' . $this->state->language->getAbbr() . '.xml');
	    }
	    
	    if($this->settings->get('Save heureka.cz XML file after product update', 'eshopModule', 'checkbox')->getValue() || $force){
		$template = $this->createTemplate();
		$template->registerHelperLoader('\WebCMS\SystemHelper::loader');
		$template->setFile('../app/templates/eshop-module/exports/heureka.latte');
		$template->products = $products;
		$template->save('./upload/exports/export-heureka-' . $this->state->language->getAbbr() . '.xml');
	    }
	    
	    $this->flashMessage('XML files has been exported. You can find these in Filesystem in directory Exports.', 'success');
	}

	private function setProductsLinks($products, $catPage) {
	    foreach ($products as $c) {

		$category = $c->getCategories();
		$category = $category[0];

		if ($category && $catPage) {
		    $c->setLink($this->link('//:Frontend:Eshop:Categories:default', array(
			    'id' => $catPage->getId(),
			    'path' => $catPage->getPath() . '/' . $category->getPath() . '/' . $c->getSlug(),
			    'abbr' => ($this->state->language->getDefaultFrontend() ? '' : $this->state->language->getAbbr() . '/')
			    )
		    ));
		}
	    }
	}

    }
    