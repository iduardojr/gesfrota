<?php
namespace Gesfrota\Model\Domain;

/**
 * Requisição de Frete
 * @Entity
 */
class RequestFreight extends Request {
	
	/**
	 * Enviar
	 * @var integer
	 */
	const TO_SEND = 1;
	
	/**
	 * Receber
	 * @var integer
	 */
	const TO_RECEIVE = 2;
	
	/**
	 * @var string
	 */
	const REQUEST_TYPE = 'Entrega';
	
	/**
	 * @Column(type="json_array")
	 * @var array
	 */
	protected $items;
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $freight;
	
	/**
	 * @param User $user
	 * @param integer $to
	 */
	public function __construct(User $user, $to ) {
		$this->setFreight($to);
		parent::__construct($user);
	}
	
	/**
	 * @return string
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @return integer
	 */
	public function getFreight() {
		return $this->freight;
	}

	/**
	 * @param array $items
	 */
	public function setItems(array $items) {
		$this->items = $items;
	}

	/**
	 * @param integer $freight
	 * @throws \DomainException
	 */
	public function setFreight(int $freight) {
		if (!self::isFreightAllowed($freight)) {
			throw new \DomainException('The ' . $freight . ' is not freight type allowed.');
		}
		$this->freight = $freight;
	}
	
	/**
	 * @return string[]
	 */
	public static function getFreightAllowed() {
		return [self::TO_SEND => 'Enviar',
			    self::TO_RECEIVE => 'Receber'
		];
		
	}
	
	/**
	 * @param integer $freight
	 */
	public static function isFreightAllowed(int $freight) {
		return array_key_exists($freight, self::getFreightAllowed());
	}
	
}
?>