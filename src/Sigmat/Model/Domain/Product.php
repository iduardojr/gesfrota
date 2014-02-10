<?php
namespace Sigmat\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Sigmat\Model\AbstractActivable;

/**
 * Produto
 * @Entity
 * @Table(name="products")
 */
class Product extends AbstractActivable {

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;


	/**
	 * @OneToOne(targetEntity="Sigmat\Model\Domain\ProductCategory", fetch="EAGER")
	 * @JoinColumn(name="product_category_id", referencedColumnName="id")
	 * @var Category
	 */
	protected $category;
	
	/**
	 * @ManyToMany(targetEntity="Sigmat\Model\Domain\ProductUnit", indexBy="id")
	 * @JoinTable(name="products_has_product_units",
	 * 		joinColumns={@JoinColumn(name="product_id", referencedColumnName="id")},
	 * 		inverseJoinColumns={@JoinColumn(name="product_unit_id", referencedColumnName="id")}
	 * )
	 * @var ArrayCollection
	 **/
	protected $units;
	

	/**
	 * Construtor 
	 * 
	 * @param ProductCategory $category
	 */
	public function __construct( ProductCategory $category = null ) {
		parent::__construct();
		$this->units = new ArrayCollection();
		if ( $category ) {
			$this->setCategory($category);
		}
	}
	
	/**
	 * Obtem o identificador formatado
	 *
	 * @return string
	 */
	public function getCode() {
		return str_repeat('0', 5 - strlen($this->getId())) . $this->getId();
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
	 * Obtem $category
	 *
	 * @return ProductCategory
	 */
	public function getCategory() {
		return $this->category;
	}
	
	/**
	 * Obtem as unidades de medida
	 *
	 * @return array
	 */
	public function getUnits() {
		return $this->units->toArray();
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
	 * Atribui $category
	 *
	 * @param ProductCategory $category
	 */
	public function setCategory( ProductCategory $category ) {
		$this->category = $category;
	}
	
	/**
	 * Adiciona uma unidade de medida 
	 * 
	 * @param ProductUnit $unit
	 */
	public function addUnit( ProductUnit $unit ) {
		$this->units[$unit->getId()] = $unit;
	}
	
	/**
	 * Remove uma unidade de medida
	 *
	 * @param ProductUnit $unit
	 */
	public function removeUnit( ProductUnit $unit ) {
		unset($this->units[$unit->getId()]);
	}
	
	/**
	 * Remove todas unidades de medida
	 */
	public function removeAllUnits() {
		$this->units->clear();
	}
}
?>