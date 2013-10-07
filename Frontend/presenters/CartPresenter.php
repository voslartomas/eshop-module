<?php

namespace FrontendModule\EshopModule;

/**
 * This presenter handle all actions in shopping cart.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class CartPresenter extends BasePresenter{
	/* \Nette\Http\SessionSection */
	private $eshopSession;
	
	/* \WebCMS\EshopModule\Doctrine\Order */
	private $order;
	
	/* Repository */
	private $productRepository;
	
	protected function startup() {
		parent::startup();
		
		$this->productRepository = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Product');
		
		$this->eshopSession = $this->session->getSection('eshop' . $this->language->getId());
		
		if(!$this->eshopSession->offsetExists('order')){
			$this->order = new \WebCMS\EshopModule\Doctrine\Order;
			$this->saveOrderState();
		}else{
			$this->order = $this->eshopSession->order;
		}
		
		$this->template->order = $this->order;
	}
	
	public function actionDefault($id){
		if(array_key_exists('itemId', $_POST)){
			$this->addCartItem($_POST['itemId'], $_POST['quantity']);
		}
	}
	
	public function renderDefault($id){
		
		$this->template->payments = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Payment')->findBy(array(
			'language' => $this->language
		));
		$this->template->shippings = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Shipping')->findBy(array(
			'language' => $this->language
		));
		$this->template->id = $id;
	}
	
	public function createComponentCartForm(){
		$form = $this->createForm();
		
		$form->getElementPrototype()->action = $this->link(':Frontend:Eshop:Cart:default', array(
			'id' => $this->actualPage->getId(),
			'path' => $this->actualPage->getPath(),
			'abbr' => $this->abbr,
			'do' => 'cartForm-submit'
		));
		
		$form->addGroup('Delivery');
		$form->addText('firstname', 'Firstname')->setRequired('Please fill in the firstname.');
		$form->addText('lastname', 'Lastname')->setRequired('Please fill in the lastname.');
		$form->addText('email', 'Email')->setRequired('Please fill in the email.')->addRule(\Nette\Forms\Form::EMAIL, 'This is not correct email address.');
		$form->addText('phone', 'Phone')->setRequired('Please fill in the phone.');
		$form->addText('street', 'Street')->setRequired('Please fill in the street.');
		$form->addText('city', 'City')->setRequired('Please fill in the city.');
		$form->addText('postcode', 'Postcode')->setRequired('Please fill in the postcode.');
		
		// invoice data
		$form->addGroup('Invoice');
		$form->addText('invoiceCompany', 'Company');
		$form->addText('invoiceNo', 'No.');
		$form->addText('invoiceVatNo', 'Tax No.');
		$form->addText('invoiceEmail', 'Email');
		$form->addText('invoicePhone', 'Phone');
		$form->addText('invoiceStreet', 'Street');
		$form->addText('invoiceCity', 'City');
		$form->addText('invoicePostcode', 'Postcode');
		
		$form->addSubmit('send', 'Send order');
		
		$form->onSuccess[] = callback($this, 'cartFormSubmitted');

		$form->setDefaults($this->order->toArray());
		
		return $form;
	}
	
	public function cartFormSubmitted($form){
		$values = $form->getValues();
		
		$this->order->setFirstname($values->firstname);
		$this->order->setLastname($values->lastname);
		$this->order->setEmail($values->email);
		$this->order->setPhone($values->phone);
		$this->order->setStreet($values->street);
		$this->order->setCity($values->city);
		$this->order->setPostCode($values->postcode);
		
		$this->order->setInvoiceCompany($values->invoiceCompany);
		$this->order->setInvoiceNo($values->invoiceNo);
		$this->order->setInvoiceVatNo($values->invoiceVatNo);
		$this->order->setInvoiceEmail($values->invoiceEmail);
		$this->order->setInvoicePhone($values->invoicePhone);
		$this->order->setInvoiceStreet($values->invoiceStreet);
		$this->order->setInvoiceCity($values->invoiceCity);
		$this->order->setInvoicePostcode($values->invoicePostcode);
		
		if(array_key_exists('payment', $_POST)) 
			$this->order->setPayment($_POST['payment']);
		if(array_key_exists('shipping', $_POST)) 
			$this->order->setShipping($_POST['shipping']);
		
		$this->order->getPriceTotal(); // TODO no tohle je trochu blbe volat ne? alespon globalni funkci pro vsechny ceny
		
		$this->saveOrderState();
		
		if($this->requiredFilled($values)){
			
			$this->order->setLanguage($this->language);
			
			// set default order state
			$status = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\OrderState')->findOneBy(array(
				'language' => $this->language,
				'default' => TRUE
			));
			
			$this->order->setStatus($status);
			
			// add shipping and payment as order items
			$p = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Payment')->find($this->order->getPayment());
			
			$payment = new \WebCMS\EshopModule\Doctrine\OrderItem;
			$payment->setName($p->getTitle());
			$payment->setPrice($p->getPrice());
			$payment->setVat($p->getVat());
			$payment->setQuantity(1);
			
			$s = $this->em->getRepository('\WebCMS\EshopModule\Doctrine\Shipping')->find($this->order->getShipping());
			
			$shipping = new \WebCMS\EshopModule\Doctrine\OrderItem;
			$shipping->setName($s->getTitle());
			$shipping->setPrice($s->getPrice());
			$shipping->setVat($s->getVat());
			$shipping->setQuantity(1);
			
			$this->order->addItem($payment);
			$this->order->addItem($shipping);
			
			// send info email with summary
			$this->sendSummaryEmail($values);
			
			$this->em->persist($this->order);
			$this->em->flush();
			$this->order = new \WebCMS\EshopModule\Doctrine\Order;
			$this->saveOrderState();
			
			$this->flashMessage($this->translation['Order has been sent. On given email has been sent email with summary.'], 'success');
		}else{
			$this->flashMessage($this->translation['Please fill all required data.'], 'danger');
		}
		
		$this->redirectThis();
	}
	
	private function requiredFilled($values){
		
		$payment = $this->order->getPayment();
		$shipping = $this->order->getShipping();
		
		if(!empty($values->firstname) &&
			!empty($values->lastname) &&
			!empty($values->email) &&
			!empty($values->phone) &&
			!empty($values->street) &&
			!empty($values->city) &&
			!empty($values->postcode) &&
			!empty($payment) &&
			!empty($shipping)
		){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	private function sendSummaryEmail($values){
		
		// order items
		$items = '';
		foreach($this->order->getItems() as $item){
			$items .= $item->getName() . ' ' . $item->getQuantity() . ' x ' . \WebCMS\SystemHelper::price($item->getPrice()) . ' = ' . \WebCMS\SystemHelper::price($item->getQuantity() * $item->getPrice()) . '<br />';
		}
		
		// email
		$email = new \Nette\Mail\Message;
		$email->addTo($values->email);
		$email->setFrom($this->settings->get('Info email', \WebCMS\Settings::SECTION_BASIC)->getValue());
		$email->setSubject($this->translation['Order was created.']);
		$email->setHtmlBody($this->settings->get('Order saved email', 'eshopModule')->getValue(FALSE, 
				array(
					'[FIRSTNAME]',
					'[LASTNAME]',
					'[EMAIL]',
					'[PHONE]',
					'[STREET]',
					'[CITY]',
					'[POSTCODE]',
					'[TOTAL_PRICE]',
					'[ORDER_ITEMS]'
				),
				array(
					$values->firstname,
					$values->lastname,
					$values->email,
					$values->phone,
					$values->street,
					$values->city,
					$values->postcode,
					\WebCMS\SystemHelper::price($this->order->getPriceTotal()),
					$items
				)
			));
		
		$email->send();
		
	}
	
	/**
	 * Saves order into Session.
	 */
	private function saveOrderState(){
		$this->eshopSession->order = $this->order;
	}
	
	/**
	 * Remove item from shopping cart.
	 * @param type $itemId
	 */
	public function actionDeleteCartItem($itemId){
		foreach($this->order->getItems() as $item){
			if($itemId === $item->getItemId()){
				$this->order->removeItem($item);
			}
		}
		
		$this->flashMessage($this->translation['Item has been removed from cart.'], 'success');
		$this->redirectThis();
	}
	
	public function actionSetQuantity($itemId, $quantity){
		if($quantity > 0){
		
			foreach($this->order->getItems() as $item){
				if($itemId === $item->getItemId()){
					$item->setQuantity($quantity);
				}
			}

			$this->flashMessage($this->translation['New quantity for item has been set.'], 'success');
		
		}else{
			$this->flashMessage($this->translation['Quantity must be greater then zero.'], 'danger');
		}
		$this->redirectThis();
	}
	
	/**
	 * Add item into shopping cart.
	 * @param type $itemId
	 * @param type $quantity
	 */
	private function addCartItem($itemId, $quantity){
		if(!$this->existsInCart($itemId)){
			$product = $this->productRepository->find($itemId);

			$item = new \WebCMS\EshopModule\Doctrine\OrderItem;
			$item->setItemId($itemId);
			$item->setName($product->getTitle());
			$item->setQuantity($quantity);
			$item->setPrice($product->getPrice());
			$item->setVat($product->getVat());

			$this->order->addItem($item);
		}else{
			$this->flashMessage($this->translation['This item has been already added.'], 'danger');
		}
		
		$this->redirectThis();
	}
	
	/**
	 * Checks whether item exists in shopping cart.
	 * @param type $itemId
	 * @return boolean
	 */
	private function existsInCart($itemId){
		foreach($this->order->getItems() as $item){
			if($itemId === $item->getItemId())
				return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Redirect to self.
	 */
	private function redirectThis(){
		
		$this->redirect(':Frontend:Eshop:Cart:default', array(
			'id' => $this->actualPage->getId(),
			'path' => $this->actualPage->getPath(),
			'abbr' => $this->abbr
				));
	}
	
	public function cartBox($context, $fromPage){
		$eshopSession = $context->session->getSection('eshop' . $context->language->getId());
		$order = $eshopSession->order;
		
		$template = $context->createTemplate();
		$template->setFile('../app/templates/eshop-module/boxes/cartBox.latte');
		$template->link = $context->link(':Frontend:Eshop:Cart:default', array(
			'id' => $fromPage->getId(),
			'path' => $fromPage->getPath(),
			'abbr' => $context->abbr
				));
		
		if(is_object($order)){
			$template->priceTotal = $order->getPriceTotal();
			
			$count = 0;
			foreach($order->getItems() as $item){
				$count += $item->getQuantity();
			}
			
			$template->itemsCount = $count;
		}else{
			$template->priceTotal = 0;
			$template->itemsCount = 0;
		}
		
		return $template;
	}
}
