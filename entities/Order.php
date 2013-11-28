<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as gedmo;
use Doctrine\orm\Mapping as orm;

/**
 * Description of Order
 * @orm\Entity
 * @orm\Table(name="Orders")
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Order extends \AdminModule\Doctrine\Entity {
	/**
	 * @orm\Column
	 */
	private $firstname;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $lastname;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $email;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $phone;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $street;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $city;
	
	/**
	 * @orm\Column(type="integer", nullable=true)
	 */
	private $postcode;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $state;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $invoiceCompany;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $invoiceNo;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $invoiceVatNo;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $invoiceEmail;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $invoicePhone;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $invoiceStreet;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $invoiceCity;
	
	/**
	 * @orm\Column(type="integer", nullable=true)
	 */
	private $invoicePostcode;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $invoiceState;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $currency;
	
	/**
	 * @orm\OneToMany(targetEntity="OrderItem", mappedBy="order", cascade={"persist"})
	 */
	private $items;
	
	/**
     * @gedmo\Timestampable(on="create")
     * @orm\Column(type="datetime")
     */
    private $created;

    /**
     * @gedmo\Timestampable(on="update")
     * @orm\Column(type="datetime")
     */
    private $updated;
	
	/**
	 * @orm\ManyToOne(targetEntity="\AdminModule\Language")
	 * @orm\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $language;
	
	/**
	 * @orm\ManyToOne(targetEntity="OrderState")
	 * @orm\JoinColumn(onDelete="SET NULL")
	 */
	private $status;
	
	/**
	 * @orm\Column(type="decimal", precision=12, scale=4)
	 */
	private $priceTotal;
	
	private $payment;
	
	private $shipping;
	
	/**
	 * @orm\ManyToOne(targetEntity="\WebCMS\AccountModule\Doctrine\Account", inversedBy="orders")
	 * @orm\JoinColumn(onDelete="SET NULL")
	 */
	private $account;
	
	public function __construct(){
		$this->items = new ArrayCollection();
	}
	
	public function removePayment(){
		foreach($this->items as $item){
			if($item->getType() === OrderItem::PAYMENT){
				$this->items->removeElement($item);
			}
		}
	}
	
	public function addPayment($item){
		$item->setOrder($this);
		$item->setType(OrderItem::PAYMENT);
		
		$this->removePayment();
		$this->items->add($item);
		
		$this->getPriceTotal();
	}
	
	public function removeShipping(){
		foreach($this->items as $item){
			if($item->getType() === OrderItem::SHIPPING){
				$this->items->removeElement($item);
			}
		}
	}
	
	public function addShipping($item){
		$item->setOrder($this);
		$item->setType(OrderItem::SHIPPING);
		
		$this->removeShipping();
		$this->items->add($item);
		
		$this->getPriceTotal();
	}
	
	public function addItem($item){
		$item->setOrder($this);
		$item->setType(OrderItem::ITEM);
		
		$this->items->add($item);
		
		$this->getPriceTotal(); // recalculation
	}
	
	public function removeItem($item){
		$this->items->removeElement($item);
	}
	
	public function getFirstname() {
		return $this->firstname;
	}

	public function getLastname() {
		return $this->lastname;
	}

	public function getEmail() {
		return $this->email;
	}

	public function getPhone() {
		return $this->phone;
	}

	public function getStreet() {
		return $this->street;
	}

	public function getCity() {
		return $this->city;
	}

	public function getState() {
		return $this->state;
	}

	public function getCurrency() {
		return $this->currency;
	}

	public function getItems() {
		
		$return = array();
		foreach($this->items as $item){
			if($item->getType() === OrderItem::ITEM){
				$return[] = $item;
			}
		}
		
		foreach($this->items as $item){
			if($item->getType() !== OrderItem::ITEM){
				$return[] = $item;
			}
		}
		
		return $return;
	}

	public function setFirstname($firstname) {
		$this->firstname = $firstname;
	}

	public function setLastname($lastname) {
		$this->lastname = $lastname;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function setPhone($phone) {
		$this->phone = $phone;
	}

	public function setStreet($street) {
		$this->street = $street;
	}

	public function setCity($city) {
		$this->city = $city;
	}

	public function setState($state) {
		$this->state = $state;
	}

	public function setCurrency($currency) {
		$this->currency = $currency;
	}

	public function setItems($items) {
		$this->items = $items;
	}

	public function getInvoiceEmail() {
		return $this->invoiceEmail;
	}

	public function getInvoicePhone() {
		return $this->invoicePhone;
	}

	public function getInvoiceStreet() {
		return $this->invoiceStreet;
	}

	public function getInvoiceCity() {
		return $this->invoiceCity;
	}

	public function getInvoiceState() {
		return $this->invoiceState;
	}

	public function setInvoiceEmail($invoiceEmail) {
		$this->invoiceEmail = $invoiceEmail;
	}

	public function setInvoicePhone($invoicePhone) {
		$this->invoicePhone = $invoicePhone;
	}

	public function setInvoiceStreet($invoiceStreet) {
		$this->invoiceStreet = $invoiceStreet;
	}

	public function setInvoiceCity($invoiceCity) {
		$this->invoiceCity = $invoiceCity;
	}

	public function setInvoiceState($invoiceState) {
		$this->invoiceState = $invoiceState;
	}
	
	public function getPostcode() {
		return $this->postcode;
	}

	public function getInvoicePostcode() {
		return $this->invoicePostcode;
	}

	public function setPostcode($postcode) {
		$this->postcode = $postcode;
	}

	public function setInvoicePostcode($invoicePostcode) {
		$this->invoicePostcode = $invoicePostcode;
	}
	
	/* Calculations */
	
	public function getQuantityTotal(){
		$items = $this->getItems();
		$total = 0;
		foreach($items as $item){
			if($item->getType() === OrderItem::ITEM){
				$total += $item->getQuantity();
			}
		}
		
		return $total;
	}
	
	public function getPriceTotal(){
		$items = $this->getItems();
		$this->priceTotal = 0;
		foreach($items as $item){
			$this->priceTotal += $item->getPrice() * $item->getQuantity();
		}
		
		return $this->priceTotal;
	}
	
	public function getPriceTotalWithVat(){
		$items = $this->getItems();
		$this->priceTotal = 0;
		foreach($items as $item){
			$this->priceTotal += $item->getPriceTotalWithVat();
		}
		
		return $this->priceTotal;
	}
	
	public function getPayment() {
		return $this->payment;
	}

	public function getShipping() {
		return $this->shipping;
	}

	public function setPayment($payment) {
		$this->payment = $payment;
	}

	public function setShipping($shipping) {
		$this->shipping = $shipping;
	}
	
	public function getCreated() {
		return $this->created;
	}

	public function getUpdated() {
		return $this->updated;
	}

	public function getLanguage() {
		return $this->language;
	}

	public function setCreated($created) {
		$this->created = $created;
	}

	public function setUpdated($updated) {
		$this->updated = $updated;
	}

	public function setLanguage($language) {
		$this->language = $language;
	}

	public function getStatus() {
		return $this->status;
	}

	public function setStatus($status) {
		
		if($status->getStoreDecrease() && ($this->status->getId() != $status->getId())){
			foreach($this->getItems() as $item){
				if($item->getProduct() && !$item->getProductVariant()){
					$newStore = $item->getProduct()->getStore() - $item->getQuantity();
					$item->getProduct()->setStore($newStore);
				}elseif($item->getProductVariant()){
					$newStore = $item->getProductVariant()->getStore() - $item->getQuantity();
					$item->getProductVariant()->setStore($newStore);
				}
			}
		}
		
		$this->status = $status;
	}
	
	public function getInvoiceCompany() {
		return $this->invoiceCompany;
	}

	public function getInvoiceNo() {
		return $this->invoiceNo;
	}

	public function getInvoiceVatNo() {
		return $this->invoiceVatNo;
	}

	public function setInvoiceCompany($invoiceCompany) {
		$this->invoiceCompany = $invoiceCompany;
	}

	public function setInvoiceNo($invoiceNo) {
		$this->invoiceNo = $invoiceNo;
	}

	public function setInvoiceVatNo($invoiceVatNo) {
		$this->invoiceVatNo = $invoiceVatNo;
	}
	
	public function getAccount() {
		return $this->account;
	}

	public function setAccount($account) {
		$this->account = $account;
	}
}
