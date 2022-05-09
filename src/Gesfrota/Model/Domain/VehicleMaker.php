<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\AbstractActivable;

/**
 * Fabricante de Veículos
 * @Entity
 * @Table(name="vehicle_makers")
 */
class VehicleMaker extends AbstractActivable {
	
	/**
	 * Carros e utilitários pequenos
	 * @var integer
	 */
	const CARS = 1;
	
	/**
	 * Caminhões e micro-ônibus
	 * @var integer
	 */
	const TRUCKS = 2;
	
	/**
	 * Motos
	 * 
	 * @var integer
	 */
	const MOTORCYCLES = 3;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @Column(type="integer")
	 * 
	 * @var integer
	 */
	protected $type;
	
	/**
	 * Construtor
	 * 
	 * @param string $name
	 */
	public function __construct( $name = null ) {
	    $this->setName($name);
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
	 * Atribui $name
	 *
	 * @param string $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}
	
	/**
	 * @return integer
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param integer $type
	 * @throws \DomainException
	 */
	public function setType($type) {
		if (!self::isTypeAllowed($type)) {
			throw new \DomainException('The ' . $type . ' is not type vehicle allowed.');
		}
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}
	
	/**
	 * @return string[]
	 */
	public static function getTypesAllowed() {
		return [self::CARS => 'Carros e Utilitários Pequenos',
				self::TRUCKS => 'Caminhões e Micro-Ônibus',
				self::MOTORCYCLES => 'Motos'
		];
		
	}
	
	/**
	 * @param integer $type
	 * @return boolean
	 */
	public static function isTypeAllowed(int $type) {
		return array_key_exists($type, self::getTypesAllowed());
	}
}
?>