<?php

namespace AdminModule\EshopModule;

/**
 * Description of CategoriesPresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class CategoriesPresenter extends BasePresenter{
	
	private $repository;
	
	/* @var \WebCMS\EshopModule\Doctrine\Category */
	private $category;
	
	protected function beforeRender() {
		parent::beforeRender();
		
	}
	
	protected function startup(){		
		parent::startup();
		
		$this->repository = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Category');
	}
	
	protected function createComponentCategoryForm(){

		$hierarchy = $this->repository->getTreeForSelect(array(
			array('by' => 'root', 'dir' => 'ASC'), 
			array('by' => 'lft', 'dir' => 'ASC')
			),
			array(
				'language = ' . $this->state->language->getId()
		));
		
		$hierarchy = array(0 => $this->translation['Pick parent']) + $hierarchy;
		
		$form = $this->createForm();
		$form->addText('title', 'Name')->setAttribute('class', 'form-control');
		$form->addSelect('parent', 'Parent')->setTranslator(NULL)->setItems($hierarchy)->setAttribute('class', 'form-control');
		$form->addCheckbox('visible', 'Show')->setAttribute('class', 'form-control')->setDefaultValue(1);
		
		$form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');
		
		$form->onSuccess[] = callback($this, 'categoryFormSubmitted');
		
		if($this->category){
			$form->setDefaults($this->category->toArray());
		}
		
		return $form;
	}
	
	public function categoryFormSubmitted(UI\Form $form){
		$values = $form->getValues();

		if($values->parent)
			$parent = $this->repository->find($values->parent);
		else
			$parent = NULL;
				
		$this->category->setTitle($values->title);
		$this->category->setVisible($values->visible);
		$this->category->setParent($parent);
		$this->category->setLanguage($this->state->language);
		$this->category->setPath('tmp value');
		
		$this->em->persist($this->category); // FIXME only if is new we have to persist entity, otherway it can be just flushed
		$this->em->flush();
		
		// creates path
		$path = $repo->getPath($this->category);
		$final = array();
		foreach($path as $p){
			if($p->getParent() != NULL) $final[] = $p->getSlug();
		}
		
		$this->category->setPath(implode('/', $final));
		
		$this->em->flush();

		$this->flashMessage($this->translation['Category has been added.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Categories:default');
	} 
	
	protected function createComponentCategoriesGrid($name){
		
		$parents = $this->repository->findBy(array(
			'parent' => NULL,
			'language' => $this->state->language->getId()
		));
		
		$prnts = array('' => $this->translation['Pick main']);
		foreach($parents as $p){
			$prnts[$p->getId()] = $p->getTitle();
		}
		
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Category', array(
			array('by' => 'root', 'dir' => 'ASC'), 
			array('by' => 'lft', 'dir' => 'ASC')
			),
			array(
				'language = ' . $this->state->language->getId()
			)
		);
		
		$grid->addColumn('title', 'Name')->setCustomRender(function($item){
			return str_repeat("-", $item->getLevel()) . $item->getTitle();
		});
		
		$grid->addColumnText('root', 'Structure')->setCustomRender(function($item){
			return '-';
		});
		
		$grid->addFilterSelect('root', 'Structure')->getControl()->setTranslator(NULL)->setItems($prnts);
		
		$grid->addColumn('visible', 'Visible')->setReplacement(array(
			'1' => 'Yes',
			NULL => 'No'
		));
		
		$grid->addAction("moveUp", "Move up");
		$grid->addAction("moveDown", "Move down");
		$grid->addAction("updateCategory", 'Edit')->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteCategory", 'Delete')->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
	}
	
	public function actionUpdateCategory($id){
		if($id) $this->category = $this->repository->find($id);
		else $this->category = new Category();
	}
	
	public function actionDeleteCategory($id){
		$this->category = $this->repository->find($id);
		$this->em->remove($this->category);
		$this->em->flush();
		
		$this->flashMessage($this->translation['Category has been removed.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Categories:default');
	}
	
	public function actionMoveUp($id){
		$this->category = $this->repository->find($id);
		
		if($this->category->getParent()){
			$this->repository->moveUp($this->category);
			
			$this->flashMessage($this->translation['Category has been moved up.'], 'success');
		}else{
			$this->flashMessage($this->translation['Category has not been moved up, because it is root category.'], 'warning');
		}
		
		if(!$this->isAjax())
			$this->redirect('Categories:default');
	}
	
	public function actionMoveDown($id){
		$this->category = $this->repository->find($id);
		
		if($this->category->getParent()){
			$this->repository->moveDown($this->category);
			
			$this->flashMessage($this->translation['Category has been moved down.'], 'success');
		}else{
			$this->flashMessage($this->translation['Category has not been moved up, because it is root category.'], 'warning');
		}
		
		if(!$this->isAjax())
			$this->redirect('Categories:default');
	}
	
	public function renderUpdateCategory($id){
		$this->reloadModalContent();
		
		$this->template->category = $this->category;
	}
	
	public function renderDefault($id){
		$this->reloadContent();
		
		$this->template->id = $id;
	}
}
