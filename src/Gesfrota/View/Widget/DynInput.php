<?php
namespace Gesfrota\View\Widget;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\AbstractComponent;
use PHPBootstrap\Validate\Measurable;
use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Validate\Validator;
use PHPBootstrap\Validate\Measure\Ruler\RulerLength;
use PHPBootstrap\Validate\Measure\Max;
use PHPBootstrap\Validate\Measure\Min;
use PHPBootstrap\Validate\Measure\Range;
use PHPBootstrap\Widget\Form\Inputable;

class DynInput extends AbstractComponent implements Inputable {
	
	// ID Renderizador
	const RendererType = 'gesfrota.view.widget.input.dynamic';
	
	/**
	 * Rótulo
	 * @var string
	 */
	protected $label;
	
	/**
	 * TextBox
	 * 
	 * @var TextBox
	 */
	protected $input;
	
	/**
	 * Valores
	 * 
	 * @var array
	 */
	protected $value;
	
	/**
	 * Validação
	 * 
	 * @var boolean
	 */
	protected $valid;
	
	/**
	 * @var Validator
	 */
	protected $validator;
	
	/**
	 * @param string $name
	 */
	public function __construct($name) {
		$this->input = new TextBox($name);
		$this->input->addFilter('trim');
		$this->input->addFilter('strip_tags');
		$this->validator = new Validator();
		$this->value = [];
	}
	
	/**
	 * @param Measurable $rule
	 */
	public function setLength(Measurable $rule = null) {
		if (! empty($rule)) {
			$rule->setRuler(RulerLength::getInstance());
		}
		$this->validator->setLength($rule);
	}
	
	/**
	 * @return Measurable
	 */
	public function getLength() {
		return $this->validator->getLength();
	}
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function setValue($values) {
		if (empty($values)) {
			$this->value = [];
		} else {
			if (! is_array($values) ) {
				$values = [$values];
			}
			foreach ($values as $key => $value) {
				$this->input->setText($value);
				$values[$key] = $this->input->getValue();
			}
			$this->value = $values;
		}
	}
	
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * @return TextBox
	 */
	public function getComponent() {
		return $this->input;
	}
	
	public function prepare(Form $form) {
		$this->input->prepare($form);
	}

	public function valid() {
		if ( $this->valid === null ) {
			$this->valid = false;
			if ($this->validator->valid($this->value)) {
				foreach ($this->value as $val) {
					$this->input->setValue($val);
					if (! $this->input->valid() ) {
						break;
					}
				}
				$this->valid = true;
			}
		}
		return $this->valid;
	}
	
	public function getFailMessages() {
		if ( $this->valid === null ) {
			$this->valid();
		}
		return array_merge($this->validator->getMessages(), $this->input->getFailMessages());
	}

	/**
	 * @return integer
	 */
	public function getInputMin() {
		if (empty($this->getLength()) || $this->getLength() instanceof Max) {
			return 0;
		}
		if ($this->getLength() instanceof Min) {
			return $this->getLength()->getContext();
		}
		if ($this->getLength() instanceof Range) {
			return $this->getLength()->getContext()[0];
		}
	}
	
	/**
	 * @return integer
	 */
	public function getInputMax() {
		if (empty($this->getLength()) || $this->getLength() instanceof Min) {
			return 0;
		}
		if ($this->getLength() instanceof Max) {
			return $this->getLength()->getContext();
		}
		if ($this->getLength() instanceof Range) {
			return $this->getLength()->getContext()[1];
		}
	}
	
	/**
	 * @param boolean $disabled
	 */
	public function setDisabled($disabled) {
		$this->input->setDisabled($disabled);
	}
	
	/**
	 * @return boolean
	 */
	public function getDisabled() {
		return $this->input->getDisabled();
	}
	
}
?>