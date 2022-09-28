<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgWindows;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;
use Gesfrota\Model\Domain\Disposal;

class DisposalItemTable extends BuilderTable {
	
	/**
	 * @param Action $survey
	 * @param Action $remove
	 * @param Action $print
	 * @param boolean $showAgency
	 */
    public function __construct(Action $survey = null, Action $remove = null, Action $print = null, $showAgency = false) {
		parent::__construct('disposal-items-table');

		$this->buildColumnText('code', '#', null, 80);
		$this->buildColumnText('description', 'Ativo', null, 450, ColumnText::Left);
		if ($showAgency) {
    		$this->buildColumnText('disposal', 'Órgão', null, 80, null, function (Disposal $value) {
    		   return $value->getAgency()->getAcronym(); 
    		});
		}
		$this->buildColumnText('classification', 'Classificação', null, 75, null, function ( $value ) {
			if ($value > 0) {
				return DisposalItem::getClassificationAllowed()[$value];
			}
			return '-';
		});
		
	    $this->buildColumnText('conservation', 'Conservação', null, 100, null, function ($value){
	        if ($value > 0) {
	            return DisposalItem::getConservationAllowed()[$value];
	        }
	        return '-';
	    });
		
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
		if ($print) {
		    $this->buildColumnAction('print', new Icon('icon-print'), new TgWindows($print, 1024, 720));
		}
		
	}
}
?>