<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\DriverLicense;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Table\ColumnText;

class DriverTable extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('driver-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('name', 'Motorista', null, null, ColumnText::Left);
		$this->buildColumnText('driverLicense', 'CNH', null, null, null, function (DriverLicense $value) {
			return implode('', $value->getCategories());
		});
		$this->buildColumnText('driverLicense', '', null, null, null, function (DriverLicense $value) {
			$now = new \DateTime();
			return new Label($value->getExpires()->format('d/m/Y'), $value->getExpires() > $now ? Label::Success : Label::Important);
		});
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, User $obj ) {
			$data['driver-id'] = $obj->getCode();
			$data['driver-name'] = $obj->getName();
			$data['driver-nif'] = $obj->getNif();
			$data['driver-cell'] = $obj->getCell();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>