<?php
namespace Sigmat\Controller;

use PHPBootstrap\Mvc\Controller;
use Sigmat\Common\Layout;

/**
 * Index
 */
class IndexController extends Controller{
	
	/**
	 * Construtor 
	 */
	public function __construct() {
	}
	
	/**
	 * index
	 * @return Layout
	 */
	public function indexAction() {
		return new Layout('index/index.phtml');
	}
}
?>