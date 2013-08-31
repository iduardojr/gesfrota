<?php
namespace Sigmat\View\Product;

use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use Sigmat\View\BuilderTable;
use PHPBootstrap\Widget\Action\TgStorage;
use Sigmat\Model\Product\Attribute;
use PHPBootstrap\Widget\Button\Button;

class AttributesList extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('attributes-list');
		
		$this->buildPagination(clone $filter);
		
		$this->buildColumnTextId();
		$this->buildColumnText('description', 'Descrição', null, null, ColumnText::Left);
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, Attribute $attr ) {
			$data['attribute-id'] = $attr->getId();
			$data['attribute-description'] = $attr->getDescription();
			$button->setToggle(new TgStorage($data));
		});
		
	}
	
}
?>