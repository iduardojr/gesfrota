<?php
namespace Sigmat\View\Product;

use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Button\ButtonGroup;
use PHPBootstrap\Widget\Tree\Tree;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Tree\TgTree;
use PHPBootstrap\Widget\Tree\TreeNode;
use PHPBootstrap\Widget\Misc\Anchor;
use PHPBootstrap\Widget\Action\TgStorage;
use Sigmat\Model\Product\Category;

/**
 * Arvore de Categorias
 */
class CategoryTree extends Box {
	
	/**
	 * Construtor
	 * 
	 * @param array $categories
	 */
	public function __construct( array $categories ) {
		$tree = new Tree('category-tree');
		foreach( $categories as $category ) {
			$tree->addNode($this->buildNode($category));
		}
		$btn = new ButtonGroup(new Button(array(new Icon('icon-eye-open'), 'Expandir todos'), new TgTree($tree, TgTree::Expand), array(Button::Link, Button::Mini)),
							   new Button(array(new Icon('icon-eye-close'), 'Recolher todos'), new TgTree($tree, TgTree::Collapse), array(Button::Link, Button::Mini)));
		parent::__construct(0, array($btn, $tree));
	}
	
	/**
	 * Construi um nó filho
	 *
	 * @param Category $category
	 * @return TreeNode
	 */
	private function buildNode( Category $category ) {
		$node = new TreeNode($category->getId(), new Anchor('#' . $category->getId() . ' ' . $category->getName(), new TgStorage(array('category-id' => $category->getId(), 'category-description' => $category->getDescription()))));
		foreach ( $category->getChildren() as $child ) {
			$node->addNode($this->buildNode($child));
		}
		return $node;
	}
}
?>