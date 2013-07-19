<?php
namespace Sigmat\View\AdministrativeUnit;

use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Button\ButtonGroup;
use PHPBootstrap\Widget\Tree\Tree;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Tree\TgTree;
use PHPBootstrap\Widget\Tree\TreeNode;
use PHPBootstrap\Widget\Misc\Anchor;
use PHPBootstrap\Widget\Action\TgStorage;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;
use Sigmat\Model\AdministrativeUnit\Agency;

/**
 * Arvore de Unidades Administrativas de um orgão
 */
class AdministrativeUnitTree extends Box {
	
	/**
	 * Construtor
	 * 
	 * @param Agency $agency
	 */
	public function __construct( Agency $agency ) {
		$tree = new Tree('administrative-unit-tree');
		foreach( $agency->getChildren() as $unit ) {
			$tree->addNode($this->buildNode($unit));
		}
		$btn = new ButtonGroup(new Button(array(new Icon('icon-eye-open'), 'Expandir todos'), new TgTree($tree, TgTree::Expand), array(Button::Link, Button::Mini)),
							   new Button(array(new Icon('icon-eye-close'), 'Recolher todos'), new TgTree($tree, TgTree::Collapse), array(Button::Link, Button::Mini)));
		parent::__construct(0, array($btn, $tree));
	}
	
	/**
	 * Construi um nó filho
	 *
	 * @param AdministrativeUnit $unit
	 * @return TreeNode
	 */
	private function buildNode( AdministrativeUnit $unit ) {
		$node = new TreeNode($unit->getId(), new Anchor('#' . $unit->getId() . ' ' . $unit->getName(), new TgStorage(array('unit-id' => $unit->getId(), 'unit-name' => $unit->getName()))));
		foreach ( $unit->getChildren() as $child ) {
			$node->addNode($this->buildNode($child));
		}
		return $node;
	}
}
?>