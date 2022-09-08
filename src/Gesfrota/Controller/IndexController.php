<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\Expr\Join;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\Fleet;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Request;
use Gesfrota\Model\Domain\RequestFreight;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\Notice;
use Gesfrota\Services\Log;
use Gesfrota\View\Layout;
use Gesfrota\Model\Domain\ImportTransactionFuel;
use Gesfrota\Model\Domain\ImportTransactionFix;
use Gesfrota\Model\Domain\ImportTransactionItem;

class IndexController extends AbstractController {
	
	public function indexAction() {
		$layout = new Layout('index/index.phtml');
		
		$tabActive = $this->request->getQuery('tab-active') ? $this->request->getQuery('tab-active') : 'fleet';
		
		if ($this->request->isPost()) {
			$data = $this->request->getPost();
			if (! empty($data['initial'])) {
				$initial = new \DateTime('first day of ' . \DateTime::createFromFormat('m/Y', $data['initial'])->format('M Y'));
				$initial->setTime(00, 00, 00);
			}
			if (! empty($data['final'])) {
				$final = new \DateTime('last day of ' . \DateTime::createFromFormat('m/Y', $data['final'])->format('M Y'));
				$final->setTime(23, 59, 59);
			}
			$tabActive = $data['tab-active'];
		} elseif ( $this->session->period ) {
	        $initial = $this->session->period[0];
	        $final = $this->session->period[1];
	    } else {
	        $initial = new \DateTime('first day of Jan ' .date('Y') .' today');
	        $final = new \DateTime("now");
		}
		$agency = $this->getAgencyActive()->isGovernment() ? null : $this->getAgencyActive();
		$this->session->period = [$initial, $final];
		$this->session->tab_active = $tabActive;
		$layout->initial = $initial;
		$layout->final = $final;
		$layout->tab_active = $tabActive;
		$layout->notice = $this->getLastNotification();
		$layout->isDashboardFleetManager = (bool) $agency;
		
		switch ($tabActive) {
		    case 'request':
		        if ( ! $agency ) {
		            $layout->request_x_driver = $this->getRequestsPerDriver(clone $initial, clone $final, $agency);
		        } else {
		            $layout->request_per_agency = $this->getRequestPerAgency(clone $initial, clone $final);
		        }
		        $layout->KPIs = $this->getRequestKPIs(clone $initial, clone $final, $agency);
		        $layout->request_x_distance = $this->getRequestsXDistance(clone $initial, clone $final, $agency);
		        $layout->request_trips_x_freight = $this->getRequestTripsXFreight(clone $initial, clone $final, $agency);
		        break;
		        
		    case 'fuel':
		        if ( $agency == null) {
		            $layout->fuel_per_agency = $this->getFuelPerAgency(clone $initial, clone $final);
		        }
		        $layout->KPIs = $this->getFuelKPIs(clone $initial, clone $final, $agency);
		        $layout->fuel_x_distance = $this->getFuelXDistance(clone $initial, clone $final, $agency);
		        $layout->fuel_outlier = $this->getFuelOutlier(clone $initial, clone $final, $agency);
		        $layout->fuel_per_type = $this->getFuelPerType(clone $initial, clone $final, $agency);
		        break;
		        
		    case 'fix':
		        if ( $agency == null) {
		            $layout->fix_per_agency = $this->getFixPerAgency(clone $initial, clone $final);
		        }
		        $layout->KPIs= $this->getFixKPIs(clone $initial, clone $final, $agency);
		        $layout->fix_x_vehicles = $this->getFixXVehicles(clone $initial, clone $final, $agency);
		        $layout->fix_parts_x_labor = $this->getFixPartsXLabor(clone $initial, clone $final, $agency);
		        $layout->fix_per_type = $this->getFixPerType(clone $initial, clone $final, $agency);
		        $layout->fix_per_supplier_type = $this->getFixPerSupplierType(clone $initial, clone $final, $agency);
		        break;
		        
		    case 'fleet':
		    default:
		        if ( $agency == null) {
		            $layout->fleet_per_agency = $this->getFleetPerAgency();
		            $layout->fleet_current_x_expected = $this->getFleetCurrentXExpected(clone $initial, clone $final);
		        }
		        $layout->fleet_per_type = $this->getFleetPerType($agency);
		        $layout->fleet_per_family = $this->getFleetPerFamily($agency);
		        $layout->fleet_per_age = $this->getFleetPerAge($agency);
		        $layout->fleet_vehicle_x_equipament = $this->getFleetVehicleXEquipament($agency);
		        break;
		}
		
		return $layout;
	}
	
	/**
	 * @return Notice
	 */
	private function getLastNotification() {
	    
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('u');
	    $builder->from(Notice::getClass(), 'u');
	    $builder->where('u.active = true AND u.id != :about');
	    $builder->setParameter('about', Notice::ABOUT);
	    $builder->addOrderBy('u.id', 'desc');
	    $builder->setMaxResults(1);
	    $result = $builder->getQuery()->getResult();
	    
	    $notice = array_shift($result);
	    if ($notice instanceof Notice && ! $notice->isReadBy($this->getUserActive())) {
	       return $notice;
	    }
	    return null;
	}
	
