<?php

namespace AdminModule\EshopModule;

/**
 * Description of SettingsPresenter
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class SettingsPresenter extends \AdminModule\BasePresenter {
			
	/* @var \WebCMS\EshopModule\Doctrine\Payment */
	private $payment;
	
	/* @var \WebCMS\EshopModule\Doctrine\Shipping */
	private $shipping;
	
	/* @var \WebCMS\EshopModule\Doctrine\OrderState */
	private $status;
	
	private $paymentRepository;
	
	private $shippingRepository;
	
	private $statusRepository;
	
	protected function startup() {
		parent::startup();
		
		$this->paymentRepository = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Payment');
		$this->shippingRepository = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Shipping');
		$this->statusRepository = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\OrderState');
	}

	protected function beforeRender() {
		parent::beforeRender();
		
	}
	
	public function actionDefault($idPage){

	}
	
	public function createComponentSettingsForm(){
		
		$settings = array();
		$settings[] = $this->settings->get('Category body class', 'eshopModule', 'text', array());
		$settings[] = $this->settings->get('Product detail body class', 'eshopModule', 'text', array());
		$settings[] = $this->settings->get('Order saved email', 'eshopModule', 'textarea', array());
		
		return $this->createSettingsForm($settings);
	}
	
	public function renderDefault($idPage){
		$this->reloadContent();
		
		$this->template->config = $this->settings->getSection('eshopModule');
		$this->template->idPage = $idPage;
	}
	
	/* PAYMENTS */
	
	public function createComponentPaymentsGrid($name){
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Payment', NULL, array(
				'language = ' . $this->state->language->getId(),
			)
		);
		
		$grid->addColumnText('title', 'Name')->setFilterText();
		
		$grid->addColumnNumber('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\SystemHelper::price($item->getPrice());
		});
		
		$grid->addActionHref("updatePayment", 'Edit', 'updatePayment', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addActionHref("deletePayment", 'Delete', 'deletePayment', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
	}
	
	public function actionUpdatePayment($idPage, $id){
		if($id) $this->payment = $this->paymentRepository->find($id);
		else $this->payment = new \WebCMS\EshopModule\Doctrine\Payment;
	}
	
	public function renderUpdatePayment($idPage){
		$this->reloadModalContent();
		
		$this->template->idPage = $idPage;
	}
	
	public function createComponentPaymentForm($name){
		$form = $this->createForm();
		
		$form->addText('title', 'Title');
		$form->addText('price', 'Price');
		$form->addText('vat', 'Vat');
		$form->addSubmit('send', 'Save');
		
		$form->onSuccess[] = callback($this, 'paymentFormSubmitted');
		
		if($this->payment->getId())
			$form->setDefaults ($this->payment->toArray());
		
		return $form;
	}
	
	public function paymentFormSubmitted(\Nette\Application\UI\Form $form){
		$values = $form->getValues();
		
		$this->payment->setTitle($values->title);
		$this->payment->setPrice($values->price);
		$this->payment->setVat($values->vat);
		$this->payment->setLanguage($this->state->language);
		
		if(!$this->payment->getId())
			$this->em->persist($this->payment);
		
		$this->em->flush();
		
		$this->flashMessage('Payment has been saved.', 'success');
		
		if(!$this->isAjax())
			$this->redirect('Settings:default', array(
				'idPage' => $this->actualPage->getId()
			));
	}
	
	public function actionDeletePayment($id){
		$payment = $this->paymentRepository->find($id);
		
		$this->em->remove($payment);
		$this->em->flush();
		
		$this->flashMessage('Payment has been deleted.', 'success');
		
		if(!$this->isAjax())
			$this->redirect('Settings:default', array(
				'idPage' => $this->actualPage->getId()
			));
	}
	
	/* SHIPPINGS */
	
	public function createComponentShippingsGrid($name){
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Shipping', NULL, array(
				'language = ' . $this->state->language->getId(),
			)
		);
		
		$grid->addColumnText('title', 'Name')->setFilterText();
		
		$grid->addColumnNumber('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\SystemHelper::price($item->getPrice());
		});
		
		$grid->addActionHref("updateShipping", 'Edit', 'updateShipping', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addActionHref("deleteShipping", 'Delete', 'deleteShipping', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
	}
	
	public function actionUpdateShipping($idPage, $id){
		if($id) $this->shipping = $this->shippingRepository->find($id);
		else $this->shipping = new \WebCMS\EshopModule\Doctrine\Shipping;
	}
	
	public function renderUpdateShipping($idPage){
		$this->reloadModalContent();
		
		$this->template->idPage = $idPage;
	}
	
	public function createComponentShippingForm($name){
		$form = $this->createForm();
		
		$form->addText('title', 'Title');
		$form->addText('price', 'Price');
		$form->addText('vat', 'Vat');
		$form->addSubmit('send', 'Save');
		
		$form->onSuccess[] = callback($this, 'shippingFormSubmitted');
		
		if($this->shipping->getId())
			$form->setDefaults ($this->shipping->toArray());
		
		return $form;
	}
	
	public function shippingFormSubmitted(\Nette\Application\UI\Form $form){
		$values = $form->getValues();
		
		$this->shipping->setTitle($values->title);
		$this->shipping->setPrice($values->price);
		$this->shipping->setVat($values->vat);
		$this->shipping->setLanguage($this->state->language);
		
		if(!$this->shipping->getId())
			$this->em->persist($this->shipping);
		
		$this->em->flush();
		
		$this->flashMessage('Shipping has been saved.', 'success');
		
		if(!$this->isAjax())
			$this->redirect('Settings:default', array(
				'idPage' => $this->actualPage->getId()
			));
	}
	
	public function actionDeleteShipping($id){
		$shipping = $this->shippingRepository->find($id);
		
		$this->em->remove($shipping);
		$this->em->flush();
		
		$this->flashMessage('Shipping has been deleted.', 'success');
		
		if(!$this->isAjax())
			$this->redirect('Settings:default', array(
				'idPage' => $this->actualPage->getId()
			));
	}
	
	/* ORDER STATUS */
	
	public function createComponentStatusesGrid($name){
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\OrderState', NULL, array(
				'language = ' . $this->state->language->getId(),
			)
		);
		
		$grid->addColumnText('title', 'Name')->setFilterText();
		$grid->addColumnText('default', 'Default')->setReplacement(array(
			'0' => 'No',
			'1' => 'Yes'
		))->setFilterText();
		
		$grid->addActionHref("updateStatus", 'Edit', 'updateStatus', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addActionHref("deleteStatus", 'Delete', 'deleteStatus', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

		return $grid;
	}
	
	public function actionUpdateStatus($idPage, $id){
		if($id) $this->status = $this->statusRepository->find($id);
		else $this->status = new \WebCMS\EshopModule\Doctrine\OrderState;
	}
	
	public function renderUpdateStatus($idPage){
		$this->reloadModalContent();
		
		$this->template->idPage = $idPage;
	}
	
	public function createComponentStatusForm($name){
		$form = $this->createForm();
		
		$form->addText('title', 'Title');
		$form->addCheckbox('default', 'Default');
		$form->addSubmit('send', 'Save');
		
		$form->onSuccess[] = callback($this, 'statusFormSubmitted');
		
		if($this->status->getId())
			$form->setDefaults($this->status->toArray());
		
		return $form;
	}
	
	public function statusFormSubmitted(\Nette\Application\UI\Form $form){
		$values = $form->getValues();
		
		if($values->default){
			$all = $this->statusRepository->findBy(array(
				'language' => $this->state->language
			));
			
			foreach($all as $item){
				$item->setDefault(FALSE);
			}
		}
		
		$this->status->setTitle($values->title);
		$this->status->setDefault($values->default);
		$this->status->setLanguage($this->state->language);
		
		if(!$this->status->getId())
			$this->em->persist($this->status);
		
		$this->em->flush();
		
		$this->flashMessage('Status has been saved.', 'success');
		
		if(!$this->isAjax())
			$this->redirect('Settings:default', array(
				'idPage' => $this->actualPage->getId()
			));
	}
	
	public function actionDeleteStatus($id){
		$status = $this->statusRepository->find($id);
		
		$this->em->remove($status);
		$this->em->flush();
		
		$this->flashMessage('Status has been deleted.', 'success');
		
		if(!$this->isAjax())
			$this->redirect('Settings:default', array(
				'idPage' => $this->actualPage->getId()
			));
	}
}