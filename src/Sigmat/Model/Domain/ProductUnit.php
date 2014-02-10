<?php
namespace Sigmat\Model\Domain;

use Sigmat\Model\AbstractActivable;

/**
 * Unidades de Produto
 * @Entity
 * @Table(name="product_units")
 */
class ProductUnit extends AbstractActivable {
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;
	
	/**
	 * Obtem $description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
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
	 * @return string
	 */
	public function __toString() {
		return $this->getDescription();
	}
	
}
?>