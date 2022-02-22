<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Gesfrota\Model\NestedSet\Node;
use Gesfrota\Model\Activable;

/**
 * Unidade Administrativa
 * @Entity
 * @Table(name="administrative_units")
 */
class AdministrativeUnit extends Node implements Activable {
	
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
	protected $active = true;
	
	/**
	 * @OneToOne(targetEntity="Gesfrota\Model\Domain\Agency")
	 * @JoinColumn(name="agency_id", referencedColumnName="id")
	 * @var Agency
	 */
	protected $agency;

	/**
	 * @OneToMany(targetEntity="Gesfrota\Model\Domain\AdministrativeUnit", mappedBy="parent", indexBy="id")
	 * @OrderBy({"name" = "ASC"})
	 * @var ArrayCollection
	 */
	protected $children;
	
	/**
	 * @ManyToOne(targetEntity="Gesfrota\Model\Domain\AdministrativeUnit", inversedBy="children", fetch="EAGER")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 * @var AdministrativeUnit
	 */
	protected $parent;
	
	/**
	 * Construtor
	 * 
	 * @param Agency $agency
	 * @param AdministrativeUnit $parent
	 */
	public function __construct( Agency $agency, AdministrativeUnit $parent = null ) {
		parent::__construct();
		$this->children = new ArrayCollection();
		$this->setAgency($agency);
		$this->setParent($parent);
	}
	
	/**
	 * Obtem a descrição completa da unidade administrativa
	 * 
	 * @param string $separator
	 * @return string
	 */
	public function getFullDescription( $separator = ' / ' ) {
		$nodes = $this->getAncestors();
		$nodes[] = $this;
		return implode(' / ', $nodes);
	}
	
	/**
	 * Obtem a descrição parcial da unidade administrativa
	 *
	 * @param string $separator
	 * @return string
	 */
	public function getPartialDescription( $separator = ' / ' ) {
		$ancestors = $this->getAncestors();
		$nodes = [];
		foreach ($ancestors as $ancestor) {
			$nodes[] = $ancestor->getAcronym();
		}
		$nodes[] = $this->getAcronym() .' - '. $this->getName();
		return implode(' / ', $nodes);
	}
	
	/**
	 * Obtem os antecessores
	 * 
	 * @return array
	 */
	public function getAncestors() {
		if ( ! $this->parent ) {
			return array();
		}
		$ancestors = $this->parent->getAncestors();
		$ancestors[] = $this->parent;
		return $ancestors;
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
	 * Obtem $agency
	 *
	 * @return Agency
	 */
	public function getAgency() {
		return $this->agency;
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
	 * @param boolean $actived
	 * @return array
	 */
	public function getChildren( $actived = null ) {
		if ( $actived !== null ) {
			$criteria = Criteria::create();
			$criteria->andWhere(Criteria::expr()->eq('active', ( bool ) $actived));
			return $this->children->matching($criteria);
		}
		return $this->children->getValues();
	}
	
	/**
	 * Obtem $active
	 *
	 * @return boolean
	 */
	public function getActive() {
		if ( $this->parent ) {
			return $this->active && $this->parent->getActive();
		}
		return $this->active;
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
	 * Atribui $active
	 *
	 * @param boolean $active
	 */
	public function setActive( $active ) {
		$this->active = ( bool ) $active;
	}
	
	/**
	 * Atribui $agency
	 *
	 * @param Agency $agency
	 */
	public function setAgency( Agency $agency ) {
		$this->agency = $agency;
	}

	/**
	 * Atribui $parent
	 *
	 * @param AdministrativeUnit $parent
	 * @throws \DomainException
	 */
	public function setParent( AdministrativeUnit $parent = null ) {
		if ( $parent && $this->assertReferenceCircular($parent) ) {
			throw new \DomainException('parent in reference circular');
		}
		$this->parent = $parent;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}
	
	/**
	 * Verifica se há referencia circular
	 * 
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
	
}
?>