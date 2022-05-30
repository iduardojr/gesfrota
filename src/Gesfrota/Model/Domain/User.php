<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Util\Crypt;
use Gesfrota\Model\AbstractActivable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Usuário
 * @Entity
 * @EntityListeners({"Gesfrota\Model\Listener\UserListener","Gesfrota\Model\Listener\LoggerListener"})
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"M" = "Manager", "F" = "FleetManager", "D" = "Driver", "R" = "Requester"}) 
 * @Table(name="users")
 */
abstract class User extends AbstractActivable {
	
	/**
	 * Gênero Masculino
	 * @var string
	 */
	const MALE = 'M';
	
	/**
	 * Gênero Feminino
	 * @var string
	 */
	const FEMALE = 'F';
	
	/**
     * @Column(type="string")
     * @var string
     */
	protected $name;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $nif;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $gender;
	
	/**
	 * @Column(type="date")
	 * @var \DateTime
	 */
	protected $birthday;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $email;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $cell;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $password;
	
	/**
	 * @Column(name="change_password", type="string")
	 * @var boolean
	 */
	protected $changePassword;
	
	/**
	 * @ManyToOne(targetEntity="AdministrativeUnit")
	 * @JoinColumn(name="lotation_id", referencedColumnName="id")
	 * @var AdministrativeUnit
	 */
	protected $lotation;
	
	/**
	 * @ManyToMany(targetEntity="ResultCenter", indexBy="id")
	 * @JoinTable(name="users_has_center_results",
	 *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@JoinColumn(name="center_result_id", referencedColumnName="id")}
	 *      )
	 * @var ArrayCollection
	 */
	protected $resultCenters;
	
	public function __construct() {
		$this->setPassword(null);
		parent::__construct();
		$this->resultCenters = new ArrayCollection();
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getFirstName() {
		$name = explode(' ', $this->name);
		return array_shift($name);
	}
	
	/**
	 * @return string
	 */
	public function getLastName() {
		$name = explode(' ', $this->name);
		array_shift($name);
		return implode(' ', $name);
	}

	/**
	 * @return string
	 */
	public function getNif() {
		return $this->nif;
	}

	/**
	 * @return string
	 */
	public function getGender() {
		return $this->gender;
	}
	
	/**
	 * Obtem $birthday
	 *
	 * @return \DateTime
	 */
	public function getBirthday() {
		return $this->birthday;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function getCell() {
		return $this->cell;
	}

	/**
	 * Obtem $password
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}
	
	/**
	 * @return AdministrativeUnit
	 */
	public function getLotation() {
		return $this->lotation;
	}
	
	/**
	 * @return boolean
	 */
	public function isChangePassword() {
		return $this->changePassword;
	}
	
	/**
	 * @return string
	 */
	public function getUserType() {
		return constant(get_class($this) . '::USER_TYPE');
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param string $nif
	 */
	public function setNif($nif) {
		$this->nif = $nif;
	}
	
	/**
	 * @param string $gender
	 * @throws \DomainException
	 */
	public function setGender($gender) {
		$gender = strtoupper($gender);
		if (! empty($gender) && ! self::isGenderllowed($gender)) {
			throw new \DomainException($gender . ' is gender not allowed.');
		}
		$this->gender = $gender;
	}

	/**
	 * Atribui $birthday
	 *
	 * @param \DateTime $birthday
	 */
	public function setBirthday( \DateTime $birthday ) {
		$this->birthday = $birthday;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @param string $cell
	 */
	public function setCell($cell) {
		$this->cell = $cell;
	}

	/**
	 * Atribui $password
	 *
	 * @param string $password
	 */
	public function setPassword( $password ) {
		if (empty($password)) {
			$password = Crypt::suggest(10);
			$this->changePassword = true;
		} else {
			$this->changePassword = false;
		}
		$this->password = Crypt::encode($password);
	}
	
	/**
	 * @param AdministrativeUnit $unit
	 */
	public function setLotation(AdministrativeUnit $unit) {
		if ($this->lotation && $unit->getAgency() !== $this->lotation->getAgency()) {
			$this->removeAllResultCenters();
		}
		$this->lotation = $unit;
	}
	
	/**
	 * @param ResultCenter $unit
	 * @return bool
	 */
	public function addResultCenter(ResultCenter $unit) {
		return $this->resultCenters->set($unit->getId(), $unit);
	}
	
	/**
	 * @param integer|ResultCenter $unit
	 * @return false|ResultCenter
	 */
	public function removeResultCenter($unit) {
		if ($unit instanceof ResultCenter) {
			return $this->resultCenters->removeElement($unit);
		} else {
			return $this->resultCenters->remove($unit);
		}
	}
	
	public function removeAllResultCenters() {
		$this->resultCenters->clear();
	}
	
	/**
	 * @return array
	 */
	public function getAllResultCenters() {
		return $this->resultCenters->toArray();
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
		return $this->getName();
	}
	
	/**
	 * Verifica se o gênero é permitido
	 * @param string $gender
	 * @return bool
	 */
	public static function isGenderllowed( $gender ) {
		$gender = strtoupper($gender);
		return array_key_exists($gender, self::getGenderAllowed());
	}
	
	/**
	 * Obtem a lista de gêneros permitidos
	 *
	 * @return string[]
	 */
	public static function getGenderAllowed() {
		return [self::MALE => 'Masculino',
			self::FEMALE => 'Feminino'
		];
	}
	
}
?>