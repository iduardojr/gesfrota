<?php
namespace Gesfrota\View\Widget;

use PHPBootstrap\Widget\AbstractComponent;
use PHPBootstrap\Widget\Layout\Box;

class Direction extends AbstractComponent {
	
	// ID Renderizador
	const RendererType = 'gesfrota.view.widget.direction';
	
	/**
	 * Lugar de Partida
	 * 
	 * @var PlaceInput
	 */
	protected $from;
	
	/**
	 * Lugar de Destino
	 * 
	 * @var PlaceInput
	 */
	protected $to;
	
	/**
	 * Pontos de Parada
	 * 
	 * @var WaypointsInput
	 */
	protected $way;
	
	/**
	 * @var Box
	 */
	protected $component;
	
	/**
	 * Marcador
	 * 
	 * @var string
	 */
	protected $marker;
	
	/**
	 * Índice do Marcador
	 * 
	 * @var integer
	 */
	protected $index;
	
	/**
	 * @var array
	 */
	protected $options;
	
	/**
	 * @param string $name
	 */
	public function __construct($name, $marker) {
		$this->setOptions([], []);
		$this->component = new Box();
		$this->marker = $marker;
		$this->index = 1;
		$this->setName($name);
	}
	
	/**
	 * @return array
	 */
	public function getOptionsMap() {
		return $this->options['map'];
	}
	
	/**
	 * @return array
	 */
	public function getOptionsRequest() {
		return $this->options['request'];
	}
	
	/**
	 * @param array $map
	 * @param array $request
	 */
	public function setOptions(array $map, array $request = []) {
		$this->options['map'] = $map;
		$this->options['request'] = $request;
	}
	
	public function getComponent() {
		return $this->component;
	}
	
	/**
	 * @return PlaceInput
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * @param PlaceInput $input
	 */
	public function setFrom(PlaceInput $input) {
		if (!empty($this->from)) {
			$this->component->remove($this->from->getParent());
		}
		$input->setDirection($this);
		$this->from = $input;
	}

	/**
	 * @return PlaceInput
	 */
	public function getTo() {
		return $this->to;
	}

	/**
	 * @param PlaceInput $input
	 */
	public function setTo(PlaceInput $input) {
		if (!empty($this->to)) {
			$this->component->remove($this->to->getParent());
		}
		$input->setDirection($this);
		$this->to = $input;
	}

	/**
	 * @return WaypointsInput
	 */
	public function getWay() {
		return $this->way;
	}

	/**
	 * @param WaypointsInput $way
	 */
	public function setWay(WaypointsInput $input) {
		if (!empty($this->way)) {
			$this->component->remove($this->way);
		}
		$input->setDirection($this);
		$this->way = $input;
	}
	
	/**
	 * @return string
	 */
	public function getMarkerInstance() {
		return $this->format($this->marker, $this->index++);
	}
	
	/**
	 * @return string
	 */
	public function getMarker() {
		return $this->marker;
	}
	
	protected function format($marker, $index) {
		$seq = function ($i) use (&$seq){
			if ($i > 25) {
				return $seq(($i/26)-1). $seq($i%26);
			}
			return chr(65+$i);
		};
		$search = ['{0}', '{A}', '{a}'];
		$replace = [$index, $seq($index-1), strtolower($seq($index-1))];
		return str_replace($search, $replace, $marker);
	}
	
}
?>
