<?php
namespace Gesfrota\View\Widget;

use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Pagination\AbstractPagination;
use PHPBootstrap\Widget\Table\Table;


class PanelQuery extends Box {
	
	/**
	 * Construtor 
	 * 
	 * @param Table $table
	 * @param Action $action
	 * @param string $query
	 * @param Modal|string $modal
	 */
	public function __construct( Table $table, Action $action, $query = null, $modal = null ) {
		parent::__construct();
		
		$this->setName($table->getName() . '-panel');
		
		$action->setParameter('query', null);
		$action->setParameter('page', null);
		
		if (! $modal instanceof Modal) {
			$modal = new Modal($modal, new Title(''));
		}
		
		$input = new SearchBox($table->getName() . '-query', $action, $modal);
		$input->setValue($query);
		$input->setSpan(4);
		$input->setPlaceholder('Busca');
			
		$this->append(new Panel($input, $table->getName() . '-panel-query'));
		$pagination = $table->getPagination();
		if ( $pagination instanceof AbstractPagination ) {
			$toggle = $pagination->getToggle();
			if ( $toggle instanceof TgAjax ) {
				$toggle->setTarget($this);
			}
		}
		$columns = $table->getColumns();
		foreach ($columns as $col) {
			$toggle = $col->getToggle();
			if ( $toggle instanceof TgAjax ) {
				$toggle->setTarget($this);
			}
		}
		$this->append($table);
	}
}
?>