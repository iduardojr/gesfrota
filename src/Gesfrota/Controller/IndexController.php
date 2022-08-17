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

class IndexController extends AbstractController {
	
	public function indexAction() {
		$layout = new Layout('index/index.phtml');
		
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
			$tab = $data['tab-active'];
		} elseif ( $this->session->period ) {
	        $initial = $this->session->period[0];
	        $final = $this->session->period[1];
	        $tab = $this->session->tab_active;
	    } else {
	        $initial = new \DateTime('first day of Jan ' .date('Y') .' today');
	        $final = new \DateTime("now");
	        $tab = 'request';
		}
		$this->session->period = [$initial, $final];
		$this->session->tab_active = $tab;
		
		$layout->initial = $initial;
		$layout->final = $final;
		$layout->tab_active = $tab;
		
		$layout->isDashboardFleetManager = false;
		$agency = $this->getAgencyActive();
		if ( ! $agency->isGovernment() ) {
		    $layout->isDashboardFleetManager = true;
		    $layout->request_x_driver = $this->getRequestsPerDriver($initial, $final, $agency);
		    $layout->activities = $this->getActivitiesRecent($agency);
		} else {
		    $agency = null;
		    $layout->request_per_agency = $this->getRequestPerAgency($initial, $final);
		    $layout->fleet_per_agency = $this->getFleetPerAgency();
		    $layout->fleet_current_x_expected = $this->getFleetCurrentXExpected($initial, $final);
		}
		
		$layout->KPIs = $this->getRequestKPIs($initial, $final, $agency);
		$layout->request_x_distance = $this->getRequestsXDistance(clone $initial, clone $final, $agency);
		$layout->trips_x_freight = $this->getTripsXFreight($initial, $final, $agency);
		
		$layout->fleet_per_type = $this->getFleetPerType($agency);
		$layout->fleet_per_family = $this->getFleetPerFamily($agency);
		$layout->fleet_per_age = $this->getFleetPerAge($agency);
		$layout->vehicle_x_equipament = $this->getVehicleXEquipament($agency);
		
		$layout->KPIs+= $this->getFuelKPIs($initial, $final, $agency);
		$layout->fuel_x_distance = $this->getFuelXDistance(clone $initial, clone $final, $agency);
		$layout->fuel_outlier_g = $this->getFuelOutlier('G', clone $initial, clone $final, $agency);
		$layout->fuel_outlier_e = $this->getFuelOutlier('E', clone $initial, clone $final, $agency);
		$layout->fuel_outlier_d = $this->getFuelOutlier('D', clone $initial, clone $final, $agency);
		$layout->fuel_per_type = $this->getFuelPerType($initial, $final, $agency);
		$layout->fuel_per_agency = $this->getFuelPerAgency($initial, $final);
		
		$layout->notice = $this->getLastNotification();
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
	
