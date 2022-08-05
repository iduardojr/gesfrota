<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\Entity;

/**
 * Requisição
 * @Entity
 * @Table(name="requests")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"T" = "RequestTrip", "F" = "RequestFreight"})
 */
abstract class Request extends Entity {
	
	/**
	 * Aberta
	 * @var integer
	 */
	const OPENED = 1;
	
	/**
	 * Confirmada
	 * @var integer
	 */
	const CONFIRMED = 2;
	
	/**
	 * Inicializada
	 * @var integer
	 */
	const INITIATED = 4;
	
	/**
	 * Finalizada
	 * @var integer
	 */
	const FINISHED = 8;
	
	/**
	 * Cancelada
	 * @var integer
	 */
	const CANCELED = 16;
	
	/**
	 * Recusada
	 * 
	 * @var integer
	 */
	const DECLINED = 32;
	
	/**
	 * @Embedded(class="Place")
	 * @var Place
	 */
	protected $from;
	
	/**
	 * @Embedded(class="Place")
	 * @var Place
	 */
	protected $to;
	
	/**
	 * @Column(type="object")
	 * @var Place[]
	 */
	protected $waypoints;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $service;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $justify;
	
	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $schedule;
	
    /**
     * @ManyToOne(targetEntity="AdministrativeUnit")
     * @JoinColumn(name="requester_unit_id", referencedColumnName="id")
     * @var AdministrativeUnit
     */
    protected $requesterUnit;
    
    /**
     * @ManyToOne(targetEntity="ResultCenter")
     * @JoinColumn(name="center_result_id", referencedColumnName="id")
     * @var ResultCenter
     */
    protected $resultCenter;
	
    /**
     * @ManyToOne(targetEntity="Vehicle", cascade={"all"})
     * @JoinColumn(name="vehicle_id", referencedColumnName="id")
     * @var Vehicle
     */
	protected $vehicle;
	
	/**
     * @ManyToOne(targetEntity="DriverLicense")
     * @JoinColumn(name="driver_license_id", referencedColumnName="id")
     * @var DriverLicense
     */
	protected $driverLicense;
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $status;
	
	/**
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="opened_by", referencedColumnName="id")
	 * @var User
	 */
	protected $openedBy;
	
	/**
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="confirmed_by", referencedColumnName="id")
	 * @var User
	 */
	protected $confirmedBy;
	
	/**
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="initiated_by", referencedColumnName="id")
	 * @var User
	 */
	protected $initiatedBy;
	
	/**
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="finished_by", referencedColumnName="id")
	 * @var User
	 */
	protected $finishedBy;
	
	/**
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="canceled_by", referencedColumnName="id")
	 * @var User
	 */
	protected $canceledBy;
	
	/**
	 * @Column(name="opened_at", type="datetime")
	 * @var \DateTime
	 */
	protected $openedAt;
	
	/**
	 * @Column(name="confirmed_at", type="datetime")
	 * @var \DateTime
	 */
	protected $confirmedAt;
	
	/**
	 * @Column(name="initiated_at", type="datetime")
	 * @var \DateTime
	 */
	protected $initiatedAt;
	
	/**
	 * @Column(name="finished_at", type="datetime")
	 * @var \DateTime
	 */
	protected $finishedAt;
	
	/**
	 * @Column(name="canceled_at", type="datetime")
	 * @var \DateTime
	 */
	protected $canceledAt;
	
	/**
	 * @Column(name="odometer_initial", type="integer")
	 * @var integer
	 */
	protected $odometerInitial;
	
	/**
	 * @Column(name="odometer_final", type="integer")
	 * @var integer
	 */
	protected $odometerFinal;
	
	/**
	 * @param User $user
	 */
	public function __construct(User $user) {
		parent::__construct();
		$this->openedBy = $user;
		if (! $user instanceof Manager) {
			$this->requesterUnit = $user->getLotation();
		}
		$this->status = self::OPENED;
		$this->openedAt = new \DateTime('now');
	}
	
	/**
	 * @return Place[]
	 */
	public function getItinerary() {
		return array_merge([$this->from], $this->waypoints, [$this->to]);
	}
	
