<?php
namespace Gesfrota\Model\NestedSet;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Ouvinte de Nós
 */
class NodeListener {
	
	/**
	 * @var EntityManager
	 */
	protected $em;
	
	/**
	 * @var array
	 */
	protected $updater;
	
	/**
	 * Construtor
	 */
	public function __construct() {
		$this->updater = array();
	}
	
	/**
	 * Ação após inserir o nó
	 * 
	 * @param Node $node
	 * @param LifecycleEventArgs $event
	 */
	public function postPersist( Node $node, LifecycleEventArgs $event ) {
		$this->setEntityManager($event->getEntityManager());
		$this->insertNode($node, $node->getParent());
	}
	
	/**
	 * Ação antes de uma atualização do nó
	 * 
	 * @param Node $node
	 * @param PreUpdateEventArgs $event
	 */
	public function preUpdate( Node $node, PreUpdateEventArgs $event ) {
		if ( $event->hasChangedField('parent') ) {
			$this->updater[spl_object_hash($node)] = $event;
		}
	}
	
	/**
	 * Ação depois de uma atualização do nó
	 * 
	 * @param Node $node
	 * @param LifecycleEventArgs $event
	 */
	public function postUpdate( Node $node, LifecycleEventArgs $event ) {
		$hash = spl_object_hash($node);
		if ( isset($this->updater[$hash]) ) {
			$this->setEntityManager($event->getEntityManager());
			$this->appendNode($node, $node->getParent());
			unset($this->updater[$hash]);
		}
	}
	
	/**
	 * Ação depois de remover um nó
	 * 
	 * @param Node $node
	 * @param LifecycleEventArgs $event
	 */
	public function postRemove( Node $node, LifecycleEventArgs $event ) {
		$this->setEntityManager($event->getEntityManager());
		$this->removeNode($node);
	}
	
	/**
	 * Atribui o gerenciador de entidades
	 * 
	 * @param EntityManager $em
	 */
	protected function setEntityManager( EntityManager $em ) {
		$this->em = $em;
	}
	
	/**
	 * Insere um nó 
	 * 
	 * @param Node $node
	 * @param Node $parent
	 */
	protected function insertNode( Node $node, Node $parent = null ) {
		$className = $this->getClassName($node);
		$position = $this->getPosition($className, $parent);
		$this->updateRange($className, $position, 0, 2);
		$this->em->createQuery('UPDATE ' . $className . ' u SET u.lft = :lft, u.rgt = :rgt WHERE u.id = :node')->execute(array('lft'=> $position, 'rgt' => $position + 1, 'node' => $node->getId()));
	}
	
	/**
	 * Obtem o nome da classe
	 * 
	 * @param Node $node
	 * @return string
	 */
	protected function getClassName( Node $node ) {
		return $this->em->getClassMetadata(get_class($node))->rootEntityName;
	}
	
	/**
	 * Remove um nó
	 * 
	 * @param Node $node
	 */
	protected function removeNode( Node $node ) {
		$this->updateRange($this->getClassName($node), $node->getRgt() + 1, 0, ( $node->getLft() - $node->getRgt() - 1 ));
	}
	
	/**
	 * Move um nó pro fim dos filhos do pai
	 * 
	 * @param Node $node
	 * @param Node $parent
	 */
	protected function appendNode( Node $node, Node $parent = null ) {
		$className = $this->getClassName($node);
		$this->em->refresh($node);
		if ($parent) {
		    $this->em->refresh($parent);
		}
		$left = $node->getLft();
        $right = $node->getRgt();
        $size = $right - $left + 1;
        $position = $this->getPosition($className, $parent);
        
        if ( $left >= $position ) {
        	$left += $size;
        	$right += $size;
        }
        
        $this->updateRange($className, $position, 0, $size);
        $this->updateRange($className, $left, $right, $position - $left);
        $this->updateRange($className, $right + 1, 0, -$size);
	}
	
	/**
	 * Obtem a posição do nó pai
	 * 
	 * @param string $className
	 * @param Node $parent
	 * @return integer
	 */
	protected function getPosition( $className, Node $parent = null ) {
		if ( $parent) {
			return $parent->getRgt();
		}
		return (int) $this->em->createQuery('SELECT MAX(u.rgt+1) FROM ' . $className . ' u')->getSingleScalarResult();
	}
	
	/**
	 * Verifica se é um nó folha
	 * 
	 * @param Node $node
	 * @return boolean
	 */
	protected function isLeaf( Node $node ) {
		return $node->getRgt() - $node->getLft() === 1;
	}
	
	/**
	 * Atualiza o intervalo entre um conjunto de nós
	 *
	 * @param string $className
	 * @param integer $first
	 * @param integer $last
	 * @param integer $delta
	 */
	protected function updateRange( $className, $first, $last, $delta )	{
		$data['delta'] = $delta;
		$data['lowerbound'] = $first;
		if ( $last > 0 ) {
			$data['upperbound'] = $last;
		}
		
		$this->em->createQuery('UPDATE ' . $className . ' u SET u.rgt = u.rgt + :delta WHERE u.rgt >= :lowerbound' . ( $last > 0 ? ' AND u.rgt <= :upperbound' : '' ))->execute($data);
		$this->em->createQuery('UPDATE ' . $className . ' u SET u.lft = u.lft + :delta WHERE u.lft >= :lowerbound' . ( $last > 0 ? ' AND u.lft <= :upperbound' : '' ))->execute($data);
	}
	
}
?>