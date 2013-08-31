<?php
namespace Sigmat\Model\Product;

use Sigmat\Model\Entity;
use Sigmat\Model\Deleting;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Classe de Produto
 * @Entity
 * @Table(name="product_classes")
 */
class ProductClass extends Entity implements Deleting {
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;
	
	/**
	 * @Column(type="boolean")
	 * @var boolean
	 */
	protected $status;
	
	/**
	 * @ManyToMany(targetEntity="Sigmat\Model\Product\Attribute", indexBy="id")
	 * @JoinTable(name="product_classes_has_product_attributes",
     *      joinColumns={@JoinColumn(name="product_class_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="product_attribute_id", referencedColumnName="id")}
     *      )
	 * @var ArrayCollection
	 */
	protected $attributes;
	
	/**
	 * Construtor
	 */
	public function __construct() {
		parent::__construct();
		$this->attributes = new ArrayCollection();
		$this->setStatus(true);
	}
	
	/**
	 * Obtem $description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Obtem $status
	 *
	 * @return boolean
	 */
	public function getStatus() {
		return $this->status;
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
	 * Atribui $status
	 *
	 * @param boolean $status
	 */
	public function setStatus( $status ) {
		$this->status = ( bool ) $status;
	}
	
	/**
	 * @see Deleting::delete()
	 */
	public function delete() {
		$this->setStatus(false);
	}
	
	/**
	 * Adiciona um atributo
	 *
	 * @param Attribute $attr
	 */
	public function addAttribute( Attribute $attr ) {
		$this->attributes[$attr->getId()] = $attr;
	}
	
	/**
	 * Remove um atributo
	 *
	 * @param Attribute $attr
	 */
	public function removeAttribute( Attribute $attr ) {
		unset($this->attributes[$attr->getId()]);
	}
	
	/**
	 * Remove todos atributos
	 */
	public function removeAllAttributes() {
		$this->attributes->clear();
	}
	
	/**]]
	 * Obtem todos os atributos
	 *
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes->toArray();
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getDescription();
	}
}
?>