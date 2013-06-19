<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Form\Inputable;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Help;
use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Form\Controls\AbstractInputEntry;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Widget;
use PHPBootstrap\Widget\Misc\Alert;

abstract class AbstractForm extends Component {
	
	/**
	 * @var boolean
	 */
	protected $attached;
	
	/**
	 * @var Form
	 */
	protected $component;

	/**
	 * Liga os dados submetidos ao formulario
	 *
	 * @param array $submittedData
	 */
	public function bind( array $submittedData ) {
		$this->component->bind($submittedData);
	}

	/**
	 * Prepara o formulario e seus controles para a renderização
	 */
	public function prepare() {
		$this->component->prepare();
	}

	/**
	 * Valida o formulário
	 *
	 * @return boolean
	 */
	public function valid() {
		return $this->component->valid();
	}

	/**
	 * Obtem as messagens de erro
	 *
	 * @return ArrayIterator
	*/
	public function getMessages() {
		return $this->component->getFailMessages();
	}

	/**
	 * Obtem um input a partir do nome
	 *
	 * @param string $name
	 * @return Inputable
	 */
	public function getInputByName( $name ) {
		$input = $this->component->getByName($name); 
		if ( $input instanceof Inputable ) {
			return $input;
		}
		return null;
	}

	/**
	 * Obtem um botão a partir do nome
	 *
	 * @param string $name
	 * @return Button
	 */
	public function getButtonByName( $name ) {
		return $this->component->getButtonByName($name);
	}
	
	/**
	 * Extrai os dados do objeto para o formulario
	 *
	 * @param object $object
	 */
	public function extract( $object ) {
		throw new \BadMethodCallException('unsupported method');
	}
	
	/**
	 * Hidrata o objeto com os valores do formulario
	 *
	 * @param object $object
	*/
	public function hydrate( $object ) {
		throw new \BadMethodCallException('unsupported method');
	}
	
	/**
	 * Constroi o formulario
	 * 
	 * @param string $name
	 * @return Form
	 */
	protected function  buildForm($name) {
		if ( ! isset($this->component) ) {
			$form = new Form($name);
			$form->setStyle(Form::Horizontal);
			$component = $this;
			$form->attach(Form::Validate, function( Form $form ) use ( $component ) {
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
				if ( $invalid ) {
					$component->setAlert(new Alert('<strong>Ops! </strong>Por favor, verifique se os campos estão corretamente preenchidos.', Alert::Error));
				}
			});
			
			$this->component = $form;
			$this->panel->append($form);
		}
		return $this->component;
	}
	
	/**
	 * Constroi um campo
	 * 
	 * @param string $label
	 * @param array|Inputable $inputs
	 * @param boolean $sanitize
	 * @return Widget
	 */
	protected function buildField( $label, $inputs, $sanitize = true ) {
		if( !is_array($inputs) ) {
			$inputs = array($inputs);
		}
		foreach( $inputs as $input ) {
			$this->component->register($input);
			if ( $sanitize && $input instanceof AbstractInputEntry ) {
				$input->addFilter('trim');
				$input->addFilter('strip_tags');
			}
		}
		if ( $label !== null )  {
			$label = new Label($label, reset($inputs));
		}
		$control = new ControlGroup($label, $inputs);
		$this->component->append($control);
		return $control;
	}
	
	/**
	 * Constroi um botão
	 * 
	 * @param string $name
	 * @param string $label
	 * @param Action $action
	 * @param string $style
	 * @return Button
	 */
	protected function buildButton( $name, $label, Action $action, $style = null ) {
		$toggle = $name == 'submit' ? new TgFormSubmit($action, $this->component) : new TgLink($action);
		$style = func_num_args() < 4 && $name == 'submit' ? Button::Primary : $style;
		$button = new Button($label, $toggle, $style);
		$button->setName($name);
		$this->component->addButton($button);
		return $button;
	}

}
?>