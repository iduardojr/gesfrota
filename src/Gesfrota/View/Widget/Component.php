<?php
namespace Gesfrota\View\Widget;

use PHPBootstrap\Widget\Widget;
use PHPBootstrap\Widget\Renderable;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Misc\Alert;

/**
 * Componente
 */
class Component implements Renderable {
	
	/**
	 * @var Box
	 */
	protected $panel;
	
	/**
	 * @var Panel
	 */
	protected $alert;
	
	/**
	 * @var Widget
	 */
	protected $component;
	
	/**
	 * Atribui alerta
	 * 
	 * @param Alert $alert
	 */
	public function setAlert( Alert $alert = null ) {
	    $this->alert->setContent($alert);
	}
	
	/**
	 * @return Box
	 */
	public function getPanel() {
		return $this->panel;
	}
	
	/**
	 * Constroi o panel
	 * 
	 * @param string $title
	 * @param string $subtext
	 * @return Box
	 */
	protected function buildPanel( $title = null, $subtext = null ) {
		if ( ! isset($this->panel) ) {
			$panel = new Box();
			if ( func_num_args() ) {
				$panel->append($this->buildHeader($title, $subtext));
			}
			$this->alert = new Panel(null, 'flash-message');
			$panel->append($this->alert);
			$this->panel = $panel;
		}
		return $this->panel;
	}
	
	/**
	 * Constroi o cabeçalho
	 * 
	 * @param string $title
	 * @param string $subtext
	 * @return Widget
	 */
	protected function buildHeader( $title, $subtext ) {
		$header = new Title($title, 2);
		$header->setName('header');
		$header->setSubtext($subtext);
		$header->setPageHeader(true);
		return $header;
	}
	
	/**
	 * Renderizar
	 * 
	 * @throws \RuntimeException
	 */
	public function render() {
		if ( ! isset($this->panel) ) {
			throw new \RuntimeException('panel not builder');
		}
		$this->panel->render();
	}
	
}
?>