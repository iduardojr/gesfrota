<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\Entity;

/**
 * Item em Alienação
 * @Entity
 * @Table(name="disposal_items")
 */
class DisposalItem extends Entity {
	
	/**
	 * Ocioso
	 * @var integer
	 */
	const UNNECESSARY = 1;
	
	/**
	 * Recuperável
	 * @var integer
	 */
	const RECOVERABLE = 2;
	
	/**
	 * Antieconômico
	 * @var integer
	 */
	const UNECONOMICAL = 4;
	
	/**
	 * Irrecuperável
	 * @var integer
	 */
	const IRRECOVERABLE = 8;
	
	/**
	 * @ManyToOne(targetEntity="Disposal", inversedBy="assets")
	 * @JoinColumn(name="disposal_id", referencedColumnName="id")
	 * @var Disposal
	 */
	protected $disposal;
	
	/**
	 * @ManyToOne(targetEntity="FleetItem")
	 * @JoinColumn(name="asset_id", referencedColumnName="id")
	 * @var FleetItem
	 */
	protected $asset;
	
	/**
	 * @Embedded(class="Place")
	 * @var Place
	 */
	protected $courtyard;
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $classification;
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $conservation;
	
	/**
	 * @Column(type="decimal")
	 * @var number
	 */
	protected $value;
	
	/**
	 * @OneToOne(targetEntity="Survey", cascade={"all"})
     * @JoinColumn(name="survey_id", referencedColumnName="id")
     * @var Survey
	 */
	protected $survey;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $report;
	
	/**
	 * @Column(name="debit_license", type="decimal")
	 * @var number
	 */
	protected $debitLicense;
	
	/**
	 * @Column(name="debit_penalty", type="decimal")
	 * @var number
	 */
	protected $debitPenalty;
	
	/**
	 * @Column(name="debit_safe", type="decimal")
	 * @var number
	 */
	protected $debitSafe;
	
	/**
	 * @Column(name="debit_tax", type="decimal")
	 * @var number
	 */
	protected $debitTax;
	
	/**
	 * @param FleetItem $asset
	 */
	public function __construct(FleetItem $asset) {
		$this->asset = $asset;
		if ($asset instanceof Vehicle) {
			$this->survey = new Survey();
		}
	}
	
	
	/**
	 * Obtem o código do ativo
	 * 
	 * @return string
	 */
	public function getCode() {
		return $this->asset->getCode();
	}
	
	/**
	 * Obtem a descrição do ativo
	 * 
	 * @return string
	 */
	public function getDescription() {
		return $this->asset->getDescription();
	}
	
	/**
	 * @return number
	 */
	public function getDebit() {
		return $this->getDebitLicense() + $this->getDebitPenalty() + $this->getDebitSafe() + $this->getDebitTax();
	}
	
	/**
	 * @param number $license
	 * @param number $penalty
	 * @param number $safe
	 * @param number $tax
	 */
	public function setDebit($license, $penalty, $safe, $tax) {
		$this->setDebitLicense($license);
		$this->setDebitPenalty($penalty);
		$this->setDebitSafe($safe);
		$this->setDebitTax($tax);
	}
	

	/**
	 * @return number
	 */
	public function getDebitLicense() {
		return $this->debitLicense;
	}

	/**
	 * @param number $license
	 */
	public function setDebitLicense($license) {
		$this->debitLicense = $license;
	}

	/**
	 * @return number
	 */
	public function getDebitPenalty() {
		return $this->debitPenalty;
	}

	/**
	 * @param number $penalty
	 */
	public function setDebitPenalty($penalty) {
		$this->debitPenalty = $penalty;
	}

	/**
	 * @return number
	 */
	public function getDebitSafe() {
		return $this->debitSafe;
	}

	/**
	 * @param number $safe
	 */
	public function setDebitSafe($safe) {
		$this->debitSafe = $safe;
	}

	/**
	 * @return number
	 */
	public function getDebitTax() {
		return $this->debitTax;
	}

	/**
	 * @param number $tax
	 */
	public function setDebitTax($tax) {
		$this->debitTax = $tax;
	}

	/**
	 * @return Disposal
	 */
	public function getDisposal() {
		return $this->disposal;
	}

	/**
	 * @param Disposal $disposal
	 */
	public function setDisposal(Disposal $disposal) {
		$this->disposal = $disposal;
	}

	/**
	 * @return FleetItem
	 */
	public function getAsset() {
		return $this->asset;
	}

	/**
	 * @return Place
	 */
	public function getCourtyard() {
		return $this->courtyard;
	}

	/**
	 * @param Place $place
	 */
	public function setCourtyard(Place $place) {
		$this->courtyard = $place;
	}

	/**
	 * @return integer
	 */
	public function getClassification() {
		return $this->classification;
	}

	/**
	 * @param integer $classification
	 * @throws \DomainException
	 */
	public function setClassification($classification) {
		if (! self::isClassificationAllowed($classification)) {
			throw new \DomainException('Classification of the unserviceable asset not allowed.');
		}
		$this->classification = $classification;
	}

	/**
	 * @return integer
	 */
	public function getConservation() {
		return $this->conservation;
	}
	
	/**
	 * @return string
	 */
	public function getRating() {
	    if ($this->getConservation() > 0) {
	        $i = 1;
	        $rating = [];
	        while ($i <= $this->getConservation()) {
	            $rating[] = '<i class="icon-star"></i>';
	            $i++;
	        }
	        while ($i <= 5) {
	            $rating[] = '<i class="icon-star-empty"></i>';
	            $i++;
	        }
	        return implode('', $rating);
	    }
	    return '-';
	}

	/**
	 * @param integer $rating
	 * @throws \DomainException
	 */
	public function setConservation($rating) {
		if (! self::isConservationAllowed($rating)) {
			throw new \DomainException('Asset conservation condition not allowed.');
		}
		$this->conservation = $rating;
	}

	/**
	 * @return number
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @param number $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}
	
	/**
	 * @param Survey $survey
	 */
	public function setSurvey(Survey $survey) {
		$this->survey = $survey;
	}
	
	/**
	 * @return Survey
	 */
	public function getSurvey() {
		return $this->survey;
	}

	/**
	 * @return string
	 */
	public function getReport() {
		return $this->report;
	}

	/**
	 * @param string $report
	 */
	public function setReport($report) {
		$this->report = $report;
	}

	/**
	 * Obtem a lista de estado de conservação permitidas
	 * 
	 * @return string[]
	 */
	public static function getConservationAllowed() {
		return [1 => 'Péssimo',
				2 => 'Ruim',
			    3 => 'Regular',
				4 => 'Bom',
			    5 => 'Ótimo'
			];
	}
	
	/**
	 * Obtem a lista de classificações permitidas
     *
	 * @return string[]
	 */
	public static function getClassificationAllowed() {
		return [self::UNNECESSARY => 'Ocioso',
				self::RECOVERABLE => 'Recuperável',
				self::UNECONOMICAL => 'Antieconômico',
				self::IRRECOVERABLE => 'Irrecuperável'
		];
	}
	
	/**
	 * Verifica se o tipo de estado de conservação é permitido
	 * 
	 * @param integer $rating
	 * @return bool
	 */
	public static function isConservationAllowed( int $rating ) {
		return $rating >= 1 && $rating <= 5;
	}
	
	/**
	 * Verifica se a classificação é permitida
	 * @param integer $classification
	 * @return bool
	 */
	public static function isClassificationAllowed( int $classification ) {
		return array_key_exists($classification, self::getClassificationAllowed());
	}
	
}
?>