<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\orm\Mapping as orm;

/**
 * Description of OrderItem
 * @orm\Entity
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class OrderItem extends \WebCMS\Entity\Entity
{
    private $itemId;

    /**
     * @orm\Column
     */
    private $name;

    /**
     * @orm\Column(type="decimal", precision=12, scale=4)
     */
    private $price;

    /**
     * @orm\Column(type="integer")
     */
    private $vat;

    /**
     * @orm\Column(type="integer")
     */
    private $quantity;

    /**
     * @orm\ManyToOne(targetEntity="Order", inversedBy="items")
     * @orm\JoinColumn(onDelete="CASCADE")
     */
    private $order;

    /**
     * @orm\ManyToOne(targetEntity="Product")
     */
    private $product;

    /**
     * @orm\ManyToOne(targetEntity="Product")
     */
    private $productVariant;

    /**
     * @orm\Column(type="integer")
     */
    private $type;

    private $variant;

    private $link;

    const PAYMENT = 1;

    const SHIPPING = 2;

    const ITEM = 3;

    public function getItemId()
    {
        return $this->itemId;
    }

    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getVat()
    {
        return $this->vat;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function setVat($vat)
    {
        $this->vat = $vat;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getPriceWithVat()
    {
        return $this->getPrice() * (($this->getVat() / 100) + 1);
    }

    public function getPriceTotal()
    {
        return $this->getPrice() * $this->getQuantity();
    }

    public function getPriceTotalWithVat()
    {
        return $this->getPriceWithVat() * $this->getQuantity();
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getVariant()
    {
        return $this->variant;
    }

    public function setVariant($variant)
    {
        $this->variant = $variant;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getProductVariant()
    {
        return $this->productVariant;
    }

    public function setProduct($product)
    {
        $this->product = $product;
    }

    public function setProductVariant($productVariant)
    {
        $this->productVariant = $productVariant;
    }

    /**
     * Gets the value of link.
     *
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Sets the value of link.
     *
     * @param mixed $link the link
     *
     * @return self
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }
}
