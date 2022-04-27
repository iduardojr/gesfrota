<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Vehicle;
use PHPBootstrap\Widget\Misc\Badge;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Table\ColumnText;

class TransferFleetTable extends TransferTable  {
	
	protected function create() {
		$this->buildColumnText('code', 'Ativo', null, 80);
		$this->buildColumnText('description', null, null, null, ColumnText::Left);
		$this->buildColumnText('fleet', 'Frota', null, 100, null, function ($value) {
			return FleetItem::getFleetAllowed()[$value];
		});
	}
	
}
?>