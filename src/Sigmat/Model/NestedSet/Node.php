<?php
namespace Sigmat\Model\NestedSet;

use Sigmat\Model\Entity;

/**
 * No
 * @MappedSuperclass
 * @EntityListeners({"Sigmat\Model\NestedSet\NodeListener"})
 */
abstract class Node extends Entity {
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $lft;
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $rgt;
	
	/**
	 * @OneToOne(targetEntity="Sigmat\NestedSet\Node", fetch="EAGER")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 * @var Node
	 */
	protected $parent;
	
	/**
	 * Obtem $lft
	 *
	 * @return integer
	 */
	public function getLft() {
		return $this->lft;
	}
	
	/**
	 * Obtem $rgt
	 *
	 * @return integer
	 */
	public function getRgt() {
		return $this->rgt;
	}
	
	/**
	 * Obtem $parent
	 * 
	 * @return Node
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * Verifica a quantidade de decendentes
	 *
	 * @return integer
	 */
	public function getNumberDecendents() {
		return ( $this->rgt - $this->lft - 1 ) / 2;
	}
	
}
?>