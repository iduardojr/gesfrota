<?php
namespace Gesfrota\Model\Domain;

/**
 * Empresa Proprietária
 * @Entity
 */
class OwnerCompany extends Owner {

	/**
	 * @OneToOne(targetEntity="Agency", inversedBy="owner")
     * @JoinColumn(name="agency_id", referencedColumnName="id")
	 * @var Agency
	 */
	protected $agency;
	
	/**
	 * @param Agency $agency
	 */
	public function __construct(Agency $agency = null) {
		parent::__construct();
		$this->agency = $agency;
	}
	
	/**
	 * @return Agency
	 */
	public function getAgency() {
		return $this->agency;
	}
	
	/**
	 * @return boolean
	 */
	public function isReadOnly() {
		return (bool) $this->agency;
	}
	
	public function setActive($active) {
		if ($this->isReadOnly()) {
			throw new \DomainException('Owner is read-only');
		}
		parent::setActive($active);
	}
	
	public function setName($name) {
		if ($this->isReadOnly()) {
			throw new \DomainException('Owner is read-only');
		}
		parent::setName($name);
	}
	
	public function setNif($nif) {
		if ($this->isReadOnly()) {
			throw new \DomainException('Owner is read-only');
		}
		parent::setNif($nif);
	}
	
	public function accept(Agency $visitor) {
		$this->name = $visitor->getName();
		$this->nif = $visitor->getNif();
		$this->active = $visitor->getActive();
	}
}
?>