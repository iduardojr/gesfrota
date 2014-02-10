<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Action\TgStorage;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use Sigmat\View\GUI\BuilderTable;
use Sigmat\Model\Domain\ProductCategory;

class ProductCategoryTable extends BuilderTable {
	
	/**
	 * Construtor
	 * 
	 */
	public function __construct( Action $filter ) {
		parent::__construct('product-category-table');
		
		$this->buildPagination(new TgAjax(clone $filter, $this));
				
		$this->buildColumnTextId();
		$this->buildColumnText('fullDescription', 'Descrição', null, null, ColumnText::Left);
		$this->buildColumnAction('select', new Icon('icon-ok'), null, null, function( Button $button, ProductCategory $category ) {
			$data['product-category-id'] = $category->getCode();
			$data['product-category-description'] = $category->getFullDescription();
			$button->setToggle(new TgStorage($data));
		});
		
	}
}
?>