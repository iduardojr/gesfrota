<?php
namespace Sigmat\Model\Product;

use Sigmat\Model\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Categoria de Produto
 * @Entity
 * @Table(name="product_categories")
 */
class Category extends Entity {
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @OneToMany(targetEntity="Sigmat\Model\Product\Category", mappedBy="parent")
	 * @OrderBy({"name" = "ASC"})
	 * @var ArrayCollection
	 */
	protected $children;
	
	/**
	 * @ManyToOne(targetEntity="Sigmat\Model\Product\Category", inversedBy="children", fetch="EAGER")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 * @var Category
	 */
	protected $parent;
	
	/**
	 * Construtor
	 * 
	 * @param Category $parent
	 */
	public function __construct( Category $parent = null ) {
		parent::__construct();
		$this->children = new ArrayCollection();
		$this->setParent($parent);
	}
	
	/**
	 * Obtem os antecessores
	 * 
	 * @return array
	 */
	public function getAncestors() {
		if ( $this->getParent() !== null ) {
			$ancestors = $this->getParent()->getAncestors();
			$ancestors[] = $this->getParent();
			return $ancestors;
		}
		return array();
	}
	
	/**
	 * Obtem a descrição da categoria
	 * 
	 * @return string
	 */
	public function getDescription() {
		$nodes = $this->getAncestors();
		$nodes[] = $this;
		return implode(' / ', $nodes);
	}
	
	/**
	 * Obtem $name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
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
	 * Obtem as unidades filhas
	 *
	 * @return array
	 */
	public function getChildren() {
		return $this->children->toArray();
	}
	
	/**
	 * Atribui $name
	 *
	 * @param string $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}
	
	
	/**
	 * Atribui $parent
	 *
	 * @param Category $parent
	 * @throws \DomainException
	 */
	public function setParent( Category $parent = null ) {
		if ( $parent !== null ) {
			if ( $this->assertReferenceCircular($parent) ) {
				throw new \DomainException('parent in reference circular');
			}
		}
		$this->parent = $parent;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}
	
	/**
	 * Verifica se há referencia circular
	 *
	 * @param Category $parent
	 * @return boolean
	 */
	private function assertReferenceCircular( Category $parent ) {
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