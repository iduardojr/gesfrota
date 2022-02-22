<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\VehicleFamily;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;

class VehicleFamilyTable extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('vehicle-family-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('name', 'Descrição', null, null, ColumnText::Left);
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, VehicleFamily $family ) {
		    $data['vehicle-family-id'] = $family->getCode();
		    $data['vehicle-family-name'] = $family->getName();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>