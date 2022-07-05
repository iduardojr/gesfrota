<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\AbstractActivable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Orgão
 * @Entity
 * @Table(name="agencies")
 */
class Agency extends AbstractActivable {

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
	protected $nif;
	
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
	 * @OneToOne(targetEntity="OwnerCompany", mappedBy="agency", cascade={"all"})
	 * @var OwnerCompany
	 */
	protected $owner;
	
	/**
	 * @OneToMany(targetEntity="ResultCenter", mappedBy="agency")
	 * @var ArrayCollection
	 */
	protected $resultCenters;
	
	/**
	 * Construtor
	 */
	public function __construct() {
		$this->owner = new OwnerCompany($this);
		$this->resultCenters = new ArrayCollection();
		parent::__construct();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Gesfrota\Model\Entity::getCode()
	 */
	public function getCode() {
		if ($this->isGovernment()) {
			return $this->getAcronym();
		}
		return parent::getCode();
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
		return str_replace('#', '', $this->acronym);
	}
	
	/**
	 * Obtem $nif
	 *
	 * @return string
	 */
	public function getNif() {
		return $this->nif;
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
	 * @return OwnerCompany
	 */
	public function getOwner() {
		return $this->owner;
	}
	
	/**
	 * Atribui $name
	 *
	 * @param string $name
	 */
	public function setName( $name ) {
		$this->name = $name;
		$this->owner->accept($this);
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
	 * Atribui $nif
	 *
	 * @param string $nif
	 */
	public function setNif( $nif ) {
		$this->nif = $nif;
		$this->owner->accept($this);
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
	
	public function setActive($active) {
		parent::setActive($active);
		$this->owner->accept($this);
	}
	
	/**
	 * @return boolean
	 */
	public function isGovernment() {
		return $this->id === 0;
	}
	
	/**
	 * @return boolean
	 */
	public function isResultCenterRequired() {
		return count($this->getResultCentersActived()) ? true : false;
	}
	
	/**
	 * @return array
	 */
	public function getResultCentersActived() {
		$options = [];
		$criteria = new Criteria();
		$criteria->where(Criteria::expr()->eq('active', true));
		$result = $this->resultCenters->matching($criteria);
		foreach($result as $item) {
			$options[$item->id] = $item->description;
		}
		return $options;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getAcronym();
	}
}
?>