<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\AbstractActivable;

/**
 * Centro de Resultado
 * @Entity
 * @Table(name="center_results")
 */
class ResultCenter extends AbstractActivable {

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;
	
	/**
	 * @ManyToOne(targetEntity="Agency", inversedBy="resultCenters")
	 * @JoinColumn(name="agency_id", referencedColumnName="id")
	 * @var Agency
	 */
	protected $agency;
	
	/**
	 * @param Agency $agency
	 */
	public function __construct(Agency $agency = null) {
		parent::__construct();
		if ($agency) {
			$this->setAgency($agency);
		}
	}
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return Agency
	 */
	public function getAgency() {
		return $this->agency;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @param Agency $agency
	 */
	public function setAgency(Agency $agency) {
		$this->agency = $agency;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getDescription();
	}
}
?>