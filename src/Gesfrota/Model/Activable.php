<?php
namespace Gesfrota\Model;

/**
 * Interface de Entidade Ativável
 */
interface Activable {
	
	/**
	 * Ativa/Desativa a entidade
	 *
	 * @param boolean $active
	 */
	public function setActive( $active );
	
	/**
	 * Verifica se a entidade está ativa/desativa
	 * 
	 * @return boolean
	 */
	public function getActive();
}
?>