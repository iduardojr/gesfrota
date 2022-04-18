<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\Entity;

/**
 * Vistória
 * @Entity
 * @Table(name="surveys")
 */
class Survey extends Entity {
	
	/**
	 * Não Avaliado
	 * @var string
	 */
	const UNVALUED = 'N/A';
	
	/**
	 * Riscado
	 * @var string
	 */
	const SCRATCHED = 'R';
	
	/**
	 * Amassado 
	 * @var string
	 */
	const KNEADED = 'A';
	
	/**
	 * Quebrado
	 * @var string
	 */
	const BROKEN = 'Q';
	
	/**
	 * Faltante
	 * @var string
	 */
	const MISSING = 'F';
	
	/**
	 * Bom
	 * @var string
	 */
	const GOOD = 'OK';
	
	/**
	 * @Column(name="lantern_fl", type="string")
	 * @var string
	 */
	protected $lanternFrontLeft;
	
	/**
	 * @Column(name="lantern_fr", type="string")
	 * @var string
	 */
	protected $lanternFrontRight;
	
	/**
	 * @Column(name="lantern_bl", type="string")
	 * @var string
	 */
	protected $lanternBackLeft;
	
	/**
	 * @Column(name="lantern_br", type="string")
	 * @var string
	 */
	protected $lanternBackRight;
	
	/**
	 * @Column(name="lantern_arrow_fl", type="string")
	 * @var string
	 */
	protected $lanternArrowFrontLeft;
	
	/**
	 * @Column(name="lantern_arrow_fr", type="string")
	 * @var string
	 */
	protected $lanternArrowFrontRight;
	
	/**
	 * @Column(name="lantern_arrow_bl", type="string")
	 * @var string
	 */
	protected $lanternArrowBackLeft;
	
	/**
	 * @Column(name="lantern_arrow_br", type="string")
	 * @var string
	 */
	protected $lanternArrowBackRight;
	
	/**
	 * @Column(name="bumper_f", type="string")
	 * @var string
	 */
	protected $bumperFront;
	
	/**
	 * @Column(name="bumper_b", type="string")
	 * @var string
	 */
	protected $bumperBack;
	
	/**
	 * @Column(name="cover_f", type="string")
	 * @var string
	 */
	protected $coverFront;
	
	/**
	 * @Column(name="cover_b", type="string")
	 * @var string
	 */
	protected $coverBack;
	
	/**
	 * @Column(name="windshield_f", type="string")
	 * @var string
	 */
	protected $windshieldFront;
	
	/**
	 * @Column(name="windshield_b", type="string")
	 * @var string
	 */
	protected $windshieldBack;
	
	/**
	 * @Column(name="roof", type="string")
	 * @var string
	 */
	protected $roof;
	
	/**
	 * @Column(name="exhaust", type="string")
	 * @var string
	 */
	protected $exhaust;
	
	/**
	 * @Column(name="window_fl", type="string")
	 * @var string
	 */
	protected $windowFrontLeft;
	
	/**
	 * @Column(name="window_fr", type="string")
	 * @var string
	 */
	protected $windowFrontRight;
	
	/**
	 * @Column(name="window_bl", type="string")
	 * @var string
	 */
	protected $windowBackLeft;
	
	/**
	 * @Column(name="window_br", type="string")
	 * @var string
	 */
	protected $windowBackRight;
	
	/**
	 * @Column(name="door_fl", type="string")
	 * @var string
	 */
	protected $doorFrontLeft;
	
	/**
	 * @Column(name="door_fr", type="string")
	 * @var string
	 */
	protected $doorFrontRight;
	
	/**
	 * @Column(name="door_bl", type="string")
	 * @var string
	 */
	protected $doorBackLeft;
	
	/**
	 * @Column(name="door_br", type="string")
	 * @var string
	 */
	protected $doorBackRight;
	
	/**
	 * @Column(name="column_fl", type="string")
	 * @var string
	 */
	protected $columnFrontLeft;
	
	/**
	 * @Column(name="column_fr", type="string")
	 * @var string
	 */
	protected $columnFrontRight;
	
	/**
	 * @Column(name="column_bl", type="string")
	 * @var string
	 */
	protected $columnBackLeft;
	
	/**
	 * @Column(name="column_br", type="string")
	 * @var string
	 */
	protected $columnBackRight;
	
	/**
	 * @Column(name="stirrup_fl", type="string")
	 * @var string
	 */
	protected $stirrupFrontLeft;
	
