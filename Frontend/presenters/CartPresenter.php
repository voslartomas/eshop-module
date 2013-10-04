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
		
		$this->eshopSession = $this->session->getSection('eshop');
		
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
		
		
		$this->template->id = $id;
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
		
		$this->flashMessage('Item has been removed from cart.', 'success');
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
}
