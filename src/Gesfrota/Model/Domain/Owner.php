<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\AbstractActivable;

/**
 * Proprietário
 * @Entity
 * @Table(name="owners")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"P" = "OwnerPerson", "E" = "OwnerCompany"})
 */
abstract class Owner extends AbstractActivable {

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
		
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $nif;
		
	/**
	 * Construtor
	 */
	public function __construct() {
		parent::__construct();
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
	 * Obtem $nif
	 *
	 * @return string
	 */
	public function getNif() {
		return $this->nif;
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
	 * Atribui $nif
	 *
	 * @param string $nif
	 */
	public function setNif( $nif ) {
		$this->nif = $nif;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}
}
?>