<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Alert;
use PHPBootstrap\Widget\Renderable;
use PHPBootstrap\Widget\Widget;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Layout\Box;

/**
 * Componente
 */
class Component implements Renderable {
	
	/**
	 * @var Box
	 */
	protected $panel;
	
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
		$this->panel->getByName('flash-message')->setContent($alert);
	}
	
	/**
	 * Constroi o panel
	 * 
	 * @param string $title
	 * @param string $subtext
	 */
	protected function buildPanel( $title, $subtext ) {
		if ( ! isset($this->panel) ) {
			$panel = new Box();
			$panel->append($this->buildHeader($title, $subtext));
			$panel->append(new Panel(null, 'flash-message'));
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