<?php
namespace Sigmat\Controller;

use Sigmat\Common\Layout;
use Sigmat\View\AgencyForm;
use PHPBootstrap\Mvc\Controller;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\TgFormSubmit;


/**
 * Orgão
 */
class AgencyController extends Controller {
	
	public function __construct() {
		
	}
	
	/**
	 * new
	 * 
	 * @return Layout
	 */
	public function newAction() {
		$form = new AgencyForm('agency');
		$form->getButtonByName('submit')->setLabel('Incluir');
		$form->getButtonByName('submit')->setToggle(new TgFormSubmit(new Action($this, 'save'), $form));
		return new Layout($form);
	}
}
?>