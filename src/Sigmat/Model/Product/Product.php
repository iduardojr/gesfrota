<?php
namespace Sigmat\Model\Product;

use Sigmat\Model\Entity;
use Sigmat\Model\Deleting;

/**
 * Produto
 * @Entity
 * @Table(name="products")
 */
class Product extends Entity implements Deleting {

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
	 * @OneToOne(targetEntity="Sigmat\Model\Product\Category", fetch="EAGER")
	 * @JoinColumn(name="product_category_id", referencedColumnName="id")
	 * @var Category
	 */
	protected $category;
	
	/**
	 * @OneToOne(targetEntity="Sigmat\Model\Product\ProductClass", fetch="EAGER")
	 * @JoinColumn(name="product_class_id", referencedColumnName="id")
	 * @var ProductClass
	 */
	protected $productClass;

	/**
	 * Construtor 
	 * 
	 * @param ProductClass $class
	 * @param Category $category
	 */
	public function __construct( ProductClass $class, Category $category = null ) {
		parent::__construct();
		$this->setProductClass($class);
		$this->setCategory($category);
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
	 * Obtem $category
	 *
	 * @return Category
	 */
	public function getCategory() {
		return $this->category;
	}
	
	/**
	 * Obtem $productClass
	 *
	 * @return ProductClass
	 */
	public function getProductClass() {
		return $this->productClass;
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
	 * Atribui $category
	 *
	 * @param Category $category
	 */
	public function setCategory( Category $category = null ) {
		$this->category = $category;
	}
	
	/**
	 * Atribui $productClass
	 *
	 * @param ProductClass $class
	 */
	public function setProductClass( ProductClass $class ) {
		$this->productClass = $class;
	}
	
	/**
	 * @see Deleting::delete()
	 */
	public function delete() {
		$this->setStatus(false);
	}
}
?>