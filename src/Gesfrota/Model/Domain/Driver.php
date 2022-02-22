<?php
namespace Gesfrota\Model\Domain;

/**
 * Motorista
 * @Entity
 */
class Driver extends User {
	
	/**
	 * @var string
	 */
	const USER_TYPE = 'Motorista';
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $license;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $renach;
	
	/**
	 * @Column(type="simple_array")
	 * @var array
	 */
	protected $vehicles;
	
	/**
	 * @Column(type="date")
	 * @var \DateTime
	 */
	protected $expires;
	

	/**
	 * @return integer
	 */
	public function getLicense() {
		return $this->license;
	}

	/**
	 * @return string
	 */
	public function getRenach() {
		return $this->renach;
	}

	/**
	 * @return array
	 */
	public function getVehicles() {
		return $this->vehicles;
	}

	/**
	 * @return \DateTime
	 */
	public function getExpires() {
		return $this->expires;
	}

	/**
	 * @param integer $license
	 */
	public function setLicense(int $license) {
		$this->license = $license;
	}

	/**
	 * @param string $renach
	 */
	public function setRenach($renach) {
		$this->renach = $renach;
	}

	/**
	 * @param array $licenses
	 */
	public function setVehicles(array $licenses = null) {
		if ($licenses === null) {
			$licenses = [];
		}
		foreach ($licenses as $license) {
			if (! self::isLicensellowed($license)) {
				throw new \DomainException($license . ' is licensed vehicles not allowed.');
			}
		}
		$this->vehicles = $licenses;
	}

	/**
	 * @param \DateTime $expires
	 */
	public function setExpires(\DateTime $expires) {
		$this->expires = $expires;
	}

	/**
	 * Verifica se a categoria da licença é permitida
	 * @param string $license
	 * @return bool
	 */
	public static function isLicensellowed( $license ) {
		return array_key_exists($license, self::getLicenseAllowed());
	}
	
	/**
	 * Obtem a lista de categorias de licença permitidos
	 *
	 * @return string[]
	 */
	public static function getLicenseAllowed() {
		return [License::A => 'A',
				License::B => 'B',
				License::C => 'C',
				License::D => 'D',
				License::E => 'E'
		];
	}
	

}
?>