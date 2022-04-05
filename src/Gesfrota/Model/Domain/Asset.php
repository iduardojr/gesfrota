<?php
namespace Gesfrota\Model\Domain;

/**
 * Ativo patrimonial
 * @Embeddable
 */
class Asset {
	
	const USEFUL = 1;
	
	const UNNECESSARY = 2;
	
	const RECOVERABLE = 4;
	
	const UNECONOMICAL = 8;
	
	const IRRECOVERABLE = 16;
	
	/**
     * @Column(type="string")
     * @var string
     */
	protected $code;
	
	/**
     * @Column(type="decimal")
     * @var number
     */
    protected $value;
	
	/**
     * @Column(type="integer")
     * @var integer
     */
    protected $status;
	
	/**
	 * Construtor
	 * 
	 * @param string $code
	 * @param float $value
	 * @param integer $status
	 */
	public function __construct( $code = null, $value = null, $status = Asset::USEFUL ) {
		$this->setCode($code);
		$this->setValue($value);
		$this->setStatus($status);
	}
	
	/**
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code) {
		$this->code = $code;
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
	 * @return integer
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param integer $status
	 */
	public function setStatus($status) {
		if (!self::isStatusAllowed($status)) {
			throw new \DomainException('The ' . $status . ' is not status type allowed.');
		}
		$this->status = $status;
	}

	/**
	 * Verifica se o status do ativo é permitido
	 * @param integer $status
	 * @return bool
	 */
	public static function isStatusAllowed( int $status ) {
		return array_key_exists($status, self::getStatusAllowed());
	}
	
	/**
	 * Obtem a lista de status do ativo
	 *
	 * @return string[]
	 */
	public static function getStatusAllowed()
	{
		return [self::USEFUL => 'Útil',
			self::UNNECESSARY => 'Ocioso',
			self::RECOVERABLE => 'Recuperável',
			self::UNECONOMICAL => 'Antieconômico',
			self::IRRECOVERABLE => 'Irrecuperável'
		];
	}
	
}
?>