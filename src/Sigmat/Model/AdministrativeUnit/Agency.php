<?php
namespace Sigmat\Model\AdministrativeUnit;

use Sigmat\Model\Entity;

/**
 * Orgão
 * @Entity
 */
class Agency extends AdministrativeUnit {

	/**
	 * @see AdministrativeUnit::setParent()
	 * @throws \BadMethodCallException
	 */
	public function setParent( AdministrativeUnit $parent = null ) {
		throw new \BadMethodCallException('method unssuportted');
	}
}
?>