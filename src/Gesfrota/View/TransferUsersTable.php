<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\Requester;
use PHPBootstrap\Widget\Misc\Badge;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Table\ColumnText;
use Gesfrota\Model\Domain\AdministrativeUnit;

class TransferUsersTable extends TransferTable  {
	
	protected function create() {
		$this->buildColumnText('nif', 'CPF', null, 100);
		$this->buildColumnText('name', 'Nome', null, null, ColumnText::Left, null);
		$this->buildColumnText('class', null, null, 30, null, function($value) {
			return '<small>' . substr(constant($value. '::USER_TYPE'), 0, 3) . '.</small>';
		});
		$this->buildColumnText('lotation', 'Lotação', null, 70, null, function (AdministrativeUnit $value) {
			return $value->getAcronym();
		});
		$this->buildColumnText('active', 'Status', null, 50, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
	}
	
}
?>