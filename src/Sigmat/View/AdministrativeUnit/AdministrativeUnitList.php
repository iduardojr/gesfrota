<?php
namespace Sigmat\View\AdministrativeUnit;

use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Button\Button;
use Sigmat\View\AbstractList;
use Sigmat\View\EntityDatasource;
use PHPBootstrap\Widget\Tree\Tree;
use PHPBootstrap\Widget\Tree\TgTree;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;
use PHPBootstrap\Widget\Tree\TreeNode;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Anchor;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Form\Controls\Help;

class AdministrativeUnitList extends AbstractList {
	
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
		$panel = $this->buildPanel('Administração', 'Gerenciar Unidades Administrativas');
		$this->component = new Tree('administrative-unit-list');
		$this->buildToolbar(new Button(array(new Icon('icon-eye-open'), 'Expandir todos'), new TgTree($this->component, TgTree::Expand), array(Button::Link, Button::Mini)),
							new Button(array(new Icon('icon-eye-close'), 'Recolher todos'), new TgTree($this->component, TgTree::Collapse), array(Button::Link, Button::Mini)));
		
		$panel->append(new Help('Você pode selecionar e arrastar as unidades administrativas para criar a estrutura que desejar.', false));
		$panel->append($this->component);
		$this->new = $new;
		$this->edit = $edit;
		$this->remove = $remove;
		$this->confirm = $this->buildConfirm('confirm-remove', new Paragraph('Deseja realmente excluir essa unidade administrativa?'));
		
	}
	
	/**
	 * @see AbstractList::setDatasource()
	 */
	public function setDatasource( EntityDatasource $datasource ) {
		$this->update($datasource);
	}
	
	/**
	 * @see AbstractList::update()
	 */
	protected function update( EntityDatasource $datasource ) {
		$datasource->reset();
		$datasource->next();
		$unit = $datasource->fetch();
		$new = clone $this->new;
		$new->setParameter('key', $unit->getId());
		$node = new TreeNode($unit->getId(), '<strong>' . $unit->getAcronym() . '</strong>', null, new Button(new Icon('icon-plus'), new TgLink($new), array(Button::Link, Button::Mini)));
		$this->component->addNode($node);
		foreach ( $unit->getChildren() as $child ) {
			$node->addNode($this->buildNode($child));
		}
	}
	 
	/**
	 * Construi um nó filho
	 * 
	 * @param AdministrativeUnit $unit
	 * @return TreeNode
	 */
	private function buildNode( AdministrativeUnit $unit ) {
		$new = clone $this->new;
		$edit = clone $this->edit;
		$remove = clone $this->remove;
		
		$new->setParameter('key', $unit->getId());
		$edit->setParameter('key', $unit->getId());
		$remove->setParameter('key', $unit->getId());
		
		$node = new TreeNode($unit->getId(), new Anchor($unit->getName(), new TgLink($edit)));
		$node->addButton(new Button(new Icon('icon-plus'), new TgLink($new), array(Button::Link, Button::Mini)));
		$node->addButton(new Button(new Icon('icon-remove'), new TgModalOpen($this->confirm, new TgLink($remove)), array(Button::Link, Button::Mini)));
		foreach ( $unit->getChildren() as $child ) {
			$node->addNode($this->buildNode($child));
		}
		$node->setOpened(true);
		return $node;
	}
}
?>