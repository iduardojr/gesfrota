<?php
namespace Sigmat\View;

use PHPBootstrap\Mvc\View\View;

/**
 * Layout
 */
class Layout extends View {
	
	/**
	 * Construtor
	 */
	public function __construct() {
		parent::__construct();
		$this->setLayout('layout/layout.phtml');
	}
}
?>