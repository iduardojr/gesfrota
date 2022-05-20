<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\ResultCenter;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Pagination\Pagination;
use PHPBootstrap\Widget\Table\ColumnText;

class ResultCenterTable extends BuilderTable  {
	
	/**
	 * @param Action $filter
	 */
	public function __construct( Action $filter ) {
		parent::__construct('result-center-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this))->setSize(Pagination::Mini);
				
		$this->buildColumnTextId();
		$this->buildColumnText('description', 'Descrição', null, 400, ColumnText::Left);
		
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, ResultCenter $unit ) {
			$data['result-center-id'] = $unit->getCode();
			$data['result-center-description'] = $unit->getDescription();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>