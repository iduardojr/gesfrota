<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Agency;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;

class AgencyTable extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('agency-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('acronym', 'Sigla', null, 70);
		$this->buildColumnText('name', 'Descrição', null, null, ColumnText::Left);
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, Agency $agency ) {
			$data['agency-id'] = $agency->getCode();
			$data['agency-name'] = $agency->getName();
			$data['administrative-unit-id'] = null;
			$data['administrative-unit-name'] = null;
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>