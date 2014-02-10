<?php
namespace Sigmat\View\GUI;

use PHPBootstrap\Widget\Widget;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalConfirm;
use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Button\ButtonToolbar;
use PHPBootstrap\Widget\Button\ButtonGroup;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Table\Table;
use PHPBootstrap\Widget\Misc\Title;

abstract class AbstractList extends Component {
	
	/**
	 * @var Table
	 */
	protected $component;
	
	/**
	 * Atribui um datasource
	 *
	 * @param EntityDatasource $datasource
	 */
	public function setDatasource( EntityDatasource $datasource ) {
		$this->component->setDataSource($datasource);
	}
	
	/**
	 * Constroi um modal para filtro
	 * 
	 * @param Form $form
	 * @param Action $submit
	 * @param Action $reset
	 * @return Modal
	 */
	protected function buildFilter( Form $form, Action $submit, Action $reset = null ) {
		$modal = new Modal('modal-filter', new Title('Filtrar?', 3));
		$modal->setWidth(600);
		$modal->setBody($form);
		$modal->addButton(new Button('Filtrar', new TgFormSubmit($submit, $form), Button::Primary));
		if ( $reset ) {
			$modal->addButton(new Button('Remover Filtros', new TgLink($reset)));
		}
		$this->panel->append($modal);
		return $modal;
	}
	
	/**
	 * Obtem o formulário do filtro
	 * 
	 * @return Form
	 */
	public function getFormFilter() {
		$modalFilter = $this->panel->getByName('modal-filter');
		return $modalFilter ? $modalFilter->getBody() : null;
	}
	
	/**
	 * Constroi um modal de confirmação
	 * 
	 * @param string $name
	 * @param Widget $body
	 * @return Modal
	 */
	protected function buildConfirm( $name, Widget $body ) {
		$modal = new Modal($name, new Title('Confirmar?', 3));
		$modal->setBody($body);
		$modal->addButton(new Button('Ok', new TgModalConfirm(), Button::Primary));
		$modal->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($modal);
		return $modal;
	}
	
	/**
	 * Constroi uma barra de ferramentas
	 * 
	 * @param array $buttons
	 * @param ...
	 * @return ButtonToolbar
	 */
	protected function buildToolbar( $buttons ) {
		$toolbar = new ButtonToolbar('toolbar');
		foreach ( func_get_args() as $group ) {
			if ( $group instanceof ButtonGroup) {
				$toolbar->addButtonGroup($group);
				continue;
			}
			if ( ! is_array($group) ) {
				$group = array($group);
			}
			$btnGroup = new ButtonGroup();
			$toolbar->addButtonGroup($btnGroup);
			foreach( $group as $button ) {
				$btnGroup->addButton($button);
			}
		}
		$this->panel->append($toolbar);
		return $toolbar;
	}
	
	/**
	 * Obtem a barra de ferramentas
	 * 
	 * @return ButtonToolbar
	 */
	public function getToolbar() {
		return $this->panel->getByName('toolbar');
	}
	
	/**
	 * Constroi uma tabela
	 * 
	 * @param string $name
	 * @param Action $pager
	 * @return BuilderTable
	 */
	protected function buildTable( $name ) {
		if ( !isset($this->component) ) {
			$this->component = new BuilderTable($name);
			$this->panel->append($this->component);
		}
		return $this->component;
	}
	
}
?>