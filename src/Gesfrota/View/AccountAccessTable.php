<?php
namespace Gesfrota\View;


use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Button\Button;
use Gesfrota\Model\Domain\Agency;
use PHPBootstrap\Widget\Misc\Label;
use Gesfrota\View\Widget\AbstractList;

class AccountAccessTable extends AbstractList {
	
	/**
	 * Construtor
	 */
	public function __construct( Action $access, Agency $active ) {
		$this->buildPanel('Minha Conta', 'Alterar órgão de acesso');
		
		$table = $this->buildTable('account-access-table');
		
		$table->buildColumnTextId();
		$table->buildColumnText('acronym', 'Sigla', null, 70);
		$table->buildColumnText('name', 'Descrição', null, null, ColumnText::Left);
		$table->buildColumnText('active', 'Status', null, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('select', new Icon('icon-ok'), $access, null, function (Button $btn, Agency $obj) use ($active) {
			if ($obj == $active) {
				$btn->setDisabled(true);
				$btn->setIcon(new Icon('icon-asterisk'));
			}
		});
			
	}
}
?>