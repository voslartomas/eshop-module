<?php

namespace AdminModule\EshopModule;

use Nette\Application\UI;

/**
 * Description of CartPresenter
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class CartPresenter extends BasePresenter{
	
	private $repository;
	
	private $repositoryOrderItems;
	
	private $order;
	
	private $orderItem;
	
	public function startup(){
		parent::startup();
		
		$this->repository = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Order');
		$this->repositoryOrderItems = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\OrderItem');
	}
	
	public function actionDefault($idPage){}
	
	protected function beforeRender(){
		parent::beforeRender();	
	}
	
	public function renderDefault($idPage){
		$this->reloadContent();
		
		$this->template->idPage = $idPage;
	}
	
	protected function createComponentOrdersGrid($name){
				
		$grid = $this->createGrid($this, $name, '\WebCMS\EshopModule\Doctrine\Order');
		
		$statuses = $this->em->getRepository('WebCMS\EshopModule\Doctrine\OrderState')->findBy(array(
			'language' => $this->state->language
		));
		
		$selectStatuses = array(NULL => 'Pick status');
		$defaultStatus = NULL;
		foreach($statuses as $s){
			if($s->getDefault())
				$defaultStatus = $s;
				
			$selectStatuses[$s->getId()] = $s->getTitle();
		}
		
		$grid->addColumn('created', 'Created')->setCustomRender(function($item){
			return $item->getCreated()->format('d.m.Y H:i:s');
		})->setSortable()->setFilter();
		
		if(is_object($defaultStatus)){
			$grid->addColumn('status', 'status')->setCustomRender(function($item){
				return is_object($item->getStatus()) ? $item->getStatus()->getTitle() : '';
			})->setSortable()->setFilterSelect($selectStatuses)/*->setDefaultValue($defaultStatus->getId())*/;
		}else{
			$grid->addColumn('status', 'status')->setCustomRender(function($item){
				return is_object($item->getStatus()) ? $item->getStatus()->getTitle() : '';
			})->setSortable()->setFilterSelect($selectStatuses);
		}
		
		$grid->addColumn('firstname', 'Firstname')->setSortable()->setFilter();
		$grid->addColumn('lastname', 'Lastname')->setSortable()->setFilter();
		$grid->addColumn('email', 'Email')->setSortable()->setFilter();
		$grid->addColumn('street', 'Street')->setSortable()->setFilter();
		$grid->addColumn('city', 'City')->setSortable()->setFilter();
		$grid->addColumn('postcode', 'Postcode')->setSortable()->setFilterNumber();
		
		$grid->addColumn('priceTotal', 'Price total')->setCustomRender(function($item){
			return \WebCMS\PriceFormatter::format($item->getPriceTotal());
		})->setSortable()->setFilterNumber();
			
		$defaults = array('created' => 'DESC');
		$grid->setDefaultSort($defaults);
		
		$operations = array();
		foreach($statuses as $status){
			$operations['setStatus-' . $status->getId()]  = 'Set status - ' . $status->getTitle();
		}
		
		$grid->setOperations($operations, function($operation, $ids) { 
			$status = explode('-', $operation);
			
			$status = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\OrderState')->find($status[1]);
			
			$qb = $this->em->createQueryBuilder();
			$qb->select('o')->from('\WebCMS\EshopModule\Doctrine\Order', 'o')->add('where', $qb->expr()->in('o.id', '?1'));
			$qb->setParameter(1, $ids);
			
			$result = $qb->getQuery()->getResult();
			
			foreach($result as $item){
				$item->setStatus($status);
			}
			
			$this->em->flush();
		});
				
		$grid->addAction("editOrder", 'Edit', \Grido\Components\Actions\Action::TYPE_HREF, 'editOrder', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary ajax'));
		$grid->addAction("deleteOrder", 'Delete', \Grido\Components\Actions\Action::TYPE_HREF, 'deleteOrder', array('idPage' => $this->actualPage->getId()))->getElementPrototype()->addAttributes(array('class' => 'btn btn-primary btn-danger'));
		
		return $grid;
	}
	
	public function actionDeleteOrder($id, $idPage){
		$order = $this->repository->find($id);
		$this->em->remove($order);
		$this->em->flush();
		
		$this->flashMessage($this->translation['Order has been deleted.'], 'success');
		if(!$this->isAjax())
			$this->redirect('Cart:default', array(
				'idPage' => $idPage
			));
	}
	
	public function actionEditOrder($id, $idPage){
		$this->reloadContent();
		
		$this->order = $this->repository->find($id);
		$this->template->order = $this->order;
	}
	
	public function renderEditOrder($idPage){
		$this->template->idPage = $idPage;
	}
	
	public function createComponentOrderForm($name){
		$form = $this->createForm();
		
		$statuses = $this->em->getRepository('WebCMS\EshopModule\Doctrine\OrderState')->findBy(array(
			'language' => $this->state->language
		));
		
		$selectStatuses = array(NULL => 'Pick status');
		$defaultStatus = NULL;
		foreach($statuses as $s){
			if($s->getDefault())
				$defaultStatus = $s;
				
			$selectStatuses[$s->getId()] = $s->getTitle();
		}
		
		$form->addSelect('status', 'Status', $selectStatuses);
		
		$form->addText('firstname', 'Firstname');
		$form->addText('lastname', 'Lastname');
		$form->addText('email', 'Email');
		$form->addText('phone', 'Phone');
		$form->addText('street', 'Street');
		$form->addText('city', 'City');
		$form->addText('postcode', 'Postcode');
		
		$form->addText('invoiceFirstname', 'Firstname');
		$form->addText('invoiceLastname', 'Lastname');
		$form->addText('invoiceEmail', 'Email');
		$form->addText('invoicePhone', 'Phone');
		$form->addText('invoiceStreet', 'Street');
		$form->addText('invoiceCity', 'City');
		$form->addText('invoicePostcode', 'Postcode');
		
		$form->addSubmit('send', 'Save');
		$form->onSuccess[] = callback($this, 'orderFormSubmitted');
		
		if($this->order){
			$form->setDefaults($this->order->toArray());
		}
		
		return $form;
	}
	
	public function orderFormSubmitted($form){
		
		$values = $form->getValues();
		
		$status = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\OrderState')->find($values->status);
		
		$this->order->setStatus($status);
		$this->order->setFirstname($values->firstname);
		$this->order->setLastname($values->lastname);
		$this->order->setEmail($values->email);
		$this->order->setPhone($values->phone);
		$this->order->setStreet($values->street);
		$this->order->setCity($values->city);
		$this->order->setPostcode($values->postcode);
		
		$this->order->setInvoiceFirstname($values->invoiceFirstname);
		$this->order->setInvoiceLastname($values->invoiceLastname);
		$this->order->setInvoiceEmail($values->invoiceEmail);
		$this->order->setInvoicePhone($values->invoicePhone);
		$this->order->setInvoiceStreet($values->invoiceStreet);
		$this->order->setInvoiceCity($values->invoiceCity);
		$this->order->setInvoicePostcode($values->invoicePostcode);
		
		$this->em->flush();
		
		$this->flashMessage($this->translation['Order has been saved.'], 'success');
		$this->redirect('Cart:editOrder', array(
				'idPage' => $this->actualPage->getId(),
				'id' => $this->getParam('id')
			));
	}
	
	public function createComponentOrderItemForm($name){
		$form = $this->createForm();
		
		$form->addText('name', 'Name');
		$form->addText('price', 'Price');
		$form->addText('vat', 'Vat');
		$form->addText('quantity', 'Quantity');
		
		$form->addSubmit('send', 'Save item');
		
		if(is_object($this->orderItem))
			$form->setDefaults ($this->orderItem->toArray());
		
		$form->onSuccess[] = callback($this, 'orderItemFormSubmitted');
		
		return $form;
	}
	
	public function orderItemFormSubmitted($form){
		
		$values = $form->getValues();
		
		if(!$this->orderItem){
			$this->orderItem = new \WebCMS\EshopModule\Doctrine\OrderItem;
			
			$order = $this->repository->find($this->getParameter('id'));
			$this->orderItem->setOrder($order);
		}
		
		$this->orderItem->setName($values->name);
		$this->orderItem->setPrice($values->price);
		$this->orderItem->setVat($values->vat);
		$this->orderItem->setQuantity($values->quantity);
		
		if(!$this->orderItem->getId())
			$this->em->persist($this->orderItem);
			
		$this->em->flush();
		
		$this->flashMessage($this->translation['Order item has been saved.'], 'success');
		$this->redirect('Cart:editOrder', array(
				'idPage' => $this->actualPage->getId(),
				'id' => $this->getParam('id')
			));
	}
	
	public function actionAddOrderItem($idItem, $idPage, $id){
		$this->reloadModalContent();
		
		
		
		if($idItem) $this->orderItem = $this->repositoryOrderItems->find($idItem);
	}
	
	public function actionDeleteOrderItem($idItem, $idPage, $id){
		$item = $this->repositoryOrderItems->find($idItem);
		
		$this->em->remove($item);
		$this->em->flush();
		
		$this->flashMessage($this->translation['Order item has been deleted.'], 'success');
		
		if(!$this->isAjax())
			$this->redirect('Cart:editOrder', array(
				'idPage' => $idPage,
				'id' => $id
			));
	}
}