	/**
	 * @Column(name="stirrup_fr", type="string")
	 * @var string
	 */
	protected $stirrupFrontRight;
	
	/**
	 * @Column(name="stirrup_bl", type="string")
	 * @var string
	 */
	protected $stirrupBackLeft;
	
	/**
	 * @Column(name="stirrup_br", type="string")
	 * @var string
	 */
	protected $stirrupBackRight;
	
	/**
	 * @Column(name="fender_fl", type="string")
	 * @var string
	 */
	protected $fenderFrontLeft;
	
	/**
	 * @Column(name="fender_fr", type="string")
	 * @var string
	 */
	protected $fenderFrontRight;
	
	/**
	 * @Column(name="fender_bl", type="string")
	 * @var string
	 */
	protected $fenderBackLeft;
	
	/**
	 * @Column(name="fender_br", type="string")
	 * @var string
	 */
	protected $fenderBackRight;
	
	/**
	 * @Column(name="tire_fl", type="string")
	 * @var string
	 */
	protected $tireFrontLeft;
	
	/**
	 * @Column(name="tire_fr", type="string")
	 * @var string
	 */
	protected $tireFrontRight;
	
	/**
	 * @Column(name="tire_bl", type="string")
	 * @var string
	 */
	protected $tireBackLeft;
	
	/**
	 * @Column(name="tire_br", type="string")
	 * @var string
	 */
	protected $tireBackRight;
	
	/**
	 * @Column(name="seat_driver", type="string")
	 * @var string
	 */
	protected $seatDriver;
	
	/**
	 * @Column(name="seat_passenger", type="string")
	 * @var string
	 */
	protected $seatPassenger;
	
	/**
	 * @Column(name="seat_rear", type="string")
	 * @var string
	 */
	protected $seatRear;
	
	/**
	 * @Column(name="dashboard", type="string")
	 * @var string
	 */
	protected $dashboard;
	
	/**
	 * @Column(name="steering_wheel", type="string")
	 * @var string
	 */
	protected $steeringWheel;
	
	/**
	 * @Column(name="horn", type="string")
	 * @var string
	 */
	protected $horn;
	
	/**
	 * @Column(name="central_console", type="string")
	 * @var string
	 */
	protected $centralConsole;
	
	/**
	 * @Column(name="roof_tapestry", type="string")
	 * @var string
	 */
	protected $roofTapestry;
	
	/**
	 * @Column(name="trunk_cap", type="string")
	 * @var string
	 */
	protected $trunkCap;
	
	/**
	 * @Column(name="door_lining", type="string")
	 * @var string
	 */
	protected $doorLining;
	
	/**
	 * @Column(name="air_conditioning", type="string")
	 * @var string
	 */
	protected $airConditioning;
	/**
	 * @Column(name="alarm", type="string")
	 * @var string
	 */
	protected $alarm;
	
	/**
	 * @Column(name="steering_hydraulic", type="string")
	 * @var string
	 */
	protected $steeringHydraulic;
	
	/**
	 * @Column(name="device_sound", type="string")
	 * @var string
	 */
	protected $deviceSound;
	
	/**
	 * @Column(name="electric_glass", type="string")
	 * @var string
	 */
	protected $electricGlass;
	
	/**
	 * @Column(name="electric_lock", type="string")
	 * @var string
	 */
	protected $electricLock;
	
	/**
	 * @Column(name="carpet", type="string")
	 * @var string
	 */
	protected $carpet;
	
	/**
	 * @Column(name="wheel_iron", type="string")
	 * @var string
	 */
	protected $wheelIron;
	
	/**
	 * @Column(name="wheel_alloy", type="string")
	 * @var string
	 */
	protected $wheelAlloy;
	
	/**
	 * @Column(name="lantern_fog", type="string")
	 * @var string
	 */
	protected $lanternFog;
	
	/**
	 * @Column(name="carburetor", type="string")
	 * @var string
	 */
	protected $carburetor;
	
	/**
	 * @Column(name="exchange", type="string")
	 * @var string
	 */
	protected $exchange;
	
	/**
	 * @Column(name="differential", type="string")
	 * @var string
	 */
	protected $differential;
	
	/**
	 * @Column(name="engine", type="string")
	 * @var string
	 */
	protected $engine;
	
	/**
	 * @Column(name="radiator", type="string")
	 * @var string
	 */
	protected $radiator;
	
