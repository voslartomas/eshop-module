<?php

    namespace AdminModule\EshopModule;

use Nette\Application\UI;

    /**
     * Description of CategoriesPresenter
     *
     * @author Tomáš Voslař <tomas.voslar at webcook.cz>
     */
    class CategoriesPresenter extends BasePresenter {

	private $repository;

	/* @var \WebCMS\EshopModule\Doctrine\Category */
	private $category;

	protected function beforeRender() {
	    parent::beforeRender();
	}

	protected function startup() {
	    parent::startup();

	    $this->repository = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Category');
	}

	protected function createComponentCategoryForm() {

	    $hierarchy = $this->repository->getTreeForSelect(array(
		array('by' => 'root', 'dir' => 'ASC'),
		array('by' => 'lft', 'dir' => 'ASC')
		), array(
		'language = ' . $this->state->language->getId()
	    ));

	    $hierarchy = $hierarchy;

	    $form = $this->createForm();
	    $form->addText('title', 'Name')->setAttribute('class', 'form-control');
	    $form->addText('slug', 'SEO adresa url')->setAttribute('class', 'form-control');
	    $form->addText('metaTitle', 'SEO title')->setAttribute('class', 'form-control');
	    $form->addText('metaDescription', 'SEO description')->setAttribute('class', 'form-control');
	    $form->addText('metaKeywords', 'SEO keywords')->setAttribute('class', 'form-control');
	    $form->addSelect('parent', 'Parent')->setTranslator(NULL)->setItems($hierarchy)->setAttribute('class', 'form-control');
	    $form->addCheckbox('visible', 'Show')->setDefaultValue(1);
	    $form->addCheckbox('favourite', 'Favourite');
	    $form->addTextarea('description', 'Description')->setAttribute('class', 'editor');

	    $form->addSubmit('save', 'Save')->setAttribute('class', 'btn btn-success');

	    $form->onSuccess[] = callback($this, 'categoryFormSubmitted');

	    if ($this->category->getId()) {
		$form->setDefaults($this->category->toArray());
	    }

	    return $form;
	}

	public function actionUpdateCategory($idPage, $id) {
	    if ($id)
		$this->category = $this->repository->find($id);
	    else
		$this->category = new \WebCMS\EshopModule\Doctrine\Category();
	}

	public function categoryFormSubmitted(UI\Form $form) {
	    $values = $form->getValues();

	    if ($values->parent)
		$parent = $this->repository->find($values->parent);
	    else
		$parent = NULL;

	    $this->category->setTitle($values->title);

	    if (!empty($values->slug)) {
		$this->category->setSlug($values->slug);
	    }

	    $this->category->setMetaTitle($values->metaTitle);
	    $this->category->setMetaDescription($values->metaDescription);
	    $this->category->setMetaKeywords($values->metaKeywords);
	    $this->category->setVisible($values->visible);
	    $this->category->setParent($parent);
	    $this->category->setFavourite($values->favourite);
	    $this->category->setDescription($values->description);
	    $this->category->setLanguage($this->state->language);
	    $this->category->setPath('tmp value');

	    if (array_key_exists('files', $_POST))
		$this->category->setPicture($_POST['files'][0]);
	    else
		$this->category->setPicture(NULL);

	    if (!$this->category->getId())
		$this->em->persist($this->category); // FIXME only if is new we have to persist entity, otherway it can be just flushed
	    $this->em->flush();

	    // FIXME it is necessary to recalculate childrens path when slug is changed or is changed parent!
	    // creates path
	    $path = $this->repository->getPath($this->category);
	    $final = array();
	    foreach ($path as $p) {
		if ($p->getParent() != NULL)
		    $final[] = $p->getSlug();
	    }

	    $this->category->setPath(implode('/', $final));

	    $this->em->flush();

	    $this->flashMessage('Category has been added.', 'success');

	    $this->handleGenerateXml();

	    if (!$this->isAjax())
		$this->redirect('Categories:default', array('idPage' => $this->actualPage->getId()));
	}

	protected function createComponentCategoriesGrid($name) {

	    $grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Category', array(
		array('by' => 'root', 'dir' => 'ASC'),
		array('by' => 'lft', 'dir' => 'ASC')
		), array(
		'language = ' . $this->state->language->getId(),
		'level > 0'
		)
	    );

	    $grid->addColumnText('title', 'Name')->setCustomRender(function($item) {
		return str_repeat("-", $item->getLevel()) . $item->getTitle();
	    })->setFilterText();

	    $grid->addColumnText('visible', 'Visible')->setReplacement(array(
		'1' => 'Yes',
		NULL => 'No'
	    ));

	    $grid->addActionHref("moveUp", "Move up", 'moveUp', array('idPage' => $this->actualPage->getId()));
	    $grid->addActionHref("moveDown", "Move down", 'moveDown', array('idPage' => $this->actualPage->getId()));
	    $grid->addActionHref("updateCategory", 'Edit', 'updateCategory', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax'));
	    $grid->addActionHref("deleteCategory", 'Delete', 'deleteCategory', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

	    return $grid;
	}

	public function actionDeleteCategory($idPage, $id) {
	    $this->category = $this->repository->find($id);
	    $this->em->remove($this->category);
	    $this->em->flush();

	    $this->flashMessage('Category has been removed.', 'success');

	    if (!$this->isAjax())
		$this->redirect('Categories:default', array('idPage' => $idPage));
	}

	public function actionMoveUp($id, $idPage) {
	    $this->category = $this->repository->find($id);

	    $this->repository->moveUp($this->category);
	    $this->flashMessage('Category has been moved up.', 'success');

	    if (!$this->isAjax())
		$this->redirect('Categories:default', array('idPage' => $idPage));
	}

	public function actionMoveDown($id, $idPage) {
	    $this->category = $this->repository->find($id);

	    $this->repository->moveDown($this->category);
	    $this->flashMessage('Category has been moved down.', 'success');

	    if (!$this->isAjax())
		$this->redirect('Categories:default', array('idPage' => $idPage));
	}

	public function actionDefault($idPage) {
	    $main = $this->repository->findBy(array(
		'title' => 'Main',
		'level' => 0,
		'language' => $this->state->language
	    ));

	    if (count($main) == 0) {
		$main = new \WebCMS\EshopModule\Doctrine\Category;
		$main->setTitle('Main');
		$main->setLanguage($this->state->language);
		$main->setPath('');
		$main->setVisible(FALSE);

		$this->em->persist($main);
		$this->em->flush();
	    }
	}

	public function renderUpdateCategory($idPage) {
	    $this->reloadContent();

	    $this->template->category = $this->category;
	    $this->template->idPage = $idPage;
	}

	public function renderDefault($idPage) {
	    $this->reloadContent();

	    $this->template->idPage = $idPage;
	}

    }
    