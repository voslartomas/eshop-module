<?php

namespace AdminModule\EshopModule;

/**
 * Description of ParametersPresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class ParametersPresenter extends BasePresenter
{
    private $repository;

    private $valueRepository;

    /* @var \WebCMS\EshopModule\Doctrine\Parameter */
    private $parameter;

    /** @var \WebCMS\EshopModule\Doctrine\ParameterValue */
    private $value;

    protected function beforeRender()
    {
        parent::beforeRender();
    }

    public function renderDefault($idPage)
    {
        $this->reloadContent();

        $this->template->idPage = $idPage;
    }

    protected function startup()
    {
        parent::startup();

        $this->valueRepository = $this->em->getRepository('WebCMS\EshopModule\Doctrine\ParameterValue');
        $this->repository = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Parameter');
    }

    protected function createComponentParametersGrid($name)
    {
        $grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Parameter');

        $grid->addColumnText('name', 'Name')->setSortable()->setFilterText();

        $grid->addActionHref("updateParameter", 'Edit', 'updateParameter', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax'));
        $grid->addActionHref("deleteParameter", 'Delete', 'deleteParameter', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary btn-danger'));

        return $grid;
    }

    public function actionDefault($idPage)
    {
    }

    public function actionDeleteParameter($id, $idPage)
    {
        $value = $this->repository->find($id);

        $this->em->remove($value);
        $this->em->flush();

        $this->flashMessage('Parameter has been removed.', 'success');

        $this->redirect('default', array(
            'idPage' => $this->actualPage->getId(),
        ));
    }

    public function actionUpdateParameter($idPage, $id)
    {
        $this->reloadContent();

        if ($id) {
            $this->parameter = $this->repository->find($id);
        } else {
            $this->parameter = new \WebCMS\EshopModule\Doctrine\Parameter();
        }

        $this->template->parameter = $this->parameter;
        $this->template->idPage = $idPage;
    }

    public function createComponentParameterForm()
    {
        $form = $this->createForm();

        $form->addText('name', 'Name')->setRequired('Fill in parameter.')->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'Save');

        $form->onSuccess[] = callback($this, 'parameterFormSubmitted');

        $form->setDefaults($this->parameter->toArray());

        return $form;
    }

    public function parameterFormSubmitted($form)
    {
        $values = $form->getValues();

        $this->parameter->setName($values->name);

        if (!$this->parameter->getId()) {
            $this->em->persist($this->parameter);
        }

        $this->em->flush();
        $this->flashMessage('Parameter value has been saved.', 'success');

        $this->redirect('this', array(
            'idPage' => $this->actualPage->getId(),
            'id' => $this->parameter->getId(),
        ));
    }

    protected function createComponentParametersValuesGrid($name)
    {
        $grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\ParameterValue', null,
            array(
                'parameter = '.$this->parameter->getId(),
            ));

        $grid->addColumnText('value', 'Value')->setSortable()->setFilterText();

        $grid->addActionHref("updateValue", 'Edit', 'updateValue', array('idPage' => $this->actualPage->getId(), 'parameter' => $this->parameter->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
        $grid->addActionHref("deleteValue", 'Delete', 'deleteValue', array('idPage' => $this->actualPage->getId(), 'parameter' => $this->parameter->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary btn-danger'));

        return $grid;
    }

    public function actionUpdateValue($id, $idPage, $parameter)
    {
        $this->reloadModalContent();

        if ($id) {
            $this->value = $this->valueRepository->find($id);
        } else {
            $this->value = new \WebCMS\EshopModule\Doctrine\ParameterValue();
        }

        if ($parameter) {
            $this->parameter = $this->repository->find($parameter);
        } else {
            $this->parameter = new \WebCMS\EshopModule\Doctrine\Parameter();
        }

        $this->template->idPage = $idPage;
    }

    public function actionDeleteValue($id, $idPage, $parameter)
    {
        $value = $this->valueRepository->find($id);

        $this->em->remove($value);
        $this->em->flush();

        $this->flashMessage('Parameter value has been removed.', 'success');

        $this->redirect('updateParameter', array(
            'idPage' => $this->actualPage->getId(),
            'id' => $parameter,
        ));
    }

    public function createComponentValueForm()
    {
        $form = $this->createForm();
        $form->addText('value', 'value');

        $form->addSubmit('send', 'Save');
        $form->onSuccess[] = callback($this, 'valueFormSubmitted');

        $form->setDefaults($this->value->toArray());

        return $form;
    }

    public function valueFormSubmitted($form)
    {
        $values = $form->getValues();

        $this->value->setValue($values->value);
        $this->value->setParameter($this->parameter);

        if (!$this->value->getId()) {
            $this->em->persist($this->value);
        }

        $this->em->flush();
        $this->flashMessage('Parameter value has been saved.', 'success');

        $this->redirect('updateParameter', array(
            'idPage' => $this->actualPage->getId(),
            'id' => $this->parameter->getId(),
        ));
    }
}
