<?php

namespace FrontendModule\EshopModule;

/**
 * Description of PagePresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class EshopPresenter extends \FrontendModule\BasePresenter{
	
	private $repository;
	
	private $page;
	
	protected function startup() {
		parent::startup();
	
		$this->repository = $this->em->getRepository('WebCMS\PageModule\Doctrine\Page');
	}

	protected function beforeRender() {
		parent::beforeRender();
		
	}
	
	public function actionDefault($id){
		
		
	}
	
	public function renderDefault($id){
		
		$this->template->id = $id;
	}

}

?>
