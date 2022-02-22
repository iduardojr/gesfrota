<?php
namespace Gesfrota\Model\Domain;

/**
 * Requisição de Viagem
 * @Entity
 */
class RequestTrip extends Request {
	
	/**
	 * @var string
	 */
	const REQUEST_TYPE = 'Viagem';
	
	/**
	 * @Column(type="json_array")
	 * @var array
	 */
	protected $passengers;
	
	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $duration;
	
	/**
	 * @OneToOne(targetEntity="RequestTrip", cascade={"all"})
     * @JoinColumn(name="trip_round_id", referencedColumnName="id")
     * 
	 * @var RequestTrip
	 */
	protected $roundTrip;
	
	/**
	 * @param User $user
	 * @param boolean $roundTrip
	 */
	public function __construct(User $user, $roundTrip = false) {
		$this->waypoints = [];
		$this->passengers = [];
		if ($roundTrip) {
			$this->roundTrip = new RequestTrip($user);
		}
		parent::__construct($user);
	}
	
	/**
	 * @return RequestTrip
	 */
	public function getRoundTrip() {
		return $this->roundTrip;
	}
	
	/**
	 * @return integer
	 */
	public function getAmountPassengers() {
		return count($this->passengers);
	}
	
	/**
	 * @return array
	 */
	public function getPassengers() {
		return $this->passengers;
	}
	
	/**
	 * @return \DateTime
	 */
	public function getDuration() {
		return $this->duration;
	}
	
	
	public function setFrom(Place $from) {
		parent::setFrom($from);
		if ($this->roundTrip) {
			$this->roundTrip->setTo($from);
		}
	}
	
	public function setTo(Place $to) {
		parent::setTo($to);
		if ($this->roundTrip) {
			$this->roundTrip->setFrom($to);
		}
	}
	
	public function setWaypoints(array $waypoints) {
		parent::setWaypoints($waypoints);
		if ($this->roundTrip) {
			$this->roundTrip->setWaypoints(array_reverse($waypoints));
		}
	}
	
	public function setService($service) {
		parent::setService($service);
		if ($this->roundTrip) {
			$this->roundTrip->setService("RETORNO: \n". $service);
		}
	}
	
	/**
	 * @param \DateTime $duration
	 */
	public function setDuration(\DateTime $duration) {
		$this->duration = $duration;
		if ($this->roundTrip) {
			$this->roundTrip->setSchedule($duration);
			$this->roundTrip->setDuration($duration);
		}
	}
	
	/**
	 * @param array $passengers
	 * @throws \DomainException
	 */
	public function setPassengers(array $passengers) {
		if ( count($passengers) <  1) {
			throw new \DomainException('the amount of passengers must be greater than zero');
		}
		$this->passengers = $passengers;
		if ($this->roundTrip) {
			$this->roundTrip->setPassengers($passengers);
		}
	}
	
	public function toCancel(User $user, $justifiy) {
		parent::toCancel($user, $justifiy);
		if ($this->roundTrip) {
			$this->roundTrip->toCancel($user, $justifiy);
		}
	}
	
	public function toDecline(User $user, $justifiy) {
		parent::toDecline($user, $justifiy);
		if ($this->roundTrip) {
			$this->roundTrip->toDecline($user, $justifiy);
		}
	}
	
	public function toConfirm(User $user, Vehicle $vehicle, Driver $driver, $roundTrip = false) {
		parent::toConfirm($user, $vehicle, $driver);
		if ($this->roundTrip && $roundTrip ) {
			$this->roundTrip->toConfirm($user, $vehicle, $driver);
		}
	}
	
}
?>