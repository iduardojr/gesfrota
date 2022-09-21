<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgWindows;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Paragraph;

class DisposalItemTable extends BuilderTable {
	
	/**
	 * @param Action $survey
	 * @param Action $print
	 * @param Action $remove
	 */
    public function __construct(Action $print, Action $survey = null, Action $remove = null) {
		parent::__construct('disposal-items-table');

		$this->buildColumnText('code', '#', null, 80);
		$this->buildColumnText('description', 'Ativo', null, 450, ColumnText::Left);
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
		
		if ($survey) {
		    $this->buildColumnAction('survey', new Icon('icon-list-alt'), $survey);
		}
		if ($remove) {
		    $this->buildColumnAction('remove', new Icon('icon-remove'), new TgAjax($remove, 'flash-message', TgAjax::Json));
		}
		$this->buildColumnAction('print', new Icon('icon-print'), new TgWindows($print, 1024, 720));
		
	}
	
}
?>