	private function getTripsXFreight(\DateTime $initial, \DateTime $final, Agency $agency = null) {
		
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
	
	private function getRequestKPIs(\DateTime $initial, \DateTime $final, Agency $agency = null) {
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('COUNT(u.id) AS score');
		$builder->from(Request::getClass(), 'u');
		$builder->where('u.openedAt BETWEEN :initial AND :final AND u.status != :status');
		$builder->setParameter('initial',  $initial);
		$builder->setParameter('final', $final);
		$builder->setParameter('status', Request::CANCELED);
		
		if ( $agency ) {
			$builder->join('u.requesterUnit', 'a', Join::WITH, 'a.agency = :agency');
			$builder->setParameter('agency', $agency->getId());
		}
		
		$KPI['request_total'] = (int) $builder->getQuery()->getSingleScalarResult();
		
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('COUNT(u.id) AS score', 'SUM(u.odometerFinal-u.odometerInitial) AS distance');
		$builder->from(Request::getClass(), 'u');
		$builder->where('u.openedAt BETWEEN :initial AND :final AND u.status IN (:status)');
		$builder->setParameter('initial', $initial);
		$builder->setParameter('final', $final);
		$builder->setParameter('status', [Request::CONFIRMED, Request::INITIATED, Request::FINISHED]);
		
		if ( $agency ) {
			$builder->join('u.requesterUnit', 'a', Join::WITH, 'a.agency = :agency');
			$builder->setParameter('agency', $agency->getId());
		}
		
		$result = $builder->getQuery()->getSingleResult();
		$KPI['request_finished'] = $result['score'];
		$KPI['request_distance'] = $result['distance'];
		
		
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('COUNT(u.id) AS score');
		$builder->from(Request::getClass(), 'u');
		$builder->where('u.openedAt BETWEEN :initial AND :final AND u.status = :status');
		$builder->setParameter('initial', $initial);
		$builder->setParameter('final', $final);
		$builder->setParameter('status', Request::DECLINED);
		
		if ( $agency ) {
			$builder->join('u.requesterUnit', 'a', Join::WITH, 'a.agency = :agency');
			$builder->setParameter('agency', $agency->getId());
		}
		
		$KPI['request_declined'] = $builder->getQuery()->getSingleScalarResult();
		
		$total = $KPI['request_declined'] + $KPI['request_finished'];
		
		$KPI['request_availability'] = 1 - ($total > 0 ? $KPI['request_declined']/$total : 0);
		
		return $KPI;
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
		
		$builder1 = $this->getEntityManager()->createQueryBuilder();
		$builder1->select('COUNT(u1.id)');
		$builder1->from(RequestTrip::getClass(), 'u1');
		$builder1->join('u1.requesterUnit', 'un1');
		$builder1->join('un1.agency', 'a1');
		$builder1->andWhere('a1.id = a.id AND u1.openedAt BETWEEN :initial AND :final AND u1.status = :status');
		$builder->addSelect('( ' . $builder1->getDQL() . ' ) as '. RequestTrip::REQUEST_TYPE);
		
		$builder1 = $this->getEntityManager()->createQueryBuilder();
		$builder1->select('COUNT(u2.id)');
		$builder1->from(RequestFreight::getClass(), 'u2');
		$builder1->join('u2.requesterUnit', 'un2');
		$builder1->join('un2.agency', 'a2');
		$builder1->andWhere('a2.id = a.id AND u2.openedAt BETWEEN :initial AND :final AND u2.status = :status');
		$builder->addSelect('( ' . $builder1->getDQL() . ' ) as '. RequestFreight::REQUEST_TYPE);
		
		$builder->from(Agency::getClass(), 'a');
		$builder->where('a.id > 0');
		$builder->setParameter('initial', $initial);
		$builder->setParameter('final', $final);
		$builder->setParameter('status', Request::FINISHED);
		$builder->groupBy('a.id');
		$builder->addOrderBy('a.id', 'desc');
		
		$result = $builder->getQuery()->getResult();
		$data = [];
		foreach ($result as $item) {
			$data[RequestTrip::REQUEST_TYPE][] = ['x' => $item['label'], 'y' => $item[RequestTrip::REQUEST_TYPE]];
			$data[RequestFreight::REQUEST_TYPE][] = ['x' => $item['label'], 'y' => $item[RequestFreight::REQUEST_TYPE]];
		}
		return $data;
	}
	
	private function getFleetPerAgency() {
		
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('a.id', 'a.acronym AS label');
		
		$builder1 = $this->getEntityManager()->createQueryBuilder();
		$builder1->select('COUNT(u1.id)');
		$builder1->from(FleetItem::getClass(), 'u1');
		$builder1->where('u1.active = true AND u1.responsibleUnit = a.id AND u1.fleet = ' . Fleet::OWN);
		$builder->addSelect('( ' . $builder1->getDQL() . ' ) as score'. Fleet::OWN);
		
		
		$builder1 = $this->getEntityManager()->createQueryBuilder();
		$builder1->select('COUNT(u2.id)');
		$builder1->from(FleetItem::getClass(), 'u2');
		$builder1->where('u2.active = true AND u2.responsibleUnit = a.id AND u2.fleet = ' . Fleet::RENTED);
		$builder->addSelect('( ' . $builder1->getDQL() . ' ) as score'. Fleet::RENTED);
		
		$builder1 = $this->getEntityManager()->createQueryBuilder();
		$builder1->select('COUNT(u3.id)');
		$builder1->from(FleetItem::getClass(), 'u3');
		$builder1->where('u3.active = true AND u3.responsibleUnit = a.id AND u3.fleet = ' . Fleet::GUARDED);
		$builder->addSelect('( ' . $builder1->getDQL() . ' ) as score' . Fleet::GUARDED);
		
		$builder1 = $this->getEntityManager()->createQueryBuilder();
		$builder1->select('COUNT(u4.id)');
		$builder1->from(FleetItem::getClass(), 'u4');
		$builder1->where('u4.active = true AND u4.responsibleUnit = a.id AND u4.fleet = ' . Fleet::ASSIGNED);
		$builder->addSelect('( ' . $builder1->getDQL() . ' ) as score' . Fleet::ASSIGNED);
		
		$builder1 = $this->getEntityManager()->createQueryBuilder();
		$builder1->select('COUNT(u5.id)');
		$builder1->from(FleetItem::getClass(), 'u5');
		$builder1->where('u5.active = false AND u5.responsibleUnit = a.id');
		$builder->addSelect('( ' . $builder1->getDQL() . ' ) AS score0');
		
		$builder1 = $this->getEntityManager()->createQueryBuilder();
		$builder1->select('COUNT(u6.id)');
		$builder1->from(FleetItem::getClass(), 'u6');
		$builder1->where('u6.responsibleUnit = a.id');
		$builder->addSelect('( ' . $builder1->getDQL() . ' ) AS HIDDEN total');
		
		$builder->from(Agency::getClass(), 'a');
		$builder->where('a.id > 0');
		$builder->groupBy('a.id');
		$builder->addOrderBy('total', 'desc');
		
		$result = $builder->getQuery()->getResult();
		$data = [];
		$allowed = FleetItem::getFleetAllowed() + [0 => 'Inativa'];
		foreach ($result as $item) {
		    foreach ($allowed as $key => $serie) {
		        $value = ['x' => $item['label'], 'y' => (int) $item['score'.$key]];
		        $data[$serie][] = $value;
		    }
		}
		return $data;
	}
	
	private function getFleetCurrentXExpected(\DateTime $initial, \DateTime $final) {
	    
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('a.id', 'a.acronym AS label');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(u1.id)');
	    $builder1->from(FleetItem::getClass(), 'u1');
	    $builder1->where('u1.responsibleUnit = a.id');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS current');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('COUNT(DISTINCT u2.vehiclePlate)');
	    $builder1->from(ImportTransactionFuel::class, 'u2');
	    $builder1->join('u2.transactionImport', 'i2');
	    $builder1->where('u2.transactionDate BETWEEN :initial AND :final AND u2.transactionAgency = a.id AND i2.finished = true');
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS expected');
	    
	    $builder->from(Agency::getClass(), 'a');
	    $builder->where('a.id > 0');
	    $builder->groupBy('a.id');
	    $builder->addOrderBy('current', 'desc');
	    
	    $result = $builder->getQuery()->getResult();
	    $data = [];
	    foreach ($result as $item) {
	        $data[] = ['x' => $item['label'],
        	           'y' => (int) $item['current'],
        	           'goals' => [[
        	                'name' => 'Expectativa',
        	                'value'=> (int) $item['expected'],
        	                'strokeHeight' => 2,
        	                'strokeDashArray' => 2,
        	                'strokeColor' => '#775DD0'
        	            ]]
	                  ];
	    }
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
	
	private function getVehicleXEquipament(Agency $agency = null) {
		
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
	
	
	private function getFuelKPIs(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('SUM(u.itemTotal) AS score');
	    $builder->from(ImportTransactionFuel::class, 'u');
	    $builder->join('u.transactionImport', 'i');
	    $builder->where('u.transactionDate BETWEEN :initial AND :final AND i.finished = true');
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    
	    if ( $agency ) {
	        $builder->andWhere('u.transactionAgency = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    
	    $KPI['fuel_total'] = (float) $builder->getQuery()->getSingleScalarResult();
	    
        $sql = 'SELECT COUNT(DISTINCT CONCAT(YEAR(i0_.transaction_date), MONTH(i0_.transaction_date))) AS total ';
        $sql.= 'FROM import_transactions_fuel i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
        $sql.= 'WHERE i0_.transaction_date BETWEEN ? AND ? AND i1_.finished = 1 ' . ($agency ? 'AND i0_.transaction_agency_id = ' . $agency->getId() . ' ' : '');
        
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total');
        
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $initial->format('Y-m-d H:i:s'));
        $query->setParameter(2, $final->format('Y-m-d H:i:s'));
        
        $amountMonth = $query->getSingleScalarResult();
        
        $KPI['fuel_avg'] = $amountMonth > 0 ? $KPI['fuel_total'] / $amountMonth : $KPI['fuel_total'];
        
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('COUNT(DISTINCT u.vehiclePlate) AS score');
        $builder->from(ImportTransactionFuel::class, 'u');
        $builder->join('u.transactionImport', 'i');
        $builder->where('u.transactionDate BETWEEN :initial AND :final AND i.finished = true');
        $builder->setParameter('initial',  $initial);
        $builder->setParameter('final', $final);
        
        if ( $agency ) {
            $builder->andWhere('u.transactionAgency = :agency');
            $builder->setParameter('agency', $agency->getId());
        }
        
        $KPI['fuel_amount'] = $builder->getQuery()->getSingleScalarResult();
        
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('SUM(u.vehicleDistance) AS score');
        $builder->from(ImportTransactionFuel::class, 'u');
        $builder->join('u.transactionImport', 'i');
        $builder->where('u.vehicleEfficiency BETWEEN 0 AND 20 AND u.transactionDate BETWEEN :initial AND :final AND i.finished = true');
        $builder->setParameter('initial',  $initial);
        $builder->setParameter('final', $final);
        
        if ( $agency ) {
            $builder->andWhere('u.transactionAgency = :agency');
            $builder->setParameter('agency', $agency->getId());
        }
        
        $KPI['fuel_distance'] = $builder->getQuery()->getSingleScalarResult();
	    
	    return $KPI;
	}
	
	private function getFuelXDistance(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $initial->setTime(0, 0, 0);
	    $final->setTime(23, 59, 59);
	    
	    $sql = 'SELECT SUM(i0_.item_total) AS fuel, SUM(i0_.vehicle_distance) AS distance, CONCAT(YEAR(i0_.transaction_date), MONTH(i0_.transaction_date)) AS period ';
	    $sql.= 'FROM import_transactions_fuel i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
	    $sql.= 'WHERE i0_.vehicle_efficiency BETWEEN 0 AND 20 AND i0_.transaction_date BETWEEN ? AND ? AND i1_.finished = 1 ' . ($agency ? 'AND i0_.transaction_agency_id = ' . $agency->getId() . ' ' : '');
	    $sql.= 'GROUP BY period';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('period', 'period');
	    $rsm->addScalarResult('fuel', 'fuel');
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
	            $initial->add(new \DateInterval('P1M'));
	        }
	    } else {
	        while($initial < $final) {
	            $data['label'][] = ucfirst(strftime('%b/%y', $initial->getTimestamp()));
	            $data['fuel'][$initial->format('Yn')] = null;
	            $data['distance'][$initial->format('Yn')] = null;
	            $initial->add(new \DateInterval('P1M'));
	        }
	    }
	    foreach ($result as $item ) {
	        $data['fuel'][$item['period']] = $item['fuel'];
	        $data['distance'][$item['period']] = $item['distance'];
	    }
	    return $data;
	}
	
	
	private function getFuelOutlier($fuel, \DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $initial = $initial->format('Y-m-d H:i:s');
	    $final = $final->format('Y-m-d H:i:s');
	    $sql = 'SELECT ROUND(i0_.vehicle_efficiency) AS efficiency, COUNT(i0_.transaction_id) AS score ';
	    $sql.= 'FROM import_transactions_fuel i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
	    $sql.= 'WHERE LEFT(item_description, 1) =  \'' . $fuel . '\' AND i0_.vehicle_efficiency between 0 AND 20 ';
	    $sql.= 'AND i0_.transaction_date BETWEEN \'' . $initial . '\'  AND \'' . $final . '\' AND i1_.finished = 1';
	    $sql.= ($agency ? ' AND i0_.transaction_agency_id = ' . $agency->getId()  : '') . ' ';
	    $sql.= 'GROUP BY efficiency';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('efficiency', 'x');
	    $rsm->addScalarResult('score', 'y');
	    
	    $result['data'] = $this->getEntityManager()->createNativeQuery($sql, $rsm)->getArrayResult();
	    
	    $sql1 = 'SELECT i0_.vehicle_efficiency AS efficiency ';
	    $sql1.= 'FROM import_transactions_fuel i0_ INNER JOIN imports i1_ ON i0_.transaction_import_id = i1_.id ';
	    $sql1.= 'WHERE LEFT(item_description, 1) =  \'' . $fuel . '\' AND i0_.vehicle_efficiency between 0 AND 20 ';
	    $sql1.= 'AND i0_.transaction_date BETWEEN \'' . $initial . '\'  AND \'' . $final . '\' AND i1_.finished = 1';
	    $sql1.= ($agency ? ' AND i0_.transaction_agency_id = ' . $agency->getId()  : '') . ' ';
	    
	    $sql2 = 'SELECT ROUND(STD(t1.efficiency)) AS std, 
                        ROUND(AVG(t1.efficiency)) AS avg, 
                        (ROUND(AVG(t1.efficiency))-ROUND(STD(t1.efficiency))) AS min, 
                        (ROUND(AVG(t1.efficiency))+ROUND(STD(t1.efficiency))) AS max
                        FROM (' . $sql1 . ') t1';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('std', 'std');
	    $rsm->addScalarResult('avg', 'avg');
	    $rsm->addScalarResult('min', 'min');
	    $rsm->addScalarResult('max', 'max');
	    $result+= $this->getEntityManager()->createNativeQuery($sql2, $rsm)->getSingleResult();
	    
	    $total   = 0;
	    $quartil = 0;
	    foreach($result['data'] as $item) {
	        $total+= $item['y'];
	        if ($item['x'] >= $result['min'] && $item['x'] <= $result['max'] ) {
	            $quartil+= $item['y'];
	        }
	    }
	    $result['percent'] = $quartil/$total*100;
	    
	    return $result;
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
	    $allowed['A'] = 'Arla-32';
	    $allowed['D'] = 'Diesel';
	    $allowed['E'] = 'Etanol';
	    $allowed['G'] = 'Gasolina';
	    $data = [];
	    foreach ($result as $item) {
	        $data['finance'][$allowed[$item['label']]] = $item['finance'];
	        $data['consume'][$allowed[$item['label']]] = $item['consume'];
	    }
	    return $data;
	}
	
	private function getFuelPerAgency(\DateTime $initial, \DateTime $final) {
	    
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('a.id', 'a.acronym AS label');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(u1.itemTotal)');
	    $builder1->from(ImportTransactionFuel::class, 'u1');
	    $builder1->join('u1.transactionImport', 'i1');
	    $builder1->where('u1.transactionAgency = a.id AND u1.transactionDate BETWEEN :initial AND :final AND i1.finished = true AND u1.itemDescription Like \'A%\'');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) as scoreA');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(u2.itemTotal)');
	    $builder1->from(ImportTransactionFuel::class, 'u2');
	    $builder1->join('u2.transactionImport', 'i2');
	    $builder1->where('u2.transactionAgency = a.id AND u2.transactionDate BETWEEN :initial AND :final AND i2.finished = true AND u2.itemDescription Like \'D%\'');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) as scoreD');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(u3.itemTotal)');
	    $builder1->from(ImportTransactionFuel::class, 'u3');
	    $builder1->join('u3.transactionImport', 'i3');
	    $builder1->where('u3.transactionAgency = a.id AND u3.transactionDate BETWEEN :initial AND :final AND i3.finished = true AND u3.itemDescription Like \'E%\'');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) as scoreE');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(u4.itemTotal)');
	    $builder1->from(ImportTransactionFuel::class, 'u4');
	    $builder1->join('u4.transactionImport', 'i4');
	    $builder1->where('u4.transactionAgency = a.id AND u4.transactionDate BETWEEN :initial AND :final AND i4.finished = true AND u4.itemDescription Like \'G%\'');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) as scoreG');
	    
	    $builder1 = $this->getEntityManager()->createQueryBuilder();
	    $builder1->select('SUM(u5.itemTotal)');
	    $builder1->from(ImportTransactionFuel::class, 'u5');
	    $builder1->join('u5.transactionImport', 'i5');
	    $builder1->where('u5.transactionAgency = a.id AND u5.transactionDate BETWEEN :initial AND :final AND i5.finished = true');
	    $builder->addSelect('( ' . $builder1->getDQL() . ' ) AS HIDDEN total');
	    
	    $builder->from(Agency::getClass(), 'a');
	    $builder->where('a.id > 0');
	    $builder->setParameter('initial',  $initial);
	    $builder->setParameter('final', $final);
	    $builder->groupBy('a.id');
	    $builder->addOrderBy('total', 'desc');
	    
	    $result = $builder->getQuery()->getResult();
	    $data = [];
	    $allowed['A'] = 'Arla-32';
	    $allowed['D'] = 'Diesel';
	    $allowed['E'] = 'Etanol';
	    $allowed['G'] = 'Gasolina';;
	    foreach ($result as $item) {
	        foreach ($allowed as $key => $serie) {
	            $data[$serie][] = ['x' => $item['label'], 'y' => (int) $item['score'.$key]];
	        }
	    }
	    return $data;
	}
	
}
?>