	/**
	 * @return array
	 */
	public function getOptDiretions() {
	    $options = [];
	    if ($this->from) {
	       $options['origin'] = ['placeId' => $this->from->getPlace()];
	    }
	    foreach($this->waypoints as $point) {
	       $options['waypoints'][] = ['location' => ['placeId' => $point->getPlace()], 'stopover' => true];
	    }
	    if ($this->to) {
	       $options['destination'] = ['placeId' => $this->to->getPlace()];
	    }
	    return $options;
	}

	/**
	 * @return \DateTime
	 */
	public function getSchedule() {
		return $this->schedule;
	}

	/**
	 * @return AdministrativeUnit
	 */
	public function getRequesterUnit() {
		return $this->requesterUnit;
	}

	/**
	 * @return Vehicle
	 */
	public function getVehicle() {
		return $this->vehicle;
	}

	/**
	 * @return DriverLicense
	 */
	public function getDriverLicense() {
		return $this->driverLicense;
	}
	
	
	/**
	 * 
	 * @return User|NULL
	 */
	public function getDriver() {
	    if ($this->driverLicense) {
	       return $this->driverLicense->getUser();
	    }
	    return null;
	}

	/**
	 * @return integer
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * @return User
	 */
	public function getOpenedBy() {
		return $this->openedBy;
	}
	
	
	/**
	 * @return User
	 */
	public function getConfirmedBy() {
		return $this->confirmedBy;
	}
	
	/**
	 * @return User
	 */
	public function getInitiatedBy() {
		return $this->initiatedBy;
	}
	
	/**
	 * @return User
	 */
	public function getFinishedBy() {
		return $this->finishedBy;
	}
	
	/**
	 * @return User
	 */
	public function getCanceledBy() {
		return $this->canceledBy;
	}
	
