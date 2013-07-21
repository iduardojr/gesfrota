<?php
namespace Sigmat\Controller\Helper;

class NotFoundEntityException extends \Exception {
	
	/**
	 * Construtor
	 *
	 * @param string $message
	 * @param string $code
	 * @param string $previous
	 */
	public function __construct( $message = null, $code = null, $previous = null ){
		if ( func_num_args() < 1 ) {
			$message = 'Entidade não foi encontrada.';
		}
		parent::__construct($message, $code, $previous);
	}
}
?>