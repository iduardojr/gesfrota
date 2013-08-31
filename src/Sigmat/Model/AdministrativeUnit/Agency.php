<?php
namespace Sigmat\Model\AdministrativeUnit;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Orgão
 * @Entity
 */
class Agency extends AdministrativeUnit {

	/**
	 * Construtor
	 */
	public function __construct() {
		$this->children = new ArrayCollection();
		$this->setStatus(true);
	}
	
	/**
	 * @see AdministrativeUnit::setParent()
	 * @throws \BadMethodCallException
	 */
	public function setParent( AdministrativeUnit $parent ) {
		throw new \BadMethodCallException('method unssuportted');
	}
	
	/**
	 * @see AdministrativeUnit::getAncestors()
	 */
	public function getAncestors() {
		return array();
	}
	
	/**
	 * @see AdministrativeUnit::getStatus()
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getAcronym();
	}
}
?>