	/**
	 * @Column(name="turbine", type="string")
	 * @var string
	 */
	protected $turbine;
	
	/**
	 * @Column(name="suspension", type="string")
	 * @var string
	 */
	protected $suspension;
	
	/**
	 * @Column(name="injection_pump", type="string")
	 * @var string
	 */
	protected $injectionPump;
	
	/**
	 * @Column(name="injection_nozzle", type="string")
	 * @var string
	 */
	protected $injectionNozzle;
	
	/**
	 * @Column(name="injection_electronic", type="string")
	 * @var string
	 */
	protected $injectionElectronic;
	
	/**
	 * @Column(name="gasoline_pump", type="string")
	 * @var string
	 */
	protected $gasolineBump;
	
	/**
	 * @Column(name="engine_starter", type="string")
	 * @var string
	 */
	protected $engineStarter;
	
	/**
	 * @Column(name="ignition_module", type="string")
	 * @var string
	 */
	protected $ignitionModule;
	
	/**
	 * @Column(name="alternator", type="string")
	 * @var string
	 */
	protected $alternator;
	
	/**
	 * @Column(name="distributor", type="string")
	 * @var string
	 */
	protected $distributor;
	
	/**
	 * @Column(name="battery", type="string")
	 * @var string
	 */
	protected $battery;
	
	/**
	 * @Column(name="safety_belts", type="string")
	 * @var string
	 */
	protected $safetyBelts;
	
	/**
	 * @Column(name="airbag", type="string")
	 * @var string
	 */
	protected $airbag;
	
	/**
	 * @Column(name="rearview_i", type="string")
	 * @var string
	 */
	protected $rearviewInternal;
	
	/**
	 * @Column(name="rearview_l", type="string")
	 * @var string
	 */
	protected $rearviewLeft;
	
	/**
	 * @Column(name="rearview_r", type="string")
	 * @var string
	 */
	protected $rearviewRight;
	
	/**
	 * @Column(name="safety_triangle", type="string")
	 * @var string
	 */
	protected $safetyTriangle;
	
	/**
	 * @Column(name="monkey", type="string")
	 * @var string
	 */
	protected $monkey;
	
	/**
	 * @Column(name="wheel_wrench", type="string")
	 * @var string
	 */
	protected $wheelWrench;
	
	/**
	 * @Column(name="wheel_spare", type="string")
	 * @var string
	 */
	protected $wheelSpare;
	
	/**
	 * @Column(name="note", type="string")
	 * @var string
	 */
	protected $note;
	
	/**
	 * @return string
	 */
	public function getLanternFrontLeft() {
		return $this->lanternFrontLeft;
	}

	/**
	 * @return string
	 */
	public function getLanternFrontRight() {
		return $this->lanternFrontRight;
	}
	
	/**
	 * @return string
	 */
	public function getLanternBackLeft() {
		return $this->lanternBackLeft;
	}
	
	/**
	 * @return string
	 */
	public function getLanternBackRight() {
		return $this->lanternBackRight;
	}
	
	/**
	 * @return string
	 */
	public function getLanternArrowFrontLeft() {
		return $this->lanternArrowFrontLeft;
	}
	
	/**
	 * @return string
	 */
	public function getLanternArrowFrontRight() {
		return $this->lanternArrowFrontRight;
	}
	
	/**
	 * @return string
	 */
	public function getLanternArrowBackLeft() {
		return $this->lanternArrowBackLeft;
	}
	
	/**
	 * @return string
	 */
	public function getLanternArrowBackRight() {
		return $this->lanternArrowBackRight;
	}
	
	/**
	 * @return string
	 */
	public function getBumperFront() {
		return $this->bumperFront;
	}
	
	/**
	 * @return string
	 */
	public function getBumperBack() {
		return $this->bumperBack;
	}
	
	/**
	 * @return string
	 */
	public function getCoverFront() {
		return $this->coverFront;
	}
	
	/**
	 * @return string
	 */
	public function getCoverBack() {
		return $this->coverBack;
	}
	
	/**
	 * @return string
	 */
	public function getWindshieldFront() {
		return $this->windshieldFront;
	}
	
	/**
	 * @return string
	 */
	public function getWindshieldBack() {
		return $this->windshieldBack;
	}
	
	/**
	 * @return string
	 */
	public function getRoof() {
		return $this->roof;
	}
	
	/**
	 * @return string
	 */
	public function getExhaust() {
		return $this->exhaust;
	}
	
