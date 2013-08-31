<?php
namespace Sigmat\View\Product;

use Sigmat\View\AbstractForm;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\Decorator\Suggest;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use Sigmat\Model\Product\Product;
use Doctrine\ORM\EntityManager;

class ProductNewForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $searchClass
	 */
	public function __construct( Action $submit, Action $searchClass ) {
		$this->buildPanel('Administração', 'Gerenciar Produtos');
		$form = $this->buildForm('product-new-form');
		
		$general = new Fieldset('Informe a classe do produto');
		
		$input[0] = new TextBox(null);
		$input[0]->setSuggestion(new Suggest($searchClass));
		$input[0]->setSpan(4);
		
		$input[1] = new Hidden('product-class');
		$input[1]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		
		$form->buildField(null, $input, null, $general);
		$form->buildField(null, new Button('Avançar', new TgFormSubmit($submit, $form), Button::Primary), null, $general);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Product $object ) {

	}
	
	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Product $object, EntityManager $em ) {
		
	}
}
?>