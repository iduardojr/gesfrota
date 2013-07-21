<?php
namespace Sigmat\Controller\Helper;

class InvalidRequestDataException extends \Exception {
	
	/**
	 * Construtor
	 * 
	 * @param string $message
	 * @param string $code
	 * @param string $previous
	 */
	public function __construct( $message = null, $code = null, $previous = null ){
		if ( func_num_args() < 1 ) {
			$message = 'Por favor, verifique se os campos estão corretamente preenchidos.';
		}
		parent::__construct($message, $code, $previous);
	}
}
?>