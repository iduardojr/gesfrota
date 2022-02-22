<?php
namespace Gesfrota\View\Widget;

use PHPBootstrap\Validate\Requirable;
use PHPBootstrap\Widget\AbstractComponent;
use PHPBootstrap\Widget\Form\Controls\Decorator\InputQuery;
use PHPBootstrap\Widget\Form\Controls\Decorator\Validate;
use PHPBootstrap\Widget\Form\Controls\Decorator\InputContext;
use PHPBootstrap\Widget\Form\Controls\Decorator\Embed;
use PHPBootstrap\Widget\Form\TextEditable;
use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Form\Controls\Decorator\AddOn;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Suggest;
use PHPBootstrap\Widget\Action\Action;

/**
 * Campo de Lugar
 */
class PlaceInput extends AbstractComponent implements TextEditable, InputQuery, InputContext, DirectionInput {
	
	// ID Renderizador
	const RendererType = 'gesfrota.view.widget.input.place';
	
	/**
	 * Descrição
	 *
	 * @var TextBox
	 */
	protected $text;
	
	/**
	 * Lugar
	 * 
	 * @var Hidden
	 */
	protected $place;

	/**
	 * Componente
	 *
	 * @var Embed
	 */
	protected $component;

	/**
	 * Marcador
	 *
	 * @var AddOn
	 */
	protected $marker;
	
	/**
	 * Direção
	 * 
	 * @var Direction
	 */
	protected $direction;

	/**
	 * 
	 * @param string $name
	 * @param Action $suggest
	 * @param string $marker
	 * @param number $minlength
	 * @param number $delay
	 */
	public function __construct( $name, Action $suggest, $marker = '', $minlength = 3, $delay = 400 ) {
		$this->text = new TextBox($name . '[description]');
		$this->place = new Hidden($name . '[place]');
		$this->marker = new AddOn($marker);
		$this->component = new Embed($this->text);
		$this->component->append($this->place);
		$this->component->append($this->marker);
		$this->component->setName($name);
		$this->setSuggestion($suggest, $minlength, $delay);
	}
	
	public function setName( $name ) {
		$this->component->setName($name);
		$this->text->setName($name . '[description]');
		$this->place->setName($name . '[place]');
	}
	
	public function getName() {
		return $this->component->getName();
	}
	
	public function prepare( Form $form ) {
		$this->text->prepare($form);
		$this->place->prepare($form);
	}
	
	/**
	 *
	 * @see TextEditable::setText()
	 */
	public function setText( $text ) {
		if (empty($text)) {
			$this->text->setValue(null);
			$this->place->setValue(null);
			return;
		}
		if (! isset($text['description'], $text['place'])) {
			throw new \InvalidArgumentException('place value not is allowed');
		}
		$this->text->setText($text['description']);
		$this->place->setText($text['place']);
	}
	
	/**
	 *
	 * @see TextEditable::getText()
	 */
	public function getText() {
		return ['description' => $this->text->getText(),
				'place' => $this->place->getText()
		];
	}
	
	public function getValue() {
		return ['description' => $this->text->getValue(),
				'place' => $this->place->getValue()
		];
	}
	
	public function setValue( $value ) {
		if (empty($value)) {
			$this->text->setValue(null);
			$this->place->setValue(null);
			return;
		}
		if (! isset($value['description'], $value['place'])) {
			throw new \InvalidArgumentException('place value not is allowed');
		}
		$this->text->setValue($value['description']);
		$this->place->setValue($value['place']);
	}
	
	public function valid() {
		return $this->text->valid() && $this->place->valid();
	}
	
	public function getFailMessages() {
		return $this->text->getFailMessages();
	}
	
	/**
	 * Adiciona um filtro de entrada de texto
	 *
	 * @param callback $filter
	 * @throws \InvalidArgumentException
	 */
	public function addFilter( $filter ) {
		$this->text->addFilter($filter);
	}
	
	/**
	 * Remove um filtro de entrada de texto
	 *
	 * @param callback $filter
	 */
	public function removeFilter( $filter ) {
		$this->text->removeFilter($filter);
	}
	
	/**
	 * Obtem Auto complete
	 *
	 * @return boolean
	 */
	public function getAutoComplete() {
		return $this->text->getAutoComplete();
	}
	
	/**
	 * Atribui auto complete
	 *
	 * @param boolean $autoComplete
	 */
	public function setAutoComplete( $autoComplete ) {
		$this->text->setAutoComplete($autoComplete);
	}
	
