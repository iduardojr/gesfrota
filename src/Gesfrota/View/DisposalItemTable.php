<?php
namespace Gesfrota\View;

use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Table\ColumnText;
use Gesfrota\Model\Domain\DisposalItem;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;

class DisposalItemTable extends BuilderTable {
	
	/**
	 * @param Action $survey
	 * @param Action $remove
	 */
    public function __construct(Action $survey, Action $remove) {
		parent::__construct('disposal-items-table');

		$this->buildColumnText('code', '#', null, 80);
		$this->buildColumnText('description', 'Ativo', null, 500, ColumnText::Left);
		$this->buildColumnText('classification', 'Classificação', null, 75, null, function ( $value ) {
			if ($value > 0) {
				return DisposalItem::getClassificationAllowed()[$value];
			}
			return '-';
		});
		
		$this->buildColumnText('rating', null, null, 100);
		
		$this->buildColumnText('value', 'Valor', null, null, null, function ( $value ) {
			if ($value > 0) {
				return 'R$ ' . number_format($value, 2, ',', '.');
			}
			return '-';
		});
		
		$this->buildColumnText('debit', 'Débitos', null, null, null, function ( $value ) {
			if ($value > 0) {
				return 'R$ ' . number_format($value, 2, ',', '.');
			}
			return '-';
		});
		
		$this->buildColumnAction('survey', new Icon('icon-list-alt'), $survey);
		$this->buildColumnAction('remove', new Icon('icon-remove'), new TgAjax($remove, 'flash-message', TgAjax::Json));
	}
	
}
?>