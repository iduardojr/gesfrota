<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Pagination\Pagination;
use PHPBootstrap\Widget\Table\ColumnText;

class AdministrativeUnitTable extends BuilderTable  {
	
	/**
	 * @param Action $filter
	 */
	public function __construct( Action $filter ) {
		parent::__construct('administrative-unit-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('partialDescription', 'Descrição', null, 900, ColumnText::Left);
		
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, AdministrativeUnit $unit ) {
			$data['administrative-unit-id'] = $unit->getCode();
			$data['administrative-unit-name'] = $unit->getName();
			$data['administrative-unit-description'] = $unit->getPartialDescription();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>