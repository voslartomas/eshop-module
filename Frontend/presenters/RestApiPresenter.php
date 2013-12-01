<?php

namespace FrontendModule\EshopModule;

/**
 * This presenter - RESTful API for eshop module.
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class RestApiPresenter extends BasePresenter{
	
	private $action = NULL;
	
	private $id = NULL;
	
	private $method;
	
	private $productRepository;
	
	public function startup() {
		parent::startup();
		
		$this->method = $this->request->getMethod();
		$this->productRepository = $this->em->getRepository('WebCMS\EshopModule\Doctrine\Product');
	}
	
	/**
	 * JSON list with links and version of the API
	 */
	public function renderDefault(){
		
		$parameters = $this->getParameter('parameters');
		
		if(array_key_exists(0, $parameters)){
			$this->action = $parameters[0];
		}
		
		if(array_key_exists(1, $parameters)){
			$this->id = $parameters[1];
		}
		
		// TODO request authentication
		
		// listing actions
		if($this->method === 'GET'){
			if(!$this->action){
				$this->baseResource();
			}elseif($this->action === 'product' && !$this->id){
				
				$barcodes = false;
				if(array_key_exists('onlyWithoutBarcode', $_GET)){
					$barcodes = true;
				}
				
				$this->listProducts(null, $barcodes);
			}elseif($this->action === 'product' && $this->id){
				$this->listProducts($this->id);
			}
		// update actions
		}elseif($this->method === 'PUT'){
			
		// create actions
		}elseif($this->method === 'POST'){
			
			// check if request is authorized
			if($this->checkHash($_POST['hash'])){
			
				if($this->action === 'product' && !$this->id){

					$this->updateProduct($_POST);
				}elseif($this->action === 'barcode'){

					$this->fillBarcode($_POST);
				}elseif($this->action === 'order'){
					$this->createOrder($_POST);
				}elseif($this->action === 'configuration'){
					$this->checkConfiguration($_POST);
				}else{
					$this->sendAPIResponse('400', 'Bad request.', array());
				}
			}
		// delete actions
		}elseif($this->method === 'DELETE'){
			
		}
	}
	
	private function checkHash($hash){
		if($this->getApiHash() == $hash){
			return TRUE;
		}else{
			$this->sendAPIResponse('401', 'Not authorized.', null);
		}
	}
	
	/**
	 * Returns list of possible actions for this Web service.
	 */
	private function baseResource(){
		$baseResource = array(
			'version' => '0.1',
			'links' => array(
				array(
					'href' => '/product',
					'rel' => 'list',
					'method' => 'GET'
				),
				array(
					'href' => '/product/id',
					'rel' => 'list product',
					'method' => 'GET'
				),
				array(
					'href' => '/product',
					'rel' => 'create',
					'method' => 'POST'
				),
				array(
					'href' => '/product/id',
					'rel' => 'update',
					'method' => 'PUT'
				),
				array(
					'href' => '/order',
					'rel' => 'create',
					'method' => 'POST'
				)
			)
		);

		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($baseResource));
	}
	
	/**
	 * Returns list of products or one product in JSON.
	 * @param type $id Id of product to get
	 */
	private function listProducts($id = NULL, $onlyWithoutBarcode = FALSE){
		
		if(!$id){
			
			if($onlyWithoutBarcode){
				$products = $this->productRepository->findBy(array(
					'barcode' => ""
				));
			}else{
				$products = $this->productRepository->findAll();
			}
			
			$r = array();
			foreach($products as $p){
				$r[] = $this->productToArray($p);
			}
			
			$response = $r;
		}else{
			$product = $this->productRepository->find($id);
			
			$response = $this->productToArray($product);
		}
		
		$this->sendAPIResponse('200', 'Successfuly fetched product data.', $response);
	}
	
	/**
	 * Convert product to array.
	 * @param type $p
	 * @return type
	 */
	private function productToArray($p){
		return array(
					'id' => $p->getId(),
					'title' => $p->getTitle(),
					'price' => round($p->getPrice(), 2),
					'vat' => $p->getVat(),
					'priceWithVat' => round($p->getPriceWithVat(), 2),
					'store' => $p->getStore(),
					'barcode' => $p->getBarcode(),
					'barcodeType' => $p->getBarcodeType()
				);
	}
	
	/**
	 * Updates product.
	 * @param type $id
	 * @param type $data
	 */
	private function updateProduct($data){
		
		if(array_key_exists('barcode', $data) && array_key_exists('title', $data)){
			
			$product = $this->productRepository->findOneBy(array(
				'barcode' => $data['barcode']
			));

			if(is_object($product)){

				$this->setAttribute($product, $data, 'store');

				$this->em->flush();

				$this->sendAPIResponse('200', 'Product ' . $product->getTitle() . ' updated.', $this->productToArray($product));

			}else{
			
				$product = new \WebCMS\EshopModule\Doctrine\Product;
				
				if(!array_key_exists('vat', $data)){
					$product->setVat(0);
				}
				
				$this->setAttribute($product, $data, 'title');
				$this->setAttribute($product, $data, 'price');
				$this->setAttribute($product, $data, 'vat');
				$this->setAttribute($product, $data, 'barcode');
				$this->setAttribute($product, $data, 'barcodeType');
				$this->setAttribute($product, $data, 'store');
				
				if(!array_key_exists('price', $data)){
					$product->setPrice(0);
				}else{
					$product->setPrice($data['price'] - $data['price'] * ($product->getVat() / ($product->getVat() + 100)));
				}
				
				$product->setLanguage($this->language);
				$product->setHide(true);
				
				$this->em->persist($product);
				$this->em->flush();
				
				$this->sendAPIResponse('200', 'Product ' . $product->getTitle() . ' added.', $this->productToArray($product));
			}
		
		}else{
			$this->sendAPIResponse('501', 'No barcode or title.', null);
		}
	}
	
	private function checkConfiguration($data){
		$hash = $data['hash'];
		
		if($hash === $this->getApiHash()){
			$this->sendAPIResponse('200', 'API hash is correct.', null);
		}else{
			$this->sendAPIResponse('0', 'API hash is incorrect.', null);
		}
	}
	
	private function getApiHash(){
		$identification = $this->settings->get('API identificator', 'eshopModule', 'text', array())->getValue();
		$token = $this->settings->get('API token', 'eshopModule', 'text', array())->getValue();
		
		return sha1($identification . $token . $identification);
	}
	
	private function setAttribute($entity, $data, $key){
		if(array_key_exists($key, $data)){
			$getter = 'set' . ucfirst($key);
			
			if(method_exists($entity, $getter)){
				$entity->$getter($data[$key]);
			}
		}
		
	}
	
	private function sendAPIResponse($status, $message, $response){
		
		$r = array(
			'status' => $status,
			'message' => $message,
			'response' => $response
		);
		
		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($r));
	}

	private function createOrder($data) {
		
		$status = $this->em->getRepository('WebCMS\EshopModule\Doctrine\OrderState')->findOneBy(array(
			'storeDecrease' => TRUE
		));
		
		// TODO if status does not exists find default one
		
		$order = new \WebCMS\EshopModule\Doctrine\Order;
		$order->setFirstname($this->translation['API order']);
		
		// order items
		foreach($data['barcode'] as $id){
			
			$product = $this->productRepository->findOneBy(array(
				'barcode' => $id
			));
			
			$orderItem = new \WebCMS\EshopModule\Doctrine\OrderItem;
			$orderItem->setOrder($order);
			$orderItem->setName($product->getTitle());
			$orderItem->setPrice($product->getPrice());
			$orderItem->setQuantity(1);
			$orderItem->setType(\WebCMS\EshopModule\Doctrine\OrderItem::ITEM);
			$orderItem->setVat($product->getVat());
			
			if(is_object($product->getVariantParent())){
				$orderItem->setProductVariant($product);
				$orderItem->setProduct($product->getVariantParent());
			}else{
				$orderItem->setProduct($product);
			}
			
			$order->addItem($orderItem);
		}
		
		$order->setStatus($status);
		$order->getPriceTotal();
		
		$this->em->persist($order);
		$this->em->flush();
		
		$this->sendAPIResponse('200', 'Order ' . $order->getId() . ' created.', NULL);
	}
	
	private function fillBarcode($data){
		
		$product = $this->productRepository->find($data['id']);
		
		$this->setAttribute($product, $data, 'barcode');
		$this->setAttribute($product, $data, 'barcodeType');
		
		$this->em->flush();
		
		$this->sendAPIResponse('200', 'Barcode update for ' . $product->getTitle() . '.', $this->productToArray($product));
	}
}