	/**
	 * Obtem campo desabilitaedo
	 *
	 * @return boolean
	 */
	public function getDisabled() {
		return $this->text->getDisabled();
	}
	
	/**
	 * Atribui campo desabilitado
	 *
	 * @param boolean $disabled
	 */
	public function setDisabled( $disabled ) {
		$this->text->setDisabled($disabled);
		$this->place->setDisabled($disabled);
	}
	
	/**
	 * Atribui o tamanho do input com valores entre 1 e 12
	 *
	 * @param integer $span
	 * @throws \InvalidArgumentException
	 */
	public function setSpan( $span ) {
		$this->text->setSpan($span);
	}
	
	/**
	 * Obtem o tamanho do input
	 *
	 * @return integer
	 */
	public function getSpan() {
		return $this->text->getSpan();
	}
	
	/**
	 * Obtem texto reservado
	 *
	 * @return string
	 */
	public function getPlaceholder() {
		return $this->text->getPlaceholder();
	}
	
	/**
	 * Atribui texto reservado
	 *
	 * @param string $placeholder
	 */
	public function setPlaceholder( $placeholder ) {
		$this->text->setPlaceholder($placeholder);
	}
	
	/**
	 * Obtem campo requerido
	 *
	 * @return Requirable
	 */
	public function getRequired() {
		return $this->text->getRequired();
	}
	
	/**
	 * Atribui campo requerido
	 *
	 * @param Requirable $rule
	 */
	public function setRequired( Requirable $rule = null ) {
		$this->text->setRequired($rule);
		$this->place->setRequired($rule);
	}
	
	/**
	 * Obtem arredondamento
	 *
	 * @return boolean
	 */
	public function getRounded() {
		return $this->text->getRounded();
	}
	
	/**
	 * Atribui arredondamento
	 *
	 * @param boolean $rounded
	 */
	public function setRounded( $rounded ) {
		$this->text->setRounded($rounded);
	}
	
	/**
	 * Obtem as regras de validação
	 *
	 * @return Validate
	 */
	public function getValidate() {
		return $this->text->getValidate();
	}
	
	/**
	 * Atribui uma sugestão
	 * 
	 * @param Action $suggest
	 * @param integer $minlength
	 * @param integer $delay
	 */
	public function setSuggestion(Action $suggest, $minlength = 3, $delay = 400) {
		$this->text->setSuggestion(new Suggest($suggest, $minlength, $delay, 20, false, false));
	}
	
	/**
	 * Obtem a sugestão 
	 * 
	 * @return Suggest
	 */
	public function getSuggestion() {
		return $this->text->getSuggestion();
	}

	/**
	 *
	 * @see Component::getComponent()
	 */
	public function getComponent() {
		return $this->component;
	}

	/**
	 * Define o marcador a esquerda
	 */
	public function setMarkerToLeft() {
		$this->component->remove($this->marker);
		$this->component->remove($this->place);
		$this->component->prepend($this->place);
		$this->component->prepend($this->marker);
	}
	
	/**
	 * Define o marcador a direita
	 */
	public function setMarkerToRight() {
		$this->component->remove($this->marker);
		$this->component->remove($this->place);
		$this->component->append($this->place);
		$this->component->append($this->marker);
	}
	
	/**
	 *
	 * @see InputContext::getContextIdentify()
	 */
	public function getContextIdentify() {
		return $this->text->getContextIdentify();
	}
	
	public function getContextValue() {
		return $this->text->getContextValue();
	}
	
	/**
	 * @return string
	 */
	public function getMarker() {
		return $this->marker->getText();
	}

	/**
	 * @param string $marker
	 */
	public function setMarker($marker) {
		$this->marker->setText($marker);
	}
	
	/**
	 * @return Direction
	 */
	public function getDirection() {
		return $this->direction;
	}
	
	/**
	 * @param Direction $direction
	 */
	public function setDirection(Direction $direction) {
		$this->direction = $direction;
	}

	public function __clone() 	{
		$this->text = clone $this->text;
		$this->place = clone $this->place;
		$this->marker = clone $this->marker;
		
		$embed = new Embed($this->text);
		
		$isPrepend = $this->component->isPrepend();
		
		if ($isPrepend) {
			$embed->prepend($this->place);
			$embed->prepend($this->marker);
		} else {
			$embed->append($this->place);
			$embed->append($this->marker);
		}
		$this->component = $embed;
	}

	
}
?>