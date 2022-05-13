<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;

class VehicleModelTable extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('vehicle-model-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('name', 'Modelo', null, null, ColumnText::Left);
		$this->buildColumnText('maker', 'Fabricante', null, 3, ColumnText::Left);
		$this->buildColumnText('family', 'Família', null, 3, ColumnText::Left);
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, VehicleModel $object ) {
		    $data['vehicle-model-id'] = $object->getId();
		    $data['vehicle-model-name'] = $object->getName();
		    $data['vehicle-model-fipe'] = $object->getFipe();
		    $data['vehicle-maker-id'] = $object->getMaker()->getCode();
		    $data['vehicle-maker-name'] = $object->getMaker()->getName();
		    $data['vehicle-family-id'] = $object->getFamily()->getCode();
		    $data['vehicle-family-name'] = $object->getFamily()->getName();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>