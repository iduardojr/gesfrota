<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use Sigmat\View\GUI\BuilderTable;
use Sigmat\Model\Domain\ProductUnit;

class ProductUnitTable extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('product-unit-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('description', 'Descrição', null, null, ColumnText::Left);
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, ProductUnit $unit ) {
			$data['product-unit-id'] = $unit->getCode();
			$data['product-unit-description'] = $unit->getDescription();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>