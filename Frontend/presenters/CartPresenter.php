<?php

namespace FrontendModule\EshopModule;

/**
 * Description of BasketPresenter
 *
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class CartPresenter extends BasePresenter{
	
	private $eshopSession;
	
	private $order;
	
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
	
	private function saveOrderState(){
		$this->eshopSession->order = $this->order;
	}
	
	public function actionDefault($id){
		if(array_key_exists('itemId', $_POST)){
			$this->addCartItem($_POST['itemId'], $_POST['quantity']);
		}
	}
	
	public function renderDefault($id){
		
		
		$this->template->id = $id;
	}
	
	public function actionDeleteCartItem($itemId){
		foreach($this->order->getItems() as $item){
			if($itemId === $item->getItemId()){
				$this->order->removeItem($item);
			}
		}
		
		$this->flashMessage('Item has been removed from cart.', 'success');
		$this->redirectThis();
	}
	
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
			$this->saveOrderState();
		}else{
			$this->flashMessage($this->translation['This item has been already added.'], 'danger');
		}
		
		$this->redirectThis();
	}
	
	private function existsInCart($itemId){
		foreach($this->order->getItems() as $item){
			if($itemId === $item->getItemId())
				return TRUE;
		}
		
		return FALSE;
	}
	
	private function redirectThis(){
		
		$this->redirect(':Frontend:Eshop:Cart:default', array(
			'id' => $this->actualPage->getId(),
			'path' => $this->actualPage->getPath(),
			'abbr' => $this->abbr
				));
	}
}
