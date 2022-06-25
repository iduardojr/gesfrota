<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\AbstractActivable;

/**
 * Carteira de Motorista
 * @Entity
 * @Table(name="drivers_license")
 */
class DriverLicense extends AbstractActivable {
	
	/**
	 * Categoria A
	 * @var string
	 */
	const A = 'A';
	
	/**
	 * Categoria B
	 * @var string
	 */
	const B = 'B';
	
	/**
	 * Categoria C
	 * @var string
	 */
	const C = 'C';
	
	/**
	 * Categoria D
	 * @var string
	 */
	const D = 'D';
	
	/**
	 * Categoria E
	 * @var string
	 */
	const E = 'E';
	
	/**
	 * @OneToOne(targetEntity="User")
	 * @JoinColumn(name="user_id", referencedColumnName="id")
	 * @var User
	 */
	protected $user;
	
	/**
	 * @Column(type="bigint")
	 * @var integer
	 */
	protected $number;
	
	/**
	 * @Column(type="simple_array")
	 * @var array
	 */
	protected $categories;
	
	/**
	 * @Column(type="date")
	 * @var \DateTime
	 */
	protected $expires;
	
	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * @see \Gesfrota\Model\AbstractActivable::setActive()
	 */
	public function setActive($active) {
		parent::setActive($active);
		if ($this->user instanceof Driver) {
			$this->getUser()->setActive($active);
		}
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->user->getName();
	}
	
	/**
	 * @return string
	 */
	public function getNif() {
		return $this->user->getNif();
	}
	
	/**
	 * @return string
	 */
	public function getCell() {
		return $this->user->getCell();
	}
	
	/**
	 * @return AdministrativeUnit
	 */
	public function getLotation() {
		return $this->user->getLotation();
	}
	
	/**
	 * @return integer
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * @return array
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * @return \DateTime
	 */
	public function getExpires() {
		return $this->expires;
	}
	
	/**
	 * @param User $user
	 */
	public function setUser( User $user ) {
		$this->user = $user;
	}
	
	/**
	 * @param integer $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}
	
	/**
	 * @param array $categories
	 * @throws \DomainException
	 */
	public function setCategories(array $categories = null) {
		if ($categories === null) {
			$categories = [];
		}
		foreach ($categories as $category) {
			if (! self::isCategoryAllowed($category)) {
				throw new \DomainException($category . ' is licensed category not allowed.');
			}
		}
		$this->categories = $categories;
	}

	/**
	 * @param \DateTime $expires
	 */
	public function setExpires(\DateTime $expires) {
		$this->expires = $expires;
	}
	
	/**
	 * @param DriverLicense $to
	 */
	public function isEqualTo(DriverLicense $to) {
		if ( $this->getNumber() == $to->getNumber() ) {
			if ( ! array_diff($this->getCategories(), $to->getCategories())) {
				if ( $this->getExpires()->format('Ymd') == $to->getExpires()->format('Ymd') ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Verifica se a categoria da licença é permitida
	 * @param string $license
	 * @return bool
	 */
	public static function isCategoryAllowed( $license ) {
		return array_key_exists($license, self::getCategoriesAllowed());
	}
	
	/**
	 * Obtem a lista de categorias de licença permitidos
	 *
	 * @return string[]
	 */
	public static function getCategoriesAllowed() {
		return [self::A => 'A',
				self::B => 'B',
				self::C => 'C',
				self::D => 'D',
				self::E => 'E'
		];
	}
	

}
?>