	/**
	 * @return \DateTime
	 */
	public function getOpenedAt() {
		return $this->openedAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getConfirmedAt() {
		return $this->confirmedAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getInitiatedAt() {
		return $this->initiatedAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getFinishedAt() {
		return $this->finishedAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getCanceledAt() {
		return $this->canceledAt;
	}
	
	/**
	 * @return \DateTime
	 */
	public function getUpdateAt() {
		switch ($this->getStatus()) {
			case Request::OPENED:
				return $this->getOpenedAt();
				break;
			
			case Request::CONFIRMED:
				return $this->getConfirmedAt();
				break;
				
			case Request::INITIATED:
				return $this->getInitiatedAt();
				break;
				
			case Request::FINISHED:
				return $this->getFinishedAt();
				break;
				
			case Request::CANCELED:
			case Request::DECLINED:
				return $this->getCanceledAt();
				break;
				
		}
	}

	/**
	 * @return array
	 */
	public function getHistory() {
		$history = [];
		switch ($this->status) {
			case self::CANCELED: 
				$history[self::CANCELED] = $this->canceledAt;
				
			case self::FINISHED:
				if ($this->finishedAt) {
					$history[self::FINISHED] = $this->finishedAt;
				}
				
			case self::INITIATED:
				if ($this->initiatedAt) {
					$history[self::INITIATED] = $this->initiatedAt;
				}
				
			case self::CONFIRMED:
				if ($this->confirmedAt) {
					$history[self::CONFIRMED] = $this->confirmedAt;
				}
				
			default:
				if ($this->status == self::UNAVAILABLE) {
					$history[self::UNAVAILABLE] = $this->canceledAt;
				}
				$history[self::OPENED] = $this->openedAt;
		}
		return $history;
	}
	
	/**
	 * @return Place
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * @return Place
	 */
	public function getTo() {
		return $this->to;
	}
	
	/**
	 * @return string
	 */
	public function getService() {
		return $this->service;
	}
	
	/**
	 * @return number
	 */
	public function getOdometerInitial() {
		return $this->odometerInitial;
	}

	/**
	 * @return number
	 */
	public function getOdometerFinal() {
		return $this->odometerFinal;
	}
	
	
	/**
	 * @return string
	 */
	public function getJustify() {
		return $this->justify;
	}

	/**
	 * @return Place[]
	 */
	public function getWaypoints() {
		return $this->waypoints;
	}
	
	/**
	 * @return ResultCenter
	 */
	public function getResultCenter() {
		return $this->resultCenter;
	}

	/**
	 * @param AdministrativeUnit $unit
	 * @throws \DomainException
	 */
	public function setRequesterUnit(AdministrativeUnit $unit) {
		if (! $this->openedBy instanceof Manager) {
			if ( $this->openedBy instanceof FleetManager ) {
				if ( $this->openedBy->getLotation()->getAgency() !== $unit->getAgency() ) {
					throw new \DomainException('the Requester Unit #' . $unit->getCode(). ' not is valid.');
				}
			} elseif ($this->openedBy->getLotation() !== $unit) {
				throw new \DomainException('the Requester Unit #' . $unit->getCode(). ' not is valid.');
			}
		}
		$this->requesterUnit = $unit;
	}
	
	/**
	 * @param Place $from
	 */
	public function setFrom(Place $from) {
		$this->from = $from;
	}
	
	/**
	 * @param Place $to
	 */
	public function setTo(Place $to) {
		$this->to = $to;
	}
	
	/**
	 * @param Place[] $waypoints
	 * @throws \InvalidArgumentException
	 */
	public function setWaypoints(array $waypoints) {
		foreach ($waypoints as $key => $waypoint) {
			if (! $waypoint instanceof Place) {
				throw new \InvalidArgumentException('waypoint[' . $key . '] not is place valid.');
			}
		}
		$this->waypoints = $waypoints;
	}
	
	/**
	 * @param Place $from
	 * @param Place $to
	 * @param Place[] $waypoints
	 * @throws \InvalidArgumentException
	 */
	public function setItinerary(Place $from, Place $to, array $waypoints = []) {
		$this->setFrom($from);
		$this->setTo($to);
		$this->setWaypoints($waypoints);
	}
	
	/**
	 * @param string $service
	 */
	public function setService($service) {
		$this->service = $service;
	}
	
	/**
	 * @param \DateTime $schedule
	 * @throws \DomainException
	 */
	public function setSchedule(\DateTime $schedule = null) {
		$now = new \DateTime('now');
		if ($schedule === null) {
			$schedule = $now;
		} else if ($schedule < $now ) {
			throw new \DomainException('the schedule cannot be in the past.');
		}
		$this->schedule = $schedule;
	}
	
	/**
	 * @param ResultCenter $unit
	 * @throws \DomainException
	 */
	public function setResultCenter(ResultCenter $unit) {
		if ( $this->openedBy->getLotation() != $this->requesterUnit && ( $this->openedBy instanceof Manager || $this->openedBy instanceof FleetManager )) {
			$actived = $this->requesterUnit->getAgency()->getResultCentersActived();
		} else {
			$actived = $this->openedBy->getResultCentersActived();
		}
		if (! isset($actived[$unit->getId()])) {
			throw new \DomainException('the result center #' . $unit->getCode(). ' not is valid.');
		}
		$this->resultCenter = $unit;
	}
	
	/**
	 * @param User $user
	 * @param Vehicle $vehicle
	 * @param User $driver
	 * @throws \DomainException
	 */
	public function toConfirm(User $user, Vehicle $vehicle, User $driver) {
		if ($this->status != self::OPENED) {
			throw new \DomainException('request cannot be confirmed: request is not opened.');
		}
		if ( $driver->getDriverLicense() == null ) {
			throw new \DomainException('request cannot be confirmed: driver is not licensed.');
		}
		if ($this->requesterUnit->getAgency()->isResultCenterRequired()) {
			$allowed = $vehicle->getResultCentersActived();
			if ( ! isset($allowed[$this->getResultCenter()->getId()]) ) {
				throw new \DomainException('request cannot be confirmed: result center of vehicle not allowed.');
			}
			
			$allowed = $driver->getResultCentersActived();
			if ( ! isset($allowed[$this->getResultCenter()->getId()]) ) {
				throw new \DomainException('request cannot be confirmed: result center of driver not allowed.');
			}
		}
		$this->vehicle = $vehicle;
		$this->driverLicense = $driver->getDriverLicense();
		$this->status = self::CONFIRMED;
		$this->confirmedBy = $user;
		$this->confirmedAt = new \DateTime('now');
	}
	
	/**
	 * @param User $user
	 * @param integer $odometer
	 * @throws \DomainException
	 * @throws \InvalidArgumentException
	 */
	public function toInitiate(User $user, $odometer) {
		if ($this->status != self::CONFIRMED) {
			throw new \DomainException('request cannot be initiated.');
		}
		if ($odometer < $this->vehicle->getOdometer()) {
			throw new \InvalidArgumentException('Initial odometer cannot be less than the vehicle odometer: ' . $this->vehicle->getOdometer() . ' km.');
		}
		$this->status = self::INITIATED;
		$this->odometerInitial = (int) $odometer;
		$this->vehicle->setOdometer($odometer);
		$this->initiatedBy = $user;
		$this->initiatedAt = new \DateTime('now');
	}
	
	/**
	 * @param User $user
	 * @param integer $odometer
	 * @param string $note
	 * @throws \DomainException
	 * @throws \InvalidArgumentException
	 */
	public function toFinish(User $user, $odometer, $note = null) {
		if ($this->status != self::INITIATED) {
			throw new \DomainException('request cannot be finished.');
		}
		if ($odometer < $this->vehicle->getOdometer()) {
			throw new \InvalidArgumentException('Final odometer cannot be less than the vehicle odometer: ' . $this->vehicle->getOdometer() . ' km.');
		}
		$this->status = self::FINISHED;
		$this->odometerFinal = $odometer;
		$this->vehicle->setOdometer($odometer);
		$this->justify = $note;
		$this->finishedBy = $user;
		$this->finishedAt = new \DateTime('now');
	}
	
	/**
	 * @param User $user
	 * @param string $justify
	 * @throws \DomainException
	 */
	public function toCancel(User $user, $justify) {
		if ($this->status > self::INITIATED) {
			throw new \DomainException('request cannot be canceled.');
		}
		$this->status = self::CANCELED;
		$this->justify = $justify;
		$this->canceledBy = $user;
		$this->canceledAt = new \DateTime('now');
	}
	
	/**
	 * @param User $user
	 * @param string $justify
	 * @throws \DomainException
	 */
	public function toDecline(User $user, $justify) {
		if ($this->status > self::INITIATED) {
			throw new \DomainException('request cannot be declined.');
		}
		$this->status = self::DECLINED;
		$this->justify = $justify;
		$this->canceledBy = $user;
		$this->canceledAt = new \DateTime('now');
	}
	
	/**
	 * @return string[]
	 */
	public function getStateAllowed() {
		switch ($this->status) {
			case self::OPENED: 
				return [self::CONFIRMED => ['icon-ok', 'Confirmar '.$this->getRequestType()],
						self::DECLINED => ['icon-remove-sign', 'Recusar ' . $this->getRequestType()],
						self::CANCELED => ['icon-remove', 'Cancelar '.$this->getRequestType()]];
				break;
				
			case self::CONFIRMED: 
				return [self::INITIATED => ['icon-play', 'Iniciar '.$this->getRequestType()],
						self::CANCELED => ['icon-remove', 'Cancelar '.$this->getRequestType()]];
						break;
						
			case self::INITIATED:
				return [self::FINISHED => ['icon-stop', 'Finalizar '.$this->getRequestType()]];
				break;
				
			case self::FINISHED:
			case self::CANCELED:
			case self::DECLINED:
				return [];
				break;
		}
	}
	
	/**
	 * @return string
	 */
	public function getRequestType() {
		return constant(get_class($this) . '::REQUEST_TYPE');
	}
	
	/**
	 * @return string[]
	 */
	public static function getStatusAllowed() {
		return [self::OPENED => 'Aberta',
				self::CONFIRMED => 'Confirmada',
			    self::CANCELED => 'Cancelada',
			    self::INITIATED => 'Iniciada',
			    self::FINISHED => 'Concluída',
				self::DECLINED => 'Recusada'
		];
		
	}
	
	/**
	 * @param integer $state
	 * @return boolean
	 */
	public static function isStatusAllowed(int $state) {
		return array_key_exists($state, self::getStatusAllowed());
	}
	
}
?>