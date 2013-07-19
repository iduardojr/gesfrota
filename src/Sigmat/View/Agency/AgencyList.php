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
		
		$this->buildTable('agency-table', clone $filter);
		
		$this->buildColumnText('id', '#', clone $filter, 70, null, function( $value ) {
			return str_repeat('0', 3 - strlen($value)) . $value; 
		});
		$this->buildColumnText('acronym', 'Sigla', clone $filter, 200, ColumnText::Left);
		$this->buildColumnText('name', 'Nome', clone $filter, null, ColumnText::Left);
		$this->buildColumnText('status', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$this->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$this->buildColumnAction('remove', new Icon('icon-remove'), $remove, $modalConfirm);
		
	}
	
}
?>