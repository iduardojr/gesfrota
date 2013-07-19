<?php
namespace Sigmat\Model\AdministrativeUnit;

use Sigmat\Model\Entity;
use Sigmat\Model\Deleting;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Unidade Administrativa
 * @Entity
 * @Table(name="administrative_units")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"U" = "AdministrativeUnit", "A" = "Agency"})
 */
class AdministrativeUnit extends Entity implements Deleting {
	
	/**
     * @Column(type="string")
     * @var string
     */
	protected $name;

	/**
     * @Column(type="string")
     * @var string
     */
	protected $acronym;

	/**
     * @Column(type="string")
     * @var string
     */
	protected $contact;

	/**
     * @Column(type="string")
     * @var string
     */
	protected $phone;

	/**
     * @Column(type="string")
     * @var string
     */
	protected $email;

	/**
     * @Column(type="boolean")
     * @var boolean
     */
	protected $status;
	
	/**
	 * @OneToMany(targetEntity="Sigmat\Model\AdministrativeUnit\AdministrativeUnit", mappedBy="parent")
	 * @OrderBy({"name" = "ASC"})
	 */
	protected $children;
	
	/**
	 * @ManyToOne(targetEntity="Sigmat\Model\AdministrativeUnit\AdministrativeUnit", inversedBy="children")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 * @var AdministrativeUnit
	 */
	protected $parent;
	
	/**
	 * Construtor
	 */
	public function __construct( AdministrativeUnit $parent ) {
		$this->children = new ArrayCollection();
		$this->setStatus(true);
		$this->setParent($parent);
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
	 * Obtem $acronym
	 *
	 * @return string
	 */
	public function getAcronym() {
		return $this->acronym;
	}
	
	/**
	 * Obtem $contact
	 *
	 * @return string
	 */
	public function getContact() {
		return $this->contact;
	}

	/**
	 * Obtem $phone
	 *
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * Obtem $email
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
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
	 * Obtem $parent
	 *
	 * @return AdministrativeUnit
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * Obtem as unidades filhas
	 *
	 * @return array
	 */
	public function getChildren() {
		$active = array();
		foreach ( $this->children as $child ) {
			if ( $child->getStatus() ) {
				$active[] = $child;
			}
		}
		return $active;
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
	 * Atribui $acronym
	 *
	 * @param string $acronym
	 */
	public function setAcronym( $acronym ) {
		$this->acronym = $acronym;
	}

	/**
	 * Atribui $contact
	 *
	 * @param string $contact
	 */
	public function setContact( $contact ) {
		$this->contact = $contact;
	}

	/**
	 * Atribui $phone
	 *
	 * @param string $phone
	 */
	public function setPhone( $phone ) {
		$this->phone = $phone;
	}

	/**
	 * Atribui $email
	 *
	 * @param string $email
	 */
	public function setEmail( $email ) {
		$this->email = $email;
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
	 * Atribui $parent
	 *
	 * @param AdministrativeUnit $parent
	 * @throws \DomainException
	 */
	public function setParent( AdministrativeUnit $parent ) {
		if ( $parent !== null ) {
			if ( $this->assertReferenceCircular($parent) ) {
				throw new \DomainException('parent in reference circular');
			}
		}
		$this->parent = $parent;
	}
	
	/**
	 * Verifica se há referencia circular
	 * @param AdministrativeUnit $parent
	 * @return boolean
	 */
	private function assertReferenceCircular( AdministrativeUnit $parent ) {
		if ( $parent === $this ) {
			return true;
		}
		foreach ( $this->children as $child ) {
			if ( $child->assertReferenceCircular($parent) ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @see \Sigmat\Model\Deleting::delete()
	 */
	public function delete() {
		$this->setStatus(false);
	}

}
?>