	/**
	 * @return string
	 */
	public function getWindowFrontLeft() {
		return $this->windowFrontLeft;
	}
	
	/**
	 * @return string
	 */
	public function getWindowFrontRight() {
		return $this->windowFrontRight;
	}
	
	/**
	 * @return string
	 */
	public function getWindowBackLeft() {
		return $this->windowBackLeft;
	}
	
	/**
	 * @return string
	 */
	public function getWindowBackRight() {
		return $this->windowBackRight;
	}
	
	/**
	 * @return string
	 */
	public function getDoorFrontLeft() {
		return $this->doorFrontLeft;
	}
	
	/**
	 * @return string
	 */
	public function getDoorFrontRight() {
		return $this->doorFrontRight;
	}
	
	/**
	 * @return string
	 */
	public function getDoorBackLeft() {
		return $this->doorBackLeft;
	}
	
	/**
	 * @return string
	 */
	public function getDoorBackRight() {
		return $this->doorBackRight;
	}
	
	/**
	 * @return string
	 */
	public function getColumnFrontLeft() {
		return $this->columnFrontLeft;
	}
	
	/**
	 * @return string
	 */
	public function getColumnFrontRight() {
		return $this->columnFrontRight;
	}
	
	/**
	 * @return string
	 */
	public function getColumnBackLeft() {
		return $this->columnBackLeft;
	}
	
	/**
	 * @return string
	 */
	public function getColumnBackRight() {
		return $this->columnBackRight;
	}
	
	/**
	 * @return string
	 */
	public function getStirrupFrontLeft() {
		return $this->stirrupFrontLeft;
	}
	
	/**
	 * @return string
	 */
	public function getStirrupFrontRight() {
		return $this->stirrupFrontRight;
	}
	
	/**
	 * @return string
	 */
	public function getStirrupBackLeft() {
		return $this->stirrupBackLeft;
	}
	
	/**
	 * @return string
	 */
	public function getStirrupBackRight() {
		return $this->stirrupBackRight;
	}
	
	/**
	 * @return string
	 */
	public function getFenderFrontLeft() {
		return $this->fenderFrontLeft;
	}
	
	/**
	 * @return string
	 */
	public function getFenderFrontRight() {
		return $this->fenderFrontRight;
	}
	
	/**
	 * @return string
	 */
	public function getFenderBackLeft() {
		return $this->fenderBackLeft;
	}
	
	/**
	 * @return string
	 */
	public function getFenderBackRight() {
		return $this->fenderBackRight;
	}
	
	/**
	 * @return string
	 */
	public function getTireFrontLeft() {
		return $this->tireFrontLeft;
	}
	
	/**
	 * @return string
	 */
	public function getTireFrontRight() {
		return $this->tireFrontRight;
	}
	
	/**
	 * @return string
	 */
	public function getTireBackLeft() {
		return $this->tireBackLeft;
	}
	
	/**
	 * @return string
	 */
	public function getTireBackRight() {
		return $this->tireBackRight;
	}
	
	/**
	 * @return string
	 */
	public function getSeatDriver() {
		return $this->seatDriver;
	}
	
	/**
	 * @return string
	 */
	public function getSeatPassenger() {
		return $this->seatPassenger;
	}
	
	/**
	 * @return string
	 */
	public function getSeatRear() {
		return $this->seatRear;
	}
	
	/**
	 * @return string
	 */
	public function getDashboard() {
		return $this->dashboard;
	}
	
	/**
	 * @return string
	 */
	public function getSteeringWheel() {
		return $this->steeringWheel;
	}
	
	/**
	 * @return string
	 */
	public function getHorn() {
		return $this->horn;
	}
	
	/**
	 * @return string
	 */
	public function getCentralConsole() {
		return $this->centralConsole;
	}
	
	/**
	 * @return string
	 */
	public function getRoofTapestry() {
		return $this->roofTapestry;
	}
	
	/**
	 * @return string
	 */
	public function getTrunkCap() {
		return $this->trunkCap;
	}
	
	/**
	 * @return string
	 */
	public function getDoorLining() {
		return $this->doorLining;
	}
	
	/**
	 * @return string
	 */
	public function getAirConditioning() {
		return $this->airConditioning;
	}
	
	/**
	 * @return string
	 */
	public function getAlarm() {
		return $this->alarm;
	}
	
	/**
	 * @return string
	 */
	public function getSteeringHydraulic() {
		return $this->steeringHydraulic;
	}
	
