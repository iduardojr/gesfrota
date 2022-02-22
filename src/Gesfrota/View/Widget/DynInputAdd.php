<?php
namespace Gesfrota\View\Widget;

use PHPBootstrap\Widget\AbstractRender;
use PHPBootstrap\Widget\Toggle\Togglable;

class DynInputAdd extends AbstractRender implements Togglable {
	
	// ID Renderizador
	const RendererType = 'gesfrota.view.widget.input.dynamic.toggle.add';
	
	/**
	 * Alvo
	 *
	 * @var DynInput
	 */
	protected $target;
	
	/**
	 * @param DynInput $target
	 */
	public function __construct(DynInput $target) {
		$this->setTarget($target);
	}
	
	
	/**
	 * @return DynInput
	 */
	public function getTarget() {
		return $this->target;
	}

	/**
	 * @param DynInput $target
	 */
	public function setTarget(DynInput $target) {
		$this->target = $target;
	}
	
}
?>
