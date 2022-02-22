<?php
namespace Gesfrota\View\Widget;


use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Validate\Validator;

class WaypointsInput extends DynInput implements DirectionInput {
	
	// ID Renderizador
	const RendererType = 'gesfrota.view.widget.input.waypoints';
	
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
	public function __construct($name, Action $suggest, $marker = '', $minlength = 3, $delay = 400 ) {
		$this->input = new PlaceInput($name, $suggest, $marker, $minlength, $delay); 
		$this->input->addFilter('trim');
		$this->input->addFilter('strip_tags');
		$this->validator = new Validator();
		$this->value = [];
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

}