	private function getActivitiesRecent(Agency $agency) {
		
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('u');
		$builder->from(Log::class, 'u');
		$builder->where('u.created BETWEEN :initial AND :final');
		$builder->andWhere('((u.referer LIKE :r1 AND u.className IN (:o1)) OR (u.referer LIKE :r2 AND u.className IN (:o2)) OR (u.referer LIKE :r3 AND u.className = :o3) OR (u.referer LIKE :r4 AND u.className = :o4))');
		$builder->setParameter('initial', new \DateTime('now today'));
		$builder->setParameter('final', new \DateTime('now 23:59:59'));
		$builder->setParameter('r1', '/fleet/%');
		$builder->setParameter('o1', [Vehicle::getClass(), Equipment::getClass()]);
		$builder->setParameter('r2', '/request/%');
		$builder->setParameter('o2', [RequestTrip::getClass(), RequestFreight::getClass()]);
		$builder->setParameter('r3', '/requester/%');
		$builder->setParameter('o3', Requester::getClass());
		$builder->setParameter('r4', '/driver/%');
		$builder->setParameter('o4', Driver::getClass());
		$builder->setMaxResults(7);
		$builder->orderBy('u.created', 'desc');
		
		$builder->andWhere('u.agency = :agency');
		$builder->setParameter('agency', $agency->getId());
		
		$result = $builder->getQuery()->getResult();
		return $result;
	}
	