	/**
	 * @return string
	 */
	public function getDeviceSound() {
		return $this->deviceSound;
	}
	
	/**
	 * @return string
	 */
	public function getElectricGlass() {
		return $this->electricGlass;
	}
	
	/**
	 * @return string
	 */
	public function getElectricLock() {
		return $this->electricLock;
	}
	
	/**
	 * @return string
	 */
	public function getCarpet() {
		return $this->carpet;
	}
	
	/**
	 * @return string
	 */
	public function getWheelIron() {
		return $this->wheelIron;
	}
	
	/**
	 * @return string
	 */
	public function getWheelAlloy() {
		return $this->wheelAlloy;
	}
	
	/**
	 * @return string
	 */
	public function getLanternFog() {
		return $this->lanternFog;
	}
	
	/**
	 * @return string
	 */
	public function getCarburetor() {
		return $this->carburetor;
	}
	
	/**
	 * @return string
	 */
	public function getExchange() {
		return $this->exchange;
	}
	
	/**
	 * @return string
	 */
	public function getDifferential() {
		return $this->differential;
	}
	
	/**
	 * @return string
	 */
	public function getEngine() {
		return $this->engine;
	}
	
	/**
	 * @return string
	 */
	public function getRadiator() {
		return $this->radiator;
	}
	
	/**
	 * @return string
	 */
	public function getTurbine() {
		return $this->turbine;
	}
	
	/**
	 * @return string
	 */
	public function getSuspension() {
		return $this->suspension;
	}
	
	/**
	 * @return string
	 */
	public function getInjectionPump() {
		return $this->injectionPump;
	}
	
	/**
	 * @return string
	 */
	public function getInjectionNozzle() {
		return $this->injectionNozzle;
	}
	
	/**
	 * @return string
	 */
	public function getInjectionElectronic() {
		return $this->injectionElectronic;
	}
	
	/**
	 * @return string
	 */
	public function getGasolineBump() {
		return $this->gasolineBump;
	}
	
	/**
	 * @return string
	 */
	public function getEngineStarter() {
		return $this->engineStarter;
	}
	
	/**
	 * @return string
	 */
	public function getIgnitionModule() {
		return $this->ignitionModule;
	}
	
	/**
	 * @return string
	 */
	public function getAlternator() {
		return $this->alternator;
	}
	
	/**
	 * @return string
	 */
	public function getDistributor() {
		return $this->distributor;
	}
	
	/**
	 * @return string
	 */
	public function getBattery() {
		return $this->battery;
	}
	
	/**
	 * @return string
	 */
	public function getSafetyBelts() {
		return $this->safetyBelts;
	}
	
	/**
	 * @return string
	 */
	public function getAirbag() {
		return $this->airbag;
	}
	
	/**
	 * @return string
	 */
	public function getRearviewInternal() {
		return $this->rearviewInternal;
	}
	
	/**
	 * @return string
	 */
	public function getRearviewLeft() {
		return $this->rearviewLeft;
	}
	
	/**
	 * @return string
	 */
	public function getRearviewRight() {
		return $this->rearviewRight;
	}
	
	/**
	 * @return string
	 */
	public function getSafetyTriangle() {
		return $this->safetyTriangle;
	}
	
	/**
	 * @return string
	 */
	public function getMonkey() {
		return $this->monkey;
	}
	
	/**
	 * @return string
	 */
	public function getWheelWrench() {
		return $this->wheelWrench;
	}
	
	/**
	 * @return string
	 */
	public function getWheelSpare() {
		return $this->wheelSpare;
	}
	
