<?php
namespace Sigmat\Model\Product;

use Sigmat\Model\Entity;

/**
 * Opções de Atributo do produto
 * @Entity
 * @Table(name="product_attribute_options")
 */
class AttributeOption extends Entity {
	
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
	 * @ManyToOne(targetEntity="Sigmat\Model\Product\Attribute", inversedBy="options")
     * @JoinColumn(name="product_attribute_id", referencedColumnName="id")
	 * @var Attribute
	 */
	protected $attribute;

	/**
	 * Construtor
	 * 
	 * @param string $description
	 */
	public function __construct( $description ) {
		parent::__construct();
		$this->setDescription($description);
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
	 * Obtem $attribute
	 * 
	 * @return \Sigmat\Model\Product\Attribute
	 */
	public function getAttribute() {
		return $this->attribute;
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
	 * Atribui $attribute
	 * 
	 * @param Attribute $attribute
	 */
	public function setAttribute( Attribute $attribute ) {
		$this->attribute = $attribute;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getDescription();
	}
	
}
?>