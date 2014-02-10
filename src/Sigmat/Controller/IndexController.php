<?php
namespace Sigmat\Controller;

use PHPBootstrap\Mvc\Controller;
use Sigmat\View\GUI\Layout;

class IndexController extends Controller {
	
	public function indexAction() {
		return new Layout('index/index.phtml');
	}
}
?>