	/**
	 * @return string
	 */
	public function getNote() {
		return $this->note;
	}
	
	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setLanternFrontLeft($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->lanternFrontLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setLanternFrontRight($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->lanternFrontRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setLanternBackLeft($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->lanternBackLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setLanternBackRight($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->lanternBackRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setLanternArrowFrontLeft($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->lanternArrowFrontLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setLanternArrowFrontRight($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->lanternArrowFrontRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setLanternArrowBackLeft($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->lanternArrowBackLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setLanternArrowBackRight($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->lanternArrowBackRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setBumperFront($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->bumperFront = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setBumperBack($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->bumperBack = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setCoverFront($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->coverFront = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setCoverBack($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->coverBack = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWindshieldFront($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->windshieldFront = $status;
	}
	
	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWindshieldBack($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->windshieldBack = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setRoof($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->roof = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setExhaust($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->exhaust = $status;
	}
	
	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWindowFrontLeft($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->windowFrontLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWindowFrontRight($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->windowFrontRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWindowBackLeft($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->windowBackLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWindowBackRight($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->windowBackRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setDoorFrontLeft($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->doorFrontLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setDoorFrontRight($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->doorFrontRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setDoorBackLeft($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->doorBackLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setDoorBackRight($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->doorBackRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setColumnFrontLeft($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->columnFrontLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setColumnFrontRight($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->columnFrontRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setColumnBackLeft($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->columnBackLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setColumnBackRight($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->columnBackRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setStirrupFrontLeft($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->stirrupFrontLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setStirrupFrontRight($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->stirrupFrontRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setStirrupBackLeft($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->stirrupBackLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setStirrupBackRight($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->stirrupBackRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setFenderFrontLeft($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->fenderFrontLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setFenderFrontRight($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->fenderFrontRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setFenderBackLeft($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->fenderBackLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setFenderBackRight($status) {
		if (! self::isStatusAllowed($status, true)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->fenderBackRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setTireFrontLeft($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->tireFrontLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setTireFrontRight($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->tireFrontRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setTireBackLeft($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->tireBackLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setTireBackRight($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->tireBackRight = $status;
	}
	
	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setSeatDriver($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->seatDriver = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setSeatPassenger($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->seatPassenger = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setSeatRear($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->seatRear = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setDashboard($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->dashboard = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setSteeringWheel($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->steeringWheel = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setHorn($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->horn = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setCentralConsole($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->centralConsole = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setRoofTapestry($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->roofTapestry = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setTrunkCap($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->trunkCap = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setDoorLining($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->doorLining = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setAirConditioning($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->airConditioning = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setAlarm($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->alarm = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setSteeringHydraulic($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->steeringHydraulic = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setDeviceSound($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->deviceSound = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setElectricGlass($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->electricGlass = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setElectricLock($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->electricLock = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setCarpet($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->carpet = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWheelIron($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->wheelIron = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWheelAlloy($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->wheelAlloy = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setLanternFog($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->lanternFog = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setCarburetor($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->carburetor = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setExchange($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->exchange = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setDifferential($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->differential = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setEngine($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->engine = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setRadiator($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->radiator = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setTurbine($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->turbine = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setSuspension($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->suspension = $status;
	}
	
	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setInjectionPump($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->injectionPump = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setInjectionNozzle($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->injectionNozzle = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setInjectionElectronic($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->injectionElectronic = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setGasolineBump($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->gasolineBump = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setEngineStarter($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->engineStarter = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setIgnitionModule($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->ignitionModule = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setAlternator($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->alternator = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setDistributor($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->distributor = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setBattery($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->battery = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setSafetyBelts($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->safetyBelts = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setAirbag($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->airbag = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setRearviewInternal($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->rearviewInternal = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setRearviewLeft($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->rearviewLeft = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setRearviewRight($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->rearviewRight = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setSafetyTriangle($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->safetyTriangle = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setMonkey($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->monkey = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWheelWrench($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->wheelWrench = $status;
	}

	/**
	 * @param string $status
	 * @throws \InvalidArgumentException
	 */
	public function setWheelSpare($status) {
		if (! self::isStatusAllowed($status)) {
			throw new \InvalidArgumentException(__METHOD__ . ' status value [' . $status . '] is not allowed.');
		}
		$this->wheelSpare = $status;
	}

	/**
	 * @param string $note
	 */
	public function setNote($note) {
		$this->note = $note;
	}

	/**
	 * @return string[]
	 */
	public static function getStatusAllowed($isBodyPart = false) {
		if ( $isBodyPart ) {
			return [
				self::UNVALUED => 'Não Avaliado',
				self::SCRATCHED => 'Riscado',
				self::KNEADED => 'Amassado',
				self::BROKEN => 'Quebrado',
				self::MISSING => 'Faltante',
				self::GOOD => 'Bom'
			];
		}
		return [
			self::UNVALUED => 'Não Avaliado',
			self::BROKEN => 'Quebrado',
			self::MISSING => 'Faltante',
			self::GOOD => 'Bom'
		];
	}
	
	/**
	 * @param string $status
	 * @param boolean $isBodyPart
	 * @return boolean
	 */
	public static function isStatusAllowed($status, $isBodyPart = false) {
		return array_key_exists($status, self::getStatusAllowed($isBodyPart));
	}
}
?>