<?php
namespace Sigmat\Model;

/**
 * Interface de uma entidade deletável
 */
interface Deleting {
	
	/**
	 * Exclui o objeto
	 */
	public function delete();
	
}
?>