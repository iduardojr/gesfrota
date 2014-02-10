<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Pagination\Pagination;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use Sigmat\View\GUI\BuilderTable;
use Sigmat\Model\Domain\AdministrativeUnit;

class AdministrativeUnitTable extends BuilderTable  {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('administrative-unit-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this))->setSize(Pagination::Mini);
				
		$this->buildColumnTextId();
		$this->buildColumnText('fullDescription', 'Descrição', null, 900, ColumnText::Left);
		
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, AdministrativeUnit $unit ) {
			$data['administrative-unit-id'] = $unit->getCode();
			$data['administrative-unit-description'] = $unit->getFullDescription();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>