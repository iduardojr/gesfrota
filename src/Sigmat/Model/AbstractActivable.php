<?php
namespace Sigmat\Model;

/**
 * Entidade ativável
 * @MappedSuperclass
 */
abstract class AbstractActivable extends Entity implements Activable {
	
	/**
	 * @Column(type="boolean")
	 * @var boolean
	 */
	protected $active = true;
	
	/**
	 * Construtor
	 */
	public function __construct() {
		parent::__construct();
		$this->setActive(true);
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
	 * Obtem $active
	 * 
	 * @return boolean
	 */
	public function getActive() {
		return $this->active;
	}
}
?>