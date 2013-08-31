<?php
namespace Sigmat\View\Product;

use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Tree\Tree;
use PHPBootstrap\Widget\Tree\TgTree;
use PHPBootstrap\Widget\Tree\TreeNode;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Anchor;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Form\Controls\Help;
use Sigmat\View\AbstractList;
use Sigmat\View\EntityDatasource;
use Sigmat\Model\Product\Category;

class CategoryList extends AbstractList {
	
	/**
	 * @var Action
	 */
	protected $new;
	
	/**
	 * @var Action
	 */
	protected $edit;
	
	/**
	 * @var Action
	 */
	protected $remove;
	
	/**
	 * @var Modal
	 */
	protected $confirm;
	
	/**
	 * @var Tree
	 */
	protected $component;
	
	/**
	 * Construtor
	 * 
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $remove
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $remove ) {
		$panel = $this->buildPanel('Administração', 'Gerenciar Categorias');
		$this->component = new Tree('product-category-list');
		$this->buildToolbar(new Button(array(new Icon('icon-eye-open'), 'Expandir todos'), new TgTree($this->component, TgTree::Expand), array(Button::Link, Button::Mini)),
							new Button(array(new Icon('icon-eye-close'), 'Recolher todos'), new TgTree($this->component, TgTree::Collapse), array(Button::Link, Button::Mini)));
		
		$panel->append(new Help('Você pode selecionar e arrastar as categorias para criar a estrutura que desejar.', false));
		$panel->append($this->component);
		$this->new = $new;
		$this->edit = $edit;
		$this->remove = $remove;
		$this->confirm = $this->buildConfirm('confirm-remove', new Paragraph('Deseja realmente excluir essa categoria e todas suas subcategorias?'));
	}
	
	/**
	 * @see AbstractList::setDatasource()
	 */
	public function setDatasource( EntityDatasource $datasource ) {
		$node = new TreeNode(0, '<em>root</em>', new Button(new Icon('icon-plus'), new TgLink(clone $this->new), array(Button::Link, Button::Mini)));
		$this->component->addNode($node);
		$datasource->reset();
		while( $datasource->next() ) {
			$category = $datasource->fetch();
			$node->addNode($this->buildNode($category));
		}
	}
	
	/**
	 * Construi um nó filho
	 * 
	 * @param Category $category
	 * @return TreeNode
	 */
	private function buildNode( Category $category ) {
		$new = clone $this->new;
		$edit = clone $this->edit;
		$remove = clone $this->remove;
		
		$new->setParameter('key', $category->getId());
		$edit->setParameter('key', $category->getId());
		$remove->setParameter('key', $category->getId());
		
		$node = new TreeNode($category->getId(), new Anchor($category->getName(), new TgLink($edit)));
		$node->addButton(new Button(new Icon('icon-plus'), new TgLink($new), array(Button::Link, Button::Mini)));
		$node->addButton(new Button(new Icon('icon-remove'), new TgModalOpen($this->confirm, new TgLink($remove)), array(Button::Link, Button::Mini)));
		foreach ( $category->getChildren() as $child ) {
			$node->addNode($this->buildNode($child));
		}
		$node->setOpened(true);
		return $node;
	}
}
?>