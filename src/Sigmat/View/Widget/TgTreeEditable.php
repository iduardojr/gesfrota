<?php
namespace Sigmat\View\Widget;

use PHPBootstrap\Widget\Tree\TreeElement;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Toggle\Pluggable;
use PHPBootstrap\Widget\AbstractRender;

/**
 * Componente alternador de edição de arvore
 */
class TgTreeEditable extends AbstractRender implements Pluggable {
	
	// ID Renderizador
	const RendererType = 'sigmat.view.widget.tree.toggle.editable';
	
	// Operação
	const Add = 'add';
	const Edit = 'edit';
	const Remove = 'remove';
	
	/**
	 * @var TreeElement
	 */
	protected $target;
	
	/**
	 * @var Action
	 */
	protected $action;
	
	/**
	 * @var string
	 */
	protected $operation;
	
	/**
	 * Construtor
	 * 
	 * @param string $operation
	 * @param Action $action
	 * @param TreeElement $target
	 */
	public function __construct( $operation, Action $action, TreeElement $target ) {
		$this->setOperation($operation);
		$this->setAction($action);
		$this->setTarget($target);
	}
	
	/**
	 * Obtem $target
	 *
	 * @return TreeElement
	 */
	public function getTarget() {
		return $this->target;
	}

	/**
	 * Atribui $target
	 *
	 * @param TreeElement $target
	 */
	public function setTarget( TreeElement $target ) {
		$this->target = $target;
	}

	/**
	 * Obtem $action
	 *
	 * @return Action
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * Atribui $action
	 *
	 * @param Action $action
	 */
	public function setAction( Action $action ) {
		$this->action = $action;
	}

	/**
	 * Obtem $operation
	 *
	 * @return string
	 */
	public function getOperation() {
		return $this->operation;
	}

	/**
	 * Atribui $operation
	 *
	 * @param string $operation
	 */
	public function setOperation( $operation ) {
		$this->operation = $operation;
	}
	
}
?>