<?php
namespace Sigmat\View\Product;

use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Paragraph;
use Sigmat\View\AbstractList;
use Sigmat\Model\Product\Category;

class ProductList extends AbstractList {
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $remove
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $remove ) {
		$panel = $this->buildPanel('Administração', 'Gerenciar Produtos');
		$modalConfirm = $this->buildConfirm('confirm-remove', new Paragraph('Deseja realmente excluir esse produto?'));
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary));
		
		$table = $this->buildTable('product-table');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter, null, function( $value ) {
			return str_repeat('0', 5 - strlen($value)) . $value; 
		});
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('category', 'Categoria', null, 300, null, function( $value ) {
			return $value instanceof Category ? $value->getDescription() : '<em>empty</em>';
		});
		$table->buildColumnText('productClass', 'Classe', null, 120, null, function ( $value ) {
			return $value->getDescription();
		}); 
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('remove', new Icon('icon-remove'), $remove, $modalConfirm);
		
	}
	
}
?>