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
	
	private $paymentRepository;
	
	private $shippingRepository;
	
	protected function startup() {
		parent::startup();
		
		$this->paymentRepository = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Payment');
		$this->shippingRepository = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Shipping');
	}

	protected function beforeRender() {
		parent::beforeRender();
		
	}
	
	public function actionDefault($idPage){

	}
	
	public function createComponentSettingsForm(){
		
		$settings = array();
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
		
		$grid->addColumn('title', 'Name')->setFilter();
		
		$grid->addColumn('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\SystemHelper::price($item->getPrice());
		});
		
		$grid->addAction("updatePayment", 'Edit', \Grido\Components\Actions\Action::TYPE_HREF, 'updatePayment', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deletePayment", 'Delete', \Grido\Components\Actions\Action::TYPE_HREF, 'deletePayment', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

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
		
		$grid->addColumn('title', 'Name')->setFilter();
		
		$grid->addColumn('price', 'Price')->setCustomRender(function($item){
			return \WebCMS\SystemHelper::price($item->getPrice());
		});
		
		$grid->addAction("updateShipping", 'Edit', \Grido\Components\Actions\Action::TYPE_HREF, 'updateShipping', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax', 'data-toggle' => 'modal', 'data-target' => '#myModal', 'data-remote' => 'false'));
		$grid->addAction("deleteShipping", 'Delete', \Grido\Components\Actions\Action::TYPE_HREF, 'deleteShipping', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-danger', 'data-confirm' => 'Are you sure you want to delete this item?'));

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
}