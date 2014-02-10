<?php
namespace Sigmat\View\GUI;

use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Table\Table;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Pagination\AbstractPagination;


class PanelQuery extends Box {
	
	/**
	 * Construtor 
	 * 
	 * @param Table $table
	 * @param Action $action
	 * @param string $query
	 */
	public function __construct( Table $table, Action $action, $query = null ) {
		parent::__construct();
		
		$this->setName($table->getName() . '-panel');
		
		$action->setParameter('query', null);
		$action->setParameter('page', null);
		
		$input = new SearchBox('query', $action);
		$input->setValue($query);
		$input->setSpan(4);
		$input->setPlaceholder('Busca');
			
		$this->append(new Panel($input, 'panel-query'));
		$pagination = $table->getPagination();
		if ( $pagination instanceof AbstractPagination ) {
			$toggle = $pagination->getToggle();
			if ( $toggle instanceof TgAjax ) {
				$toggle->setTarget($this);
			}
		}
		$this->append($table);
	}
}
?>