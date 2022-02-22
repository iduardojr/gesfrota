<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Owner;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;

class OwnerTable extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('agency-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('nif', 'CPF/CNPJ', null, 130, ColumnText::Left);
		$this->buildColumnText('name', 'Proprietário', null, null, ColumnText::Left);
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, Owner $obj ) {
		    $data['owner-id'] = $obj->getCode();
			$data['owner-name'] = $obj->getName();
			$data['owner-nif'] = $obj->getNif();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>