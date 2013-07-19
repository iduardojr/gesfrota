<?php
namespace Sigmat\Controller;

use PHPBootstrap\Mvc\Controller;
use Sigmat\View\Layout;

/**
 * Index
 */
class IndexController extends Controller{
	
	public function indexAction() {
		return new Layout('index/index.phtml');
	}
}
?>