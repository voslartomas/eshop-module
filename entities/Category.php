<?php

namespace WebCMS\EshopModule\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as gedmo;
use Doctrine\orm\Mapping as orm;

/**
 * Description of Page
 * @gedmo\Tree(type="nested")
 * @orm\Entity(repositoryClass="\WebCMS\EshopModule\Doctrine\CategoryRepository")
 * @orm\Table(name="eshopCategory")
 * @author Tomáš Voslař <tomas.voslar at webcook.cz>
 */
class Category extends \AdminModule\Seo{

    /**
     * @orm\Column(length=64)
     */
    private $title;
	
    /**
     * @orm\Column(type="text", nullable=true)
     */
    private $description;
	
	/**
	 * @orm\Column(nullable=true)
	 */
	private $picture;
	
    /**
     * @gedmo\Slug(fields={"title"})
     * @orm\Column(length=64)
     */
    private $slug;

    /**
     * @gedmo\TreeLeft
     * @orm\Column(type="integer")
     */
    private $lft;

    /**
     * @gedmo\TreeRight
     * @orm\Column(type="integer")
     */
    private $rgt;

    /**
     * @gedmo\TreeParent
     * @orm\ManyToOne(targetEntity="Category", inversedBy="children")
     * @orm\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @gedmo\TreeRoot
     * @orm\Column(type="integer", nullable=true)
     */
    private $root;

    /**
     * @gedmo\TreeLevel
     * @orm\Column(name="lvl", type="integer")
     */
    private $level;

    /**
     * @orm\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

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
	 * @orm\Column
	 */
	private $path;
	
	/**
	 * @orm\Column(type="boolean")
	 */
	public $visible;
	
	/**
	 * @orm\ManyToMany(targetEntity="Product", mappedBy="categories")
	 */
	private $products;
	
	/**
	 * @orm\Column(type="boolean", nullable=true)
	 */
	private $favourite;
		
	private $link;
	
    public function __construct()    {
        $this->children = new ArrayCollection();
		$this->products = new ArrayCollection();
    }

    public function getSlug()
    {
        return $this->slug;
    }
	
	public function setSlug($slug) {
		$this->slug = $slug;
	}

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

	public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getLeft()
    {
    	return $this->lft;
    }

	public function getRight()
    {
        return $this->rgt;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getUpdated()
    {
        return $this->updated;
    }
	
	public function getLanguage() {
		return $this->language;
	}

	public function setLanguage($language) {
		$this->language = $language;
	}
	
	public function getVisible() {
		return $this->visible;
	}

	public function setVisible($visible) {
		$this->visible = $visible;
	}

	public function getDefault() {
		return $this->default;
	}

	public function setDefault($default) {
		$this->default = $default;
	}
		
	public function getPath() {
		return $this->path;
	}

	public function setPath($path) {
		$this->path = $path;
	}
	
	public function getPicture() {
		return $this->picture;
	}

	public function setPicture($picture) {
		$this->picture = $picture;
	}
	
    public function __toString(){
        return $this->getTitle();
    }
	
	public function getProducts() {
		
		$products = array();
		foreach($this->products as $p){
			if(!$p->getHide()){
				$products[] = $p;
			}
		}
		
		return $products;
	}

	public function setProducts($products) {
		$this->products = $products;
	}
	
	public function getFavourite() {
		return $this->favourite;
	}

	public function setFavourite($favourite) {
		$this->favourite = $favourite;
	}
	
	public function getLink() {
		return $this->link;
	}
	
	public function setLink($link) {
		$this->link = $link;
	}
}
