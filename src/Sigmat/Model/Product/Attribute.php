<?php
namespace Sigmat\Model\Product;

use Sigmat\Model\Entity;
use Sigmat\Model\Deleting;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Atributos de um produto
 * @Entity
 * @Table(name="product_attributes")
 */
class Attribute extends Entity implements Deleting {
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;
	
	/**
	 * @Column(type="boolean")
	 * @var boolean
	 */
	protected $status;
	
	/**
	 * @OneToMany(targetEntity="Sigmat\Model\Product\AttributeOption", mappedBy="attribute", cascade="all", indexBy="id")
	 * @var ArrayCollection
	 **/
	protected $options;
	
	/**
	 * Construtor
	 */
	public function __construct() {
		parent::__construct();
		$this->options = new ArrayCollection();
		$this->setStatus(true);
	}
	
	/**
	 * Obtem $name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Obtem $description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Obtem $status
	 *
	 * @return boolean
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * Atribui $name
	 *
	 * @param string $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}
	
	/**
	 * Atribui $description
	 *
	 * @param string $description
	 */
	public function setDescription( $description ) {
		$this->description = $description;
	}
	
	/**
	 * Atribui $status
	 *
	 * @param boolean $status
	 */
	public function setStatus( $status ) {
		$this->status = ( bool ) $status;
	}
	
	/**
	 * @see Deleting::delete()
	 */
	public function delete() {
		$this->setStatus(false);
	}
	
	/**
	 * Adiciona uma opção
	 * 
	 * @param AttributeOption $option
	 */
	public function addOption( AttributeOption $option ) {
		if ( ! $this->options->contains($option) ) {
			$option->setAttribute($this);
			$option->setStatus(true);
			$this->options[] = $option;
		}
	}
	
	/**
	 * Remove uma opção
	 * 
	 * @param AttributeOption $option
	 */
	public function removeOption( AttributeOption $option ) {
		if ( $this->options->contains($option) ) {
			if ( $option->getId() > 0 ) {
				$option->setStatus(false);
			} else {
				$this->options->removeElement($option);
			}
		}
	}
	
	/**
	 * Remove todas as opcoes
	 * 
	 * @return boolean
	 */
	public function removeAllOptions() {
		foreach( $this->getOptions() as $option ) {
			$this->removeOption($option);
		}
	}
	
	/**
	 * Obtem todas as opções
	 *  
	 * @return array
	 */
	public function getOptions() {
		return $this->options->matching(new Criteria(Criteria::expr()->eq('status', true)))
							 ->toArray();
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}
}
?>