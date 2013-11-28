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
		
		// listing actions
		if($this->method === 'GET'){
			if(!$this->action){
				$this->baseResource();
			}elseif($this->action === 'product' && !$this->id){
				$this->listProducts();
			}elseif($this->action === 'product' && $this->id){
				$this->listProducts($this->id);
			}
		// update actions
		}elseif($this->method === 'PUT'){
			
		// create actions
		}elseif($this->method === 'POST'){
			
			if($this->action === 'product' && $this->id){
				
				$this->updateProduct($this->id, $_POST);
			}else{
				$this->sendResponse(new \Nette\Application\Responses\JsonResponse('Not found')); 
			}
		// delete actions
		}elseif($this->method === 'DELETE'){
			
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
				)
			)
		);

		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($baseResource));
	}
	
	/**
	 * Returns list of products or one product in JSON.
	 * @param type $id Id of product to get
	 */
	private function listProducts($id = NULL){
		
		if(!$id){
			$products = $this->productRepository->findAll();
			
			$r = array();
			foreach($products as $p){
				$r[] = $this->productToArray($p);
			}
			
			$response = $r;
		}else{
			$product = $this->productRepository->find($id);
			
			$response = $this->productToArray($product);
		}
		
		
		$this->sendResponse(new \Nette\Application\Responses\JsonResponse($response));
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
	private function updateProduct($id, $data){
		
		$product = $this->productRepository->find($id);
		
		if($product->getId()){
			
			$this->setAttribute($product, $data, 'barcode');
			$this->setAttribute($product, $data, 'barcodeType');
			$this->setAttribute($product, $data, 'store');
			
			$this->em->flush();
			
			$this->sendAPIResponse('200', 'Product updated.', $this->productToArray($product));
			
		}else{
			// product not found
		}
	}
	
	private function setAttribute($entity, $data, $key){
		if(array_key_exists($key, $data)){
			$getter = 'set' . ucfirst($key);
			$entity->$getter($data[$key]);
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
}