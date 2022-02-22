<?php
namespace Gesfrota\View\Widget;

class DynInputRemove extends DynInputAdd {
	
	// ID Renderizador
	const RendererType = 'gesfrota.view.widget.input.dynamic.toogle.remove';
	
	/**
	 * Index
	 * @var integer
	 */
	protected $index;
	
	/**
	 * @param DynInput $target
	 * @param integer $index
	 */
	public function __construct(DynInput $target, $index) {
		parent::__construct($target);
		$this->setIndex($index);
	}
	
	/**
	 * @return number
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * @param number $index
	 */
	public function setIndex($index) {
		$this->index = $index;
	}

	
}
?>
