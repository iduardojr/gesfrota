<?php
namespace Sigmat\View\Agency;

use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Misc\Label;
use Sigmat\View\AbstractList;

class AgencyList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $remove
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $remove ) {
		$panel = $this->buildPanel('Administração', 'Gerenciar Orgãos');
		$modalConfirm = $this->buildConfirm('confirm-remove', new Paragraph('Deseja realmente excluir esse orgão e todos os seus cadastros?'));
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary));
		
		$table = $this->buildTable('agency-table');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId();
		$table->buildColumnText('acronym', 'Sigla', clone $filter, 200, ColumnText::Left);
		$table->buildColumnText('name', 'Nome', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('status', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('remove', new Icon('icon-remove'), $remove, $modalConfirm);
		
	}
	
}
?>