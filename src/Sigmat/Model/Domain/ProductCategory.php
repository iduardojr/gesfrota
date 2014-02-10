<?php
namespace Sigmat\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Sigmat\Model\NestedSet\Node;
use Sigmat\Model\Activable;

/**
 * Categoria de Produto
 * @Entity
 * @Table(name="product_categories")
 */
class ProductCategory extends Node implements Activable {

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;
	
	/**
	 * @Column(type="boolean")
	 * @var boolean
	 */
	protected $active = true;

	/**
	 * @OneToMany(targetEntity="Sigmat\Model\Domain\ProductCategory", mappedBy="parent", indexBy="id")
	 * @OrderBy({"description" = "ASC"})
	 * @var ArrayCollection
	 */
	protected $children;
	
	/**
	 * @ManyToOne(targetEntity="Sigmat\Model\Domain\ProductCategory", inversedBy="children", fetch="EAGER")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 * @var ProductCategory
	 */
	protected $parent;
	
	/**
	 * Construtor
	 * 
	 * @param ProductCategory $parent
	 */
	public function __construct( ProductCategory $parent = null ) {
		parent::__construct();
		$this->setActive(true);
		$this->children = new ArrayCollection();
		if ( isset($parent) ) {
			$parent->addChild($this);
		}
	}

	/**
	 * Obtem os antecessores
	 * 
	 * @return array
	 */
	public function getAncestors() {
		if ( $this->getParent() !== null ) {
			$ancestors = $this->getParent()->getAncestors();
			$ancestors[$this->getParent()->getId()] = $this->getParent();
			return $ancestors;
		}
		return array();
	}

	/**
	 * Obtem a descrição completa
	 * 
	 * @return string
	 */
	public function getFullDescription( $separator = ' / ' ) {
		$nodes = $this->getAncestors();
		$nodes[] = $this;
		return implode($separator, $nodes);
	}

	/**
	 * Obtem $descriptions
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Obtem $active
	 *
	 * @return boolean
	 */
	public function getActive() {
		if ( $this->parent ) {
			return $this->active && $this->parent->getActive();
		}
		return $this->active;
	}
	
	/**
	 * Obtem $parent
	 *
	 * @return Category
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * Obtem $children
	 * 
	 * @return array
	 */
	public function getChildren() {
		return $this->children->getValues();
	}
	
	/**
	 * Atribui $description
	 *
	 * @param string $description
	 */
	public function setDescription( $description ) {
		$this->description = $description;
	}

	/**
	 * Atribui $parent
	 *
	 * @param ProductCategory $parent
	 * @throws \DomainException
	 * @throws \BadMethodCallException
	 */
	public function setParent( PRoductCategory $parent = null ) {
		if ( isset($parent) ) {
			$parent->addChild($this);
		} else {
			$this->parent = null;
		}
	}

	/**
	 * Atribui $active
	 *
	 * @param boolean $active
	 */
	public function setActive( $active ) {
		$this->active = ( bool ) $active;
	}

	/**
	 * Adiciona uma categoria filho
	 * 
	 * @param ProductCategory $category
	 * @throws \BadMethodCallException
	 * @throws \DomainException
	 */
	public function addChild( ProductCategory $category ) {
		if ( !$this->children->contains($category) ) {
			$parent = $category->getParent();
			if ( $parent instanceof ProductCategory ) {
				$parent->removeChild($category);
			}
			if ( $category->assertReferenceCircular($this) ) {
				throw new \DomainException('parent in reference circular');
			}
			$category->parent = $this;
			$this->children->add($category);
		}
	}

	/**
	 * Remove uma categoria filho
	 * 
	 * @param ProductCategory $category
	 * @throws \BadMethodCallException
	 */
	public function removeChild( ProductCategory $category ) {
		if ( !$this->children->contains($category) ) {
			$category->parent = null;
			$this->children->removeElement($category);
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getDescription();
	}

	/**
	 * Verifica se há referência circular
	 *
	 * @param ProductCategory $parent
	 * @return boolean
	 */
	private function assertReferenceCircular( ProductCategory $parent ) {
		if ( $parent === $this ) {
			return true;
		}
		foreach ( $this->children as $child ) {
			if ( $child->assertReferenceCircular($parent) ) {
				return true;
			}
		}
		return false;
	}
}
?>