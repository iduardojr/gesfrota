<?php
namespace Sigmat\View\Agency;

use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Misc\Paragraph;
use Sigmat\View\AbstractList;
use Sigmat\View\EntityDatasource;

class AgencyList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param EntityDatasource $datasource
	 * @param Action $action
	 */
	public function __construct( EntityDatasource $datasource, Action $action ) {
		$panel = $this->buildPanel('Administração', 'Gerenciar Orgãos');
		$confirm = $this->buildConfirm('confirm-remove', new Paragraph('Deseja realmente excluir esse orgão e todos os cadastros relacionados?'));
		
		$form = new FormFilter();
		$form->bind($datasource->getFilter());
		$datasource->setFilter($form->getData());
		
		$filtered = 'Filtrar';
		foreach ( $datasource->getFilter() as $data ) {
			if ( !empty($data) ) {
				$filtered = 'Filtrados<span class="badge badge-important">' . $datasource->getTotal() . '</span>';
				break;
			}
		}
		
		$filter = $this->buildFilter($form, clone $action, clone $action );
		$new = clone $action;
		$new->setMethodName('new');
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary),
							new Button(array($filtered, new Icon('icon-filter')), new TgModalOpen($filter), Button::Link));
		
		
		
		$this->buildTable('agency-table', $datasource, clone $action);
		
		$this->buildColumnText('id', '#', clone $action, 70, null, function( $value ) {
			return str_repeat('0', 3 - strlen($value)) . $value; 
		});
		$this->buildColumnText('acronym', 'Sigla', clone $action, 200, ColumnText::Left);
		$this->buildColumnText('name', 'Nome', clone $action, null, ColumnText::Left);
		$this->buildColumnText('status', 'Status', clone $action, 70, null, function ( $value ) {
			return $value ? '<span class="label label-success">Ativo<span>' : '<span class="label label-important">Inativo</span>';
		});
		$this->buildColumnAction('edit', 'icon-pencil', clone $action);
		$this->buildColumnAction('remove', 'icon-remove', clone $action, $confirm);
		
	}
}
?>