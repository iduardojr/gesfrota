<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;
use Gesfrota\Model\Domain\FleetItem;

class FleetVehicleTable extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('fleet-vehicle-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('description', 'Veículo', null, 400, ColumnText::Left);
		$this->buildColumnText('fleet', 'Frota', null, null, null, function ($value) {
			return FleetItem::getFleetAllowed()[$value];
		});
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, Vehicle $obj ) {
			$data['vehicle-id'] = $obj->getId();
			$data['vehicle-plate'] = $obj->getPlate();
			$data['vehicle-description'] = $obj->getDescription();
			$data['vehicle-fleet'] = $obj->getFleet();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>