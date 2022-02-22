<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\VehicleMaker;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;

class VehicleMakerTable extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('vehicle-maker-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('name', 'Descrição', null, null, ColumnText::Left);
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, VehicleMaker $object ) {
		    $data['vehicle-maker-id'] = $object->getCode();
		    $data['vehicle-maker-name'] = $object->getName();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>