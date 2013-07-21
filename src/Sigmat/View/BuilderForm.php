<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Help;
use PHPBootstrap\Widget\Form\Inputable;
use PHPBootstrap\Widget\Form\Controls\Decorator\Embed;
use PHPBootstrap\Widget\Form\Controls\AbstractInputEntry;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\AbstractContainer;

/**
 * Construtor de Formulario
 */
class BuilderForm extends Form {
	
	/**
	 * Construtor
	 * 
	 * @param string $name
	 */
	public function __construct( $name, Component $component = null) {
		parent::__construct($name);
		$this->setStyle(Form::Horizontal);
		$this->attach(Form::Validate, function( Form $form ) use ( $component ) {
			$invalid = false;
			foreach( $form->getControls() as $input ) {
				if ( ! $input->valid() ) {
					do {
						$parent = $input->getParent();
						if ( $parent instanceof ControlGroup ) {
							$parent->setHelp(new Help(current($input->getFailMessages())));
							$parent->setSeverity(ControlGroup::Error);
							break;
						}
					} while ( $parent === null);
					$invalid = true;
				}
			}
		});
	}
	
	/**
	 * Constroi um campo
	 *
	 * @param string $label
	 * @param array|Inputable $inputs
	 * @param boolean $sanitize
	 * @param AbstractContainer $container
	 * @return ControlGroup
	 */
	public function buildField( $label, $inputs, $sanitize = null, AbstractContainer $container = null ) {
		if ( $sanitize === null ) {
			$sanitize = true;
		}
		if( !is_array($inputs) ) {
			$inputs = array($inputs);
		}
		foreach( $inputs as $input ) {
			if ( $input instanceof Inputable ) {
				if ( $input instanceof Embed ) {
					$this->register($input->getInput());
				} else {
					$this->register($input);
				}
				if ( $sanitize && $input instanceof AbstractInputEntry ) {
					$input->addFilter('trim');
					$input->addFilter('strip_tags');
				}
			}
		}
		if ( $label !== null )  {
			$input = reset($inputs);
			if ( ! $input instanceof Inputable )  {
				$input = null;
			}
			$label = new Label($label, $input);
		}
		$control = new ControlGroup($label, $inputs);
		if ( func_num_args() < 4 ) {
			$this->append($control);
		} elseif ( $container !== null ) {
			$container->append($control);
			$this->append($container);	
		}
		return $control;
	}
	
	/**
	 * Constroi um botão
	 *
	 * @param string $name
	 * @param string $label
	 * @param Action|Togglable $toggle
	 * @param string|array $style
	 * @return Button
	 */
	public function buildButton( $name, $label, $toggle, $style = null ) {
		if ( $toggle instanceof Action ) {
			$toggle = $name == 'submit' ? new TgFormSubmit($toggle, $this) : new TgLink($toggle);
		}
		$style = func_num_args() < 4 && $name == 'submit' ? Button::Primary : $style;
		$button = new Button($label, $toggle, $style);
		$button->setName($name);
		$this->addButton($button);
		return $button;
	}
}
?>