	private function getRequestKPIs(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->addSelect('COUNT(u.id) AS HIDDEN total');
	    $builder->from(Request::getClass(), 'u');
	    $builder->join('u.requesterUnit', 'a');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(u1.id)');
	    $builder1->from(Request::getClass(), 'u1');
	    $builder1->join('u1.requesterUnit', 'a1');
	    $builder1->where('a1.agency = a.agency AND u1.openedAt BETWEEN :initial AND :final AND u1.status != :canceled');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS request_total');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(u2.id)');
	    $builder1->from(Request::getClass(), 'u2');
	    $builder1->join('u2.requesterUnit', 'a2');
	    $builder1->where('a2.agency = a.agency AND u2.openedAt BETWEEN :initial AND :final AND u2.status IN (:status)');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS request_finished');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(u3.odometerFinal-u3.odometerInitial)');
	    $builder1->from(Request::getClass(), 'u3');
	    $builder1->join('u3.requesterUnit', 'a3');
	    $builder1->where('a3.agency = a.agency AND u3.openedAt BETWEEN :initial AND :final AND u3.status IN (:status)');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS request_distance');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(u4.id)');
	    $builder1->from(Request::getClass(), 'u4');
	    $builder1->join('u4.requesterUnit', 'a4');
	    $builder1->where('a4.agency = a.agency AND u4.openedAt BETWEEN :initial AND :final AND u4.status = :declined');
	    
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS request_declined');
	    
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    $builder->setParameter('canceled', Request::CANCELED);
	    $builder->setParameter('declined', Request::DECLINED);
	    $builder->setParameter('status', [Request::CONFIRMED, Request::INITIATED, Request::FINISHED]);
	    if ( $agency ) {
	        $builder->where('a.agency = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    
	    $KPI = $builder->getQuery()->getSingleResult();
	    
	    $total = $KPI['request_declined'] + $KPI['request_finished'];
	    
	    $KPI['request_availability'] = 1 - ($total > 0 ? $KPI['request_declined']/$total : 0);
	    
	    return $KPI;
	}
	
	private function getRequestTripsXFreight(\DateTime $initial, \DateTime $final, Agency $agency = null) {
		
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('COUNT(u.id) AS score');
		$builder->from(RequestTrip::getClass(), 'u');
		$builder->where('u.openedAt BETWEEN :initial AND :final AND u.status = :status');
		$builder->setParameter('initial',  $initial);
		$builder->setParameter('final', $final);
		$builder->setParameter('status', Request::FINISHED);
		
		if ( $agency ) {
			$builder->join('u.requesterUnit', 'a', Join::WITH, 'a.agency = :agency');
			$builder->setParameter('agency', $agency->getId());
		}
		
		$result[RequestTrip::REQUEST_TYPE] = $builder->getQuery()->getSingleResult()['score'];
		
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('COUNT(u.id) AS score');
		$builder->from(RequestFreight::getClass(), 'u');
		$builder->where('u.openedAt BETWEEN :initial AND :final AND u.status = :status');
		$builder->setParameter('initial',  $initial);
		$builder->setParameter('final', $final);
		$builder->setParameter('status', Request::FINISHED);
		
		if ( $agency ) {
			$builder->join('u.requesterUnit', 'a', Join::WITH, 'a.agency = :agency');
			$builder->setParameter('agency', $agency->getId());
		}
		
		$result[RequestFreight::REQUEST_TYPE] = $builder->getQuery()->getSingleResult()['score'];
		return $result;
	}
	
	private function getRequestsXDistance(\DateTime $initial, \DateTime $final, Agency $agency = null) {
		$sql = 'SELECT CONCAT(YEAR(r0_.opened_at), MONTH(r0_.opened_at)) AS opened, COUNT(r0_.id) AS request, SUM(r0_.odometer_final - r0_.odometer_initial) AS distance ';
		$sql.= 'FROM requests r0_ ';
		if ($agency) {
			$sql.= 'INNER JOIN administrative_units a1_ ON r0_.requester_unit_id = a1_.id AND a1_.agency_id = ' . $agency->getId() . ' ';
		}
		$sql.= 'WHERE (r0_.opened_at BETWEEN ? AND ? AND r0_.status = ?) ';
		$sql.= 'GROUP BY opened';
		
		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('opened', 'opened');
		$rsm->addScalarResult('request', 'request');
		$rsm->addScalarResult('distance', 'distance');
		
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		$query->setParameter(1, $initial->format('Y-m-d H:i:s'));
		$query->setParameter(2, $final->format('Y-m-d H:i:s'));
		$query->setParameter(3, Request::FINISHED);
		
		$data = [];
		$result = $query->getResult();
		
		if ($initial->format('Y') == $final->format('Y')) {
			while($initial < $final) {
				$data['label'][] = ucfirst(strftime('%b', $initial->getTimestamp()));
				$data['request'][$initial->format('Yn')] = 0;
				$data['distance'][$initial->format('Yn')] = 0;
				$initial->add(new \DateInterval('P1M'));
			}
		} else {
			while($initial < $final) {
				$data['label'][] = ucfirst(strftime('%b/%y', $initial->getTimestamp()));
				$data['request'][$initial->format('Yn')] = 0;
				$data['distance'][$initial->format('Yn')] = 0;
				$initial->add(new \DateInterval('P1M'));
			}
		}
		foreach ($result as $item ) {
			$data['request'][$item['opened']] = $item['request'];
			$data['distance'][$item['opened']] = $item['distance'];
		}
		return $data;
	}
	
	
	private function getRequestsPerDriver(\DateTime $initial, \DateTime $final, Agency $agency = null) {
		
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('d.name AS driver, COUNT(u.id) AS request', 'SUM(u.odometerFinal-u.odometerInitial) AS distance');
		$builder->from(Request::getClass(), 'u');
		$builder->join('u.driverLicense', 'l');
		$builder->join('l.user', 'd');
		$builder->where('u.openedAt BETWEEN :initial AND :final AND u.status = :status');
		$builder->setParameter('initial', $initial);
		$builder->setParameter('final', $final);
		$builder->setParameter('status', Request::FINISHED);
		$builder->groupBy('l.user');
		$builder->setMaxResults(3);
		$builder->orderBy('request', 'desc');
		$builder->addOrderBy('distance', 'desc');
		
		if ( $agency ) {
			$builder->join('u.requesterUnit', 'a', Join::WITH, 'a.agency = :agency');
			$builder->setParameter('agency', $agency->getId());
		}
		
		$result = $builder->getQuery()->getResult();
		return $result;
	}
	
	private function getRequestPerAgency(\DateTime $initial, \DateTime $final) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('a.id', 'a.acronym AS label');
	    $builder->from(Agency::getClass(), 'a');
	    $builder->where('a.id > 0');
	    $builder->groupBy('a.id');
	    $builder->addOrderBy('total', 'desc');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(r_1.id)');
	    $builder1->from(RequestTrip::getClass(), 'r_1');
	    $builder1->join('r_1.requesterUnit', 'r_un1');
	    $builder1->join('r_un1.agency', 'r_a1');
	    $builder1->andWhere('r_1.id = a.id AND r_1.openedAt BETWEEN :initial AND :final AND r_1.status = '. Request::FINISHED);
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS request_trip');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(r_2.id)');
	    $builder1->from(RequestFreight::getClass(), 'r_2');
	    $builder1->join('r_2.requesterUnit', 'r_un2');
	    $builder1->join('r_un2.agency', 'r_a2');
	    $builder1->andWhere('r_a2.id = a.id AND r_2.openedAt BETWEEN :initial AND :final AND r_2.status = '. Request::FINISHED);
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS request_freight');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(r_.id)');
	    $builder1->from(Request::getClass(), 'r_');
	    $builder1->join('r_.requesterUnit', 'r_un');
	    $builder1->join('r_un.agency', 'r_a');
	    $builder1->andWhere('r_a.id = a.id AND r_.openedAt BETWEEN :initial AND :final AND r_.status = '. Request::FINISHED);
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS HIDDEN total');
	    
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    
	    $result = $builder->getQuery()->getResult();
	    $data = [];
	    foreach ($result as $item) {
	        $data[RequestTrip::REQUEST_TYPE][] = ['x' => $item['label'], 'y' => $item['request_trip']];
	        $data[RequestFreight::REQUEST_TYPE][] = ['x' => $item['label'], 'y' => $item['request_freight']];
	    }
	    return $data;
	    
	}
	
	private function getFleetVehicleXEquipament(Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('COUNT(u.id) AS score');
	    $builder->from(Vehicle::getClass(), 'u');
	    $builder->where('u.active = true');
	    
	    if ( $agency ) {
	        $builder->andWhere('u.responsibleUnit = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    $data[Vehicle::FLEET_TYPE] = (int) $builder->getQuery()->getSingleScalarResult();
	    
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('COUNT(u.id) AS score');
	    $builder->from(Equipment::getClass(), 'u');
	    $builder->where('u.active = true');
	    
	    if ( $agency ) {
	        $builder->andWhere('u.responsibleUnit = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    $data[Equipment::FLEET_TYPE] = (int) $builder->getQuery()->getSingleScalarResult();
	    
	    return $data;
	}
	
	private function getFleetPerType(Agency $agency = null) {
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('u.fleet, COUNT(u.id) AS score');
		$builder->from(FleetItem::getClass(), 'u');
		$builder->where('u.active = true');
		$builder->groupBy('u.fleet');
		$builder->orderBy('score', 'desc');
		
		if ( $agency ) {
			$builder->andWhere('u.responsibleUnit = :agency');
			$builder->setParameter('agency', $agency->getId());
		}
		$result = $builder->getQuery()->getResult();
		
		$data = [];
		foreach ($result as $item) {
			$data[FleetItem::getFleetAllowed()[$item['fleet']]] = $item['score'];
		}
		return $data;
	}
	
	
	private function getFleetPerFamily(Agency $agency = null) {
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('f.id, f.name AS label, COUNT(u.id) AS score');
		$builder->from(Vehicle::getClass(), 'u');
		$builder->join('u.model', 'v');
		$builder->join('v.family', 'f');
		$builder->where('u.active = true');
		$builder->groupBy('f.id');
		$builder->orderBy('score', 'desc');
		
		if ( $agency ) {
			$builder->andWhere('u.responsibleUnit = :agency');
			$builder->setParameter('agency', $agency->getId());
		}
		$result = $builder->getQuery()->getResult();
		$data = [];
		foreach ($result as $item) {
			$data[$item['label']] = $item['score'];
		}
		return $data;
	}
	
	private function getFleetPerAge(Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('u.yearManufacture AS label, COUNT(u.id) AS score');
	    $builder->from(Vehicle::getClass(), 'u');
	    $builder->join('u.model', 'v');
	    $builder->join('v.family', 'f');
	    $builder->where('u.active = true');
	    $builder->groupBy('u.yearManufacture');
	    $builder->orderBy('label', 'desc');
	    
	    if ( $agency ) {
	        $builder->andWhere('u.responsibleUnit = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    $result = $builder->getQuery()->getResult();
	    $data = [];
	    foreach ($result as $item) {
	        $data[$item['label']] = $item['score'];
	    }
	    return $data;
	}
	
	private function getFleetPerAgency() {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('a.id', 'a.acronym AS label');
	    $builder->from(Agency::getClass(), 'a');
	    $builder->where('a.id > 0');
	    $builder->groupBy('a.id');
	    $builder->addOrderBy('total', 'desc');
	    
	    $fleet = [0 => 'Inativa'] + FleetItem::getFleetAllowed();
	    foreach ($fleet as $key => $type) {
	        $builder1 = $this->getEntityManager()->createQueryBuilder();
	        $builder1->select('COUNT(fl_'.$key.'.id)');
	        $builder1->from(FleetItem::getClass(), 'fl_'.$key);
	        $builder1->where('fl_'.$key.'.responsibleUnit = a.id AND ' . ( $key > 0 ? 'fl_'.$key.'.active = true AND fl_'.$key.'.fleet = '.$key : 'fl_'.$key.'.active = false'));
	        
	        $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fleet_'.$key);
	    }
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(fl_.id)');
	    $builder1->from(FleetItem::getClass(), 'fl_');
	    $builder1->where('fl_.responsibleUnit = a.id');
	    
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS HIDDEN total');
	    
	    $result = $builder->getQuery()->getResult();
	    $data = [];
	    foreach ($result as $item) {
	        foreach ($fleet as $key => $serie) {
	            $data[$serie][] = ['x' => $item['label'], 'y' => (int) $item['fleet_'.$key]];
	        }
	    }
	    return $data;
	}
	
	private function getFleetCurrentXExpected(\DateTime $initial, \DateTime $final) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('a.id', 'a.acronym AS label');
	    $builder->from(Agency::getClass(), 'a');
	    $builder->where('a.id > 0');
	    $builder->groupBy('a.id');
	    $builder->addOrderBy('fleet_current', 'desc');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(fl_c.id)');
	    $builder1->from(FleetItem::getClass(), 'fl_c');
	    $builder1->where('fl_c.responsibleUnit = a.id');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fleet_current');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(DISTINCT fl_e.vehiclePlate)');
	    $builder1->from(ImportTransactionItem::class, 'fl_e');
	    $builder1->join('fl_e.transactionImport', 'fl_e_i');
	    $builder1->where('fl_e.transactionDate BETWEEN :initial AND :final AND fl_e.transactionAgency = a.id AND fl_e_i.finished = true');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fleet_expected');
	    
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    
	    $result = $builder->getQuery()->getResult();
	    $data = [];
	    foreach ($result as $item) {
	        $data[] = ['x' => $item['label'],
	            'y' => (int) $item['fleet_current'],
	            'goals' => [[
	                'name' => 'Expectativa',
	                'value'=> (int) $item['fleet_expected'],
	                'strokeHeight' => 2,
	                'strokeDashArray' => 2,
	                'strokeColor' => '#775DD0'
	            ]]
	        ];
	    }
	    return $data;
	}
	
	private function getFuelKPIs(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->addSelect('COUNT(u.transactionId) AS HIDDEN total');
	    $builder->from(ImportTransactionFuel::class, 'u');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(u1.itemTotal)');
	    $builder1->from(ImportTransactionFuel::class, 'u1');
	    $builder1->join('u1.transactionImport', 'i1');
	    $builder1->where('u1.transactionDate BETWEEN :initial AND :final AND i1.finished = true');
	    if ( $agency ) {
	        $builder1->andWhere('u1.transactionAgency = :agency');
	    }
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fuel_total');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(DISTINCT u2.vehiclePlate)');
	    $builder1->from(ImportTransactionFuel::class, 'u2');
	    $builder1->join('u2.transactionImport', 'i2');
	    $builder1->where('u2.transactionDate BETWEEN :initial AND :final AND i2.finished = true');
	    if ( $agency ) {
	        $builder1->andWhere('u2.transactionAgency = :agency');
	    }
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fuel_amount');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(u3.vehicleDistance)');
	    $builder1->from(ImportTransactionFuel::class, 'u3');
	    $builder1->join('u3.transactionImport', 'i3');
	    $builder1->where('u3.vehicleEfficiency BETWEEN 0 AND 20 AND u3.transactionDate BETWEEN :initial AND :final AND i3.finished = true');
	    if ( $agency ) {
	        $builder1->andWhere('u3.transactionAgency = :agency');
	    }
	    
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fuel_distance');
	    
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    if ( $agency ) {
	        $builder->setParameter('agency', $agency->getId());
	    }
	    
	    $KPI = $builder->getQuery()->getSingleResult();
	    
        $sql = 'SELECT COUNT(DISTINCT CONCAT(YEAR(i0_.transaction_date), MONTH(i0_.transaction_date))) AS total ';
        $sql.= 'FROM import_transaction_items i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
        $sql.= 'WHERE i0_.transaction_service = \'S\' AND i0_.transaction_date BETWEEN ? AND ? AND i1_.finished = 1 '; 
        $sql.= ($agency ? 'AND i0_.transaction_agency_id = ' . $agency->getId() . ' ' : '');
        
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total');
        
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $initial->format('Y-m-d H:i:s'));
        $query->setParameter(2, $final->format('Y-m-d H:i:s'));
        
        $amountMonth = $query->getSingleScalarResult();
        
        $KPI['fuel_avg'] = $amountMonth > 0 ? $KPI['fuel_total'] / $amountMonth : $KPI['fuel_total'];
	    
	    return $KPI;
	}
	
	private function getFuelXDistance(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT COUNT(DISTINCT i0_.vehicle_plate) AS vehicles, SUM(i0_.item_total) AS fuel, SUM(i0_.vehicle_distance) AS distance, CONCAT(YEAR(i0_.transaction_date), MONTH(i0_.transaction_date)) AS period ';
	    $sql.= 'FROM import_transaction_items i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
	    $sql.= 'WHERE i0_.transaction_service = \'S\' AND i0_.vehicle_efficiency BETWEEN 0 AND 20 AND i0_.transaction_date BETWEEN ? AND ? AND i1_.finished = 1 ' . ($agency ? 'AND i0_.transaction_agency_id = ' . $agency->getId() . ' ' : '');
	    $sql.= 'GROUP BY period';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('period', 'period');
	    $rsm->addScalarResult('fuel', 'fuel');
	    $rsm->addScalarResult('vehicles', 'vehicles');
	    $rsm->addScalarResult('distance', 'distance');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter(1, $initial->format('Y-m-d H:i:s'));
	    $query->setParameter(2, $final->format('Y-m-d H:i:s'));
	    
	    $data = [];
	    $result = $query->getResult();
	    
	    if ($initial->format('Y') == $final->format('Y')) {
	        while($initial < $final) {
	            $data['label'][] = ucfirst(strftime('%b', $initial->getTimestamp()));
	            $data['fuel'][$initial->format('Yn')] = null;
	            $data['distance'][$initial->format('Yn')] = null;
	            $data['vehicles'][$initial->format('Yn')] = null;
	            $initial->add(new \DateInterval('P1M'));
	        }
	    } else {
	        while($initial < $final) {
	            $data['label'][] = ucfirst(strftime('%b/%y', $initial->getTimestamp()));
	            $data['fuel'][$initial->format('Yn')] = null;
	            $data['distance'][$initial->format('Yn')] = null;
	            $data['vehicles'][$initial->format('Yn')] = null;
	            $initial->add(new \DateInterval('P1M'));
	        }
	    }
	    foreach ($result as $item ) {
	        $data['fuel'][$item['period']] = $item['fuel'];
	        $data['distance'][$item['period']] = $item['distance'];
	        $data['vehicles'][$item['period']] = $item['vehicles'];
	    }
	    return $data;
	}
	
	
	private function getFuelOutlier(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $initial = $initial->format('Y-m-d H:i:s');
	    $final = $final->format('Y-m-d H:i:s');
	    $sql = 'SELECT LEFT(i0_.item_description, 1) AS fuel, ROUND(i0_.vehicle_efficiency) AS efficiency, COUNT(i0_.transaction_id) AS score ';
	    $sql.= 'FROM import_transaction_items i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
	    $sql.= 'WHERE LEFT(i0_.item_description, 1) != \'A\' AND i0_.transaction_service = \'S\' AND i0_.vehicle_efficiency between 0 AND 20 ';
	    $sql.= 'AND i0_.transaction_date BETWEEN \'' . $initial . '\'  AND \'' . $final . '\' AND i1_.finished = 1 ';
	    $sql.= ($agency ? ' AND i0_.transaction_agency_id = ' . $agency->getId()  : '') . ' ';
	    $sql.= 'GROUP BY fuel, efficiency';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('fuel', 'fuel');
	    $rsm->addScalarResult('efficiency', 'x');
	    $rsm->addScalarResult('score', 'y');
	    
	    $result1 = $this->getEntityManager()->createNativeQuery($sql, $rsm)->getArrayResult();
	    
	    $sql1 = 'SELECT LEFT(i0_.item_description, 1) AS fuel, i0_.vehicle_efficiency AS efficiency ';
	    $sql1.= 'FROM import_transaction_items i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
	    $sql1.= 'WHERE LEFT(i0_.item_description, 1) != \'A\'  AND i0_.vehicle_efficiency between 0 AND 20 ';
	    $sql1.= 'AND i0_.transaction_date BETWEEN \'' . $initial . '\'  AND \'' . $final . '\' AND i1_.finished = 1 ';
	    $sql1.= 'AND i0_.transaction_service = \'S\'';
	    $sql1.= ($agency ? ' AND i0_.transaction_agency_id = ' . $agency->getId()  : '') . ' ';
	    
	    $sql2 = 'SELECT t1.fuel, COUNT(efficiency) AS total, 
                        ROUND(STD(t1.efficiency)) AS std, 
                        ROUND(AVG(t1.efficiency)) AS avg, 
                        (ROUND(AVG(t1.efficiency))-ROUND(STD(t1.efficiency))) AS min, 
                        (ROUND(AVG(t1.efficiency))+ROUND(STD(t1.efficiency))) AS max
                        FROM (' . $sql1 . ') t1 GROUP BY t1.fuel';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('fuel', 'fuel');
	    $rsm->addScalarResult('total', 'total');
	    $rsm->addScalarResult('std', 'std');
	    $rsm->addScalarResult('avg', 'avg');
	    $rsm->addScalarResult('min', 'min');
	    $rsm->addScalarResult('max', 'max');
	    $result2 = $this->getEntityManager()->createNativeQuery($sql2, $rsm)->getArrayResult();
	    
	    $data = []; 
	    foreach ($result2 as $resume ) {
	        $data[$resume['fuel']]['total']  = (int) $resume['total'];
	        $data[$resume['fuel']]['std']    = (int) $resume['std'];
	        $data[$resume['fuel']]['avg']    = (int) $resume['avg'];
	        $data[$resume['fuel']]['min']    = (int) $resume['min'];
	        $data[$resume['fuel']]['max']    = (int) $resume['max'];
	        $data[$resume['fuel']]['quartil']= 0;
	        $data[$resume['fuel']]['percent']= 0;
	    }
	    foreach($result1 as $item) {
	        $data[$item['fuel']]['data'][] = ['x' => $item['x'], 'y' => $item['y']];
	        if ($item['x'] >= $data[$item['fuel']]['min'] && $item['x'] <= $data[$item['fuel']]['max'] ) {
	            $data[$item['fuel']]['quartil']+= $item['y'];
	            $data[$item['fuel']]['percent'] =  $data[$item['fuel']]['quartil']/$data[$item['fuel']]['total']*100;
	        }
	    }
	    return $data;
	}
		private function getFuelPerType(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('SUM(u.itemTotal) AS finance, SUM(u.itemQuantity) AS consume, SUBSTRING(u.itemDescription, 1, 1) AS label');
	    $builder->from(ImportTransactionFuel::class, 'u');
	    $builder->join('u.transactionImport', 'i');
	    $builder->where('u.transactionDate BETWEEN :initial AND :final AND i.finished = true');
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    $builder->groupBy('label');
	    
	    if ( $agency ) {
	        $builder->andWhere('u.transactionAgency = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    
	    $result = $builder->getQuery()->getResult();
	    $fuel = ['A' => 'Arla-32', 'D' => 'Diesel', 'E' => 'Etanol', 'G' => 'Gasolina'];
	    $data = [];
	    foreach ($result as $item) {
	        $data['finance'][$fuel[$item['label']]] = (float) $item['finance'];
	        $data['consume'][$fuel[$item['label']]] = (float) $item['consume'];
	    }
	    return $data;
	}
	
	private function getFuelPerAgency(\DateTime $initial, \DateTime $final) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('a.id', 'a.acronym AS label');
	    $builder->from(Agency::getClass(), 'a');
	    $builder->where('a.id > 0');
	    $builder->groupBy('a.id');
	    $builder->addOrderBy('total', 'desc');
	    
	    $fuel = ['A' => 'Arla-32', 'D' => 'Diesel', 'E' => 'Etanol', 'G' => 'Gasolina'];
	    foreach ($fuel as $key => $type) {
	        $builder1 = $this->getEntityManager()->createQueryBuilder();
	        $builder1->select('SUM(fu_'.$key.'.itemTotal)');
	        $builder1->from(ImportTransactionFuel::class, 'fu_'.$key);
	        $builder1->join('fu_'.$key.'.transactionImport', 'fu_i'.$key);
	        $builder1->where('fu_'.$key.'.transactionAgency = a.id AND fu_'.$key.'.transactionDate BETWEEN :initial AND :final AND fu_i'.$key.'.finished = true AND SUBSTRING(fu_'.$key.'.itemDescription, 1, 1) = \''.$key.'\'');
	        $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fuel_'.$key);
	    }
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(fu_.itemTotal)');
	    $builder1->from(ImportTransactionFuel::class, 'fu_');
	    $builder1->join('fu_.transactionImport', 'fu_i');
	    $builder1->where('fu_.transactionAgency = a.id AND fu_.transactionDate BETWEEN :initial AND :final AND fu_i.finished = true');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS HIDDEN total');
	    
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    
	    $result = $builder->getQuery()->getResult();
	    $data = [];
	    foreach ($result as $item) {
	        foreach ($fuel as $key => $serie) {
	            $data[$serie][] = ['x' => $item['label'], 'y' => (int) $item['fuel_'.$key]];
	        }
	    }
	    return $data;
	}
	
	private function getFixKPIs(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->addSelect('COUNT(u.transactionId) AS HIDDEN total');
	    $builder->from(ImportTransactionFix::class, 'u');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(u1.itemTotal)');
	    $builder1->from(ImportTransactionFix::class, 'u1');
	    $builder1->join('u1.transactionImport', 'i1');
	    $builder1->where('u1.transactionDate BETWEEN :initial AND :final AND i1.finished = true');
	    if ( $agency ) {
	        $builder1->andWhere('u1.transactionAgency = :agency');
	    }
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fix_total');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(DISTINCT u2.vehiclePlate)');
	    $builder1->from(ImportTransactionFix::class, 'u2');
	    $builder1->join('u2.transactionImport', 'i2');
	    $builder1->where('u2.transactionDate BETWEEN :initial AND :final AND i2.finished = true');
	    if ( $agency ) {
	        $builder1->andWhere('u2.transactionAgency = :agency');
	    }
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fix_amount');
	    
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    if ( $agency ) {
	        $builder->setParameter('agency', $agency->getId());
	    }
	    
	    $KPI = $builder->getQuery()->getSingleResult();
	    
	    $sql = 'SELECT COUNT(DISTINCT CONCAT(YEAR(i0_.transaction_date), MONTH(i0_.transaction_date))) AS total ';
	    $sql.= 'FROM import_transaction_items i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
	    $sql.= 'WHERE i0_.transaction_service = \'M\' AND i0_.transaction_date BETWEEN ? AND ? AND i1_.finished = 1 ';
	    $sql.= ($agency ? 'AND i0_.transaction_agency_id = ' . $agency->getId() . ' ' : '');
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('total', 'total');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter(1, $initial->format('Y-m-d H:i:s'));
	    $query->setParameter(2, $final->format('Y-m-d H:i:s'));
	    
	    $amountMonth = $query->getSingleScalarResult();
	    
	    $KPI['fix_avg'] = $amountMonth > 0 ? $KPI['fix_total'] / $amountMonth : $KPI['fix_total'];
	    
	    return $KPI;
	}
	
	private function getFixXVehicles(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT COUNT(DISTINCT i0_.vehicle_plate) AS vehicles, SUM(i0_.item_total) AS score, 
                       CONCAT(YEAR(i0_.transaction_date), MONTH(i0_.transaction_date)) AS period ';
	    $sql.= 'FROM import_transaction_items i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
	    $sql.= 'WHERE i0_.transaction_service = \'M\' AND i0_.transaction_date BETWEEN ? AND ? AND i1_.finished = 1 ';
	    $sql.= ($agency ? 'AND i0_.transaction_agency_id = ' . $agency->getId() . ' ' : '');
	    $sql.= 'GROUP BY period';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('period', 'period');
	    $rsm->addScalarResult('score', 'score');
	    $rsm->addScalarResult('vehicles', 'vehicles');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter(1, $initial->format('Y-m-d H:i:s'));
	    $query->setParameter(2, $final->format('Y-m-d H:i:s'));
	    
	    $data = [];
	    $result = $query->getResult();
	    
	    if ($initial->format('Y') == $final->format('Y')) {
	        while($initial < $final) {
	            $data['label'][] = ucfirst(strftime('%b', $initial->getTimestamp()));
	            $data['score'][$initial->format('Yn')] = null;
	            $data['vehicles'][$initial->format('Yn')] = null;
	            $initial->add(new \DateInterval('P1M'));
	        }
	    } else {
	        while($initial < $final) {
	            $data['label'][] = ucfirst(strftime('%b/%y', $initial->getTimestamp()));
	            $data['score'][$initial->format('Yn')] = null;
	            $data['vehicles'][$initial->format('Yn')] = null;
	            $initial->add(new \DateInterval('P1M'));
	        }
	    }
	    foreach ($result as $item ) {
	        $data['score'][$item['period']] = $item['score'];
	        $data['vehicles'][$item['period']] = $item['vehicles'];
	    }
	    return $data;
	}
	
	private function getFixPartsXLabor(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('u.itemType AS label, SUM(u.itemTotal) AS score');
	    $builder->from(ImportTransactionFix::class, 'u');
	    $builder->join('u.transactionImport', 'i');
	    $builder->where('u.transactionDate BETWEEN :initial AND :final AND i.finished = true');
	    $builder->groupBy('u.itemType');
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    
	    if ( $agency ) {
	        $builder->andWhere('u.transactionAgency = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    
	    $data = [];
	    $result = $builder->getQuery()->getArrayResult();
	    foreach ($result as $item) {
	        $label = 'Ambos';
	        if ($item['label']) {
	            $label = $item['label'] == ImportTransactionFix::TYPE_PRODUCT ? 'Aquisição de Peças' : 'Mão de Obra';
	        }
	        $data[$label] = (float) $item['score'];
	    }
	    return $data;
	}
	
	private function getFixPerType(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('u.transactionFixtype AS label, SUM(u.itemTotal) AS score');
	    $builder->from(ImportTransactionFix::class, 'u');
	    $builder->join('u.transactionImport', 'i');
	    $builder->where('u.transactionDate BETWEEN :initial AND :final AND i.finished = true');
	    $builder->groupBy('u.transactionFixtype');
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    
	    if ( $agency ) {
	        $builder->andWhere('u.transactionAgency = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    
	    $data = [];
	    $result = $builder->getQuery()->getArrayResult();
	    foreach ($result as $item) {
	        $data[empty($item['label']) ? 'Não Informado' : $item['label']] = (float) $item['score'];
	    }
	    return $data;
	}
	
	private function getFixPerSupplierType(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('u.supplierType AS label, SUM(u.itemTotal) AS score');
	    $builder->from(ImportTransactionFix::class, 'u');
	    $builder->join('u.transactionImport', 'i');
	    $builder->where('u.transactionDate BETWEEN :initial AND :final AND i.finished = true');
	    $builder->groupBy('u.supplierType');
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    
	    if ( $agency ) {
	        $builder->andWhere('u.transactionAgency = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    
	    $data = [];
	    $result = $builder->getQuery()->getArrayResult();
	    foreach ($result as $item) {
	        $data[empty($item['label']) ? 'NÃO INFORMADO' : $item['label']] = (float) $item['score'];
	    }
	    return $data;
	}
	
	private function getFixPerAgency(\DateTime $initial, \DateTime $final) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('a.id', 'a.acronym AS label');
	    $builder->from(Agency::getClass(), 'a');
	    $builder->where('a.id > 0');
	    $builder->groupBy('a.id');
	    $builder->addOrderBy('total', 'desc');
	    
	    $qb = $this->getEntityManager()->createQueryBuilder();
	    $qb->select('DISTINCT u.transactionFixtype AS desc');
	    $qb->from(ImportTransactionFix::class, 'u');
	    
	    $fixtype = $qb->getQuery()->getResult();
	    foreach ($fixtype as $key => $type) {
	        $builder1 = $this->getEntityManager()->createQueryBuilder();
	        $builder1->select('SUM(fi_'.$key.'.itemTotal)');
	        $builder1->from(ImportTransactionFix::class, 'fi_' . $key);
	        $builder1->join('fi_'.$key.'.transactionImport', 'fi_i' . $key);
	        $builder1->where('fi_'.$key.'.transactionAgency = a.id AND fi_'.$key.'.transactionDate BETWEEN :initial AND :final AND fi_i'.$key.'.finished = true AND fi_'.$key.'.transactionFixtype =\'' . $type['desc'] . '\'');
	        $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS fix_' . $key);
	    }
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(fi_.itemTotal)');
	    $builder1->from(ImportTransactionFix::class, 'fi_');
	    $builder1->join('fi_.transactionImport', 'fi_i');
	    $builder1->where('fi_.transactionAgency = a.id AND fi_.transactionDate BETWEEN :initial AND :final AND fi_i.finished = true');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS HIDDEN total');
	    
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    
	    $result = $builder->getQuery()->getResult();
	    $data = [];
	    foreach ($result as $item) {
	        foreach ($fixtype as $key => $type) {
	            $data[empty($type['desc']) ? 'Não Informado' : $type['desc']][] = ['x' => $item['label'], 'y' => (int) $item['fix_'.$key]];
	        }
	    }
	    return $data;
	}
	
}
?>