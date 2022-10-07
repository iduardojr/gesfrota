<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Request;
use Gesfrota\Model\Domain\RequestFreight;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\Notice;
use Gesfrota\Services\Log;
use Gesfrota\View\Layout;
use Gesfrota\Model\Domain\ImportTransactionFix;
use Gesfrota\Model\Domain\ImportTransactionItem;
use Gesfrota\Model\Domain\Disposal;
use Gesfrota\Model\Domain\DisposalItem;

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
		$layout->KPIs = [];
		switch ($tabActive) {
		    case 'request':
		        if ( ! $agency ) {
		            $layout->request_per_agency = $this->getRequestPerAgency(clone $initial, clone $final);
		        } else {
		            $layout->request_x_driver = $this->getRequestsPerDriver(clone $initial, clone $final, $agency);
		        }
		        $layout->KPIs+= $this->getRequestKPIs(clone $initial, clone $final, $agency);
		        $layout->request_x_distance = $this->getRequestsXDistance(clone $initial, clone $final, $agency);
		        $layout->request_trips_x_freight = $this->getRequestTripsXFreight(clone $initial, clone $final, $agency);
		        break;
		        
		    case 'fuel':
		        if ( $agency == null) {
		            $layout->fuel_per_agency = $this->getFuelPerAgency(clone $initial, clone $final);
		        }
		        $layout->KPIs+= $this->getFuelKPIs(clone $initial, clone $final, $agency);
		        $layout->fuel_x_distance = $this->getFuelXDistance(clone $initial, clone $final, $agency);
		        $layout->fuel_outlier = $this->getFuelOutlier(clone $initial, clone $final, $agency);
		        $layout->fuel_per_type = $this->getFuelPerType(clone $initial, clone $final, $agency);
		        break;
		        
		    case 'fix':
		        if ( $agency == null) {
		            $layout->fix_per_agency = $this->getFixPerAgency(clone $initial, clone $final);
		        }
		        $layout->KPIs+= $this->getFixKPIs(clone $initial, clone $final, $agency);
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
		        $layout->KPIs+= $this->getFleetKPIs($agency);
		        $layout->fleet_per_type = $this->getFleetPerType($agency);
		        $layout->fleet_per_family = $this->getFleetPerFamily($agency);
		        $layout->fleet_per_age = $this->getFleetPerAge($agency);
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
	    $sql = 'SELECT';
	    $sql.= ' status, ';
	    $sql.= ' SUM(requests) AS requests, ';
	    $sql.= ' SUM(distance) AS distance ';
	    $sql.= 'FROM resume_requests ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY status';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('status', 'status');
	    $rsm->addScalarResult('requests', 'requests');
	    $rsm->addScalarResult('distance', 'distance');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    $data['request_total']    = 0; 
	    $data['request_finished'] = 0;
	    $data['request_distance'] = 0; 
	    $data['request_declined'] = 0;
	    
	    $allow = [Request::CONFIRMED, Request::INITIATED, Request::FINISHED];
	    
	    $result = $query->getResult();
	    foreach ($result as $item) {
	        $data['request_total']+= $item['status'] != Request::CANCELED ? $item['requests'] : 0;
	        $data['request_finished']+= array_search($item['status'], $allow) !== false ? $item['requests'] : 0;
	        $data['request_distance']+= array_search($item['status'], $allow) !== false ? $item['distance'] : 0;
	        $data['request_declined']+= $item['status'] == Request::DECLINED ? $item['requests'] : 0;
	    }
	    $total = $data['request_declined'] + $data['request_finished'];
	    $data['request_availability'] = 1 - ($total > 0 ? $data['request_declined']/$total : 0);
	    
	    return $data;
	}
	
	private function getRequestTripsXFreight(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT type AS discr, SUM(requests) AS requests, SUM(distance) AS distance  ';
	    $sql.= 'FROM resume_requests ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final AND status = :finished ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY discr';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('discr', 'discr');
	    $rsm->addScalarResult('requests', 'requests');
	    $rsm->addScalarResult('distance', 'distance');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('finished', Request::FINISHED);
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
		
	    $data = [];
	    $result = $query->getResult();
	    foreach ($result as $row) {
	        switch ($row['discr']) {
	            case 'T':
	                $key  = RequestTrip::REQUEST_TYPE;
	                break;
	            
	            case 'F':
	                $key = RequestFreight::REQUEST_TYPE;
	                break;
	        }
	        $data[$key] = $row['requests'];
	    }
		
		return $data;
	}
	
	private function getRequestsXDistance(\DateTime $initial, \DateTime $final, Agency $agency = null) {
		$sql = 'SELECT TRIM(period) AS discr, SUM(requests) AS requests, SUM(distance) AS distance ';
        $sql.= 'FROM resume_requests ';
        $sql.= 'WHERE period BETWEEN :initial AND :final AND status = :finished ' . ($agency ? 'AND id = :agency ' : '');
		$sql.= 'GROUP BY discr';
		
		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('discr', 'discr');
		$rsm->addScalarResult('requests', 'requests');
		$rsm->addScalarResult('distance', 'distance');
		
		$query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
		$query->setParameter('initial', (int) $initial->format('Ym'));
		$query->setParameter('final', (int) $final->format('Ym'));
		$query->setParameter('finished', Request::FINISHED);
		$query->setParameter('agency', $agency ? $agency->getId() : null);
		
		$data = [];
		$result = $query->getResult();
		if ($initial->format('Y') == $final->format('Y')) {
			while($initial < $final) {
				$data['label'][] = ucfirst(strftime('%b', $initial->getTimestamp()));
				$data['requests'][$initial->format('Ym')] = 0;
				$data['distance'][$initial->format('Ym')] = 0;
				$initial->add(new \DateInterval('P1M'));
				
			}
		} else {
			while($initial < $final) {
				$data['label'][] = ucfirst(strftime('%b/%y', $initial->getTimestamp()));
				$data['requests'][$initial->format('Ym')] = 0;
				$data['distance'][$initial->format('Ym')] = 0;
				$initial->add(new \DateInterval('P1M'));
			}
		}
		foreach ($result as $item ) {
			$data['requests'][$item['discr']] = $item['requests'];
			$data['distance'][$item['discr']] = $item['distance'];
		}
		return $data;
	}
	
	
	private function getRequestsPerDriver(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT driver_id AS discr, driver, SUM(requests) AS requests, SUM(distance) AS distance ';
	    $sql.= 'FROM resume_requests ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final AND status = :finished ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY discr ';
	    $sql.= 'ORDER BY requests desc, distance desc ';
	    $sql.= 'LIMIT 0, 3 ';

	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('driver', 'driver');
	    $rsm->addScalarResult('requests', 'requests');
	    $rsm->addScalarResult('distance', 'distance');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('finished', Request::FINISHED);
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    return $query->getResult();
	}
	
	private function getRequestPerAgency(\DateTime $initial, \DateTime $final) {
	    $series = ['T' => RequestTrip::REQUEST_TYPE, 'F' => RequestFreight::REQUEST_TYPE];
	    foreach ($series as $serie) {
	        $data[$serie] = [];
	    }
	    
	    $sql = 'SELECT acronym, type AS discr, SUM(requests) AS requests, SUM(distance) AS distance ';
	    $sql.= 'FROM resume_requests ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final AND status = :finished OR period IS NULL OR status IS NULL ';
	    $sql.= 'GROUP BY id, discr ';
	    $sql.= 'ORDER BY requests desc ';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('acronym', 'label');
	    $rsm->addScalarResult('discr', 'discr');
	    $rsm->addScalarResult('requests', 'requests');
	    $rsm->addScalarResult('distance', 'distance');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('finished', Request::FINISHED);
	    
	    $result = $query->getResult();
	    
	    foreach ($result as $row) {
	        foreach ($series as $key => $serie) {
	            if ($key == $row['discr']) {
	                $data[$serie][$row['label']] = ['x' => $row['label'], 'y' => $row['requests']];
	            } elseif( ! isset($data[$serie][$row['label']]) ) {
	                $data[$serie][$row['label']] = ['x' => $row['label'], 'y' => 0];
	            }
	        }
	    }
	    return $data;
	    
	}
	
	private function getFleetKPIs( Agency $agency = null ) {
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('COUNT(u.id) AS score');
	    $builder->from(DisposalItem::getClass(), 'u');
	    $builder->join('u.disposal', 'd');
	    $builder->where('d.status = :confirmed');
	    $builder->setParameter('confirmed', Disposal::CONFIRMED);
	    if ( $agency ) {
	        $builder->andWhere('d.agency = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    $data['disposal_partial'] = (int) $builder->getQuery()->getSingleScalarResult();
	    
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('COUNT(u.id) AS score');
	    $builder->from(DisposalItem::getClass(), 'u');
	    $builder->join('u.disposal', 'd');
	    $builder->where('d.status NOT IN (:status)');
	    $builder->setParameter('status', [Disposal::DECLINED, Disposal::FORWARDED]);
	    if ( $agency ) {
	        $builder->andWhere('d.agency = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    $data['disposal_total'] = (int) $builder->getQuery()->getSingleScalarResult();
	    
	    $builder = $this->getEntityManager()->createQueryBuilder();
	    $builder->select('SUM(u.value) AS score');
	    $builder->from(DisposalItem::getClass(), 'u');
	    $builder->join('u.disposal', 'd');
	    $builder->where('d.status = :confirmed');
	    $builder->setParameter('confirmed', Disposal::CONFIRMED);
	    if ( $agency ) {
	        $builder->andWhere('d.agency = :agency');
	        $builder->setParameter('agency', $agency->getId());
	    }
	    $data['disposal_value'] = (int) $builder->getQuery()->getSingleScalarResult();
	    
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
		
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('COUNT(u.id) AS score');
		$builder->from(Equipment::getClass(), 'u');
		$builder->where('u.active = true');
		
		if ( $agency ) {
		    $builder->andWhere('u.responsibleUnit = :agency');
		    $builder->setParameter('agency', $agency->getId());
		}
		if ($builder->getQuery()->getSingleScalarResult() > 0 ) {
		    $data['Equipamentos'] = (int) $builder->getQuery()->getSingleScalarResult();
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
	    $sql1 = 'SELECT';
	    $sql1.= ' a.id, ';
	    $sql1.= ' a.acronym, ';
	    $sql1.= ' COUNT(f.id) AS f1, ';
	    $sql1.= ' 0 AS f2 ';
	    $sql1.= 'FROM fleet_items f LEFT JOIN agencies a ON responsible_unit_id = a.id ';
	    $sql1.= 'GROUP BY a.id';
	    
	    $sql2 = 'SELECT';
	    $sql2.= ' id, ';
	    $sql2.= ' acronym, ';
	    $sql2.= ' 0 AS f1, ';
	    $sql2.= ' COUNT(DISTINCT vehicle_plate) AS f2 ';
	    $sql2.= 'FROM resume_fleet_expected ';
	    $sql2.= 'WHERE period BETWEEN :initial AND :final ';
	    $sql2.= 'GROUP BY id';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('acronym', 'label');
	    $rsm->addScalarResult('current', 'current');
	    $rsm->addScalarResult('expected', 'expected');
	    
	    $sql = 'SELECT id, acronym, SUM(f1) AS current, SUM(f2) AS expected ';
	    $sql.= 'FROM ((' . $sql1 . ') UNION ALL (' . $sql2 . ')) t ';
	    $sql.= 'GROUP BY id ';
	    $sql.= 'ORDER BY current desc';
	        
 	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
 	    $query->setParameter('initial', (int) $initial->format('Ym'));
 	    $query->setParameter('final', (int) $final->format('Ym'));
	    
 	    $result = $query->getResult();
	    
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
	
	private function getFuelKPIs(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT';
	    $sql.= ' SUM(item_total) AS fuel_total,';
	    $sql.= ' COUNT(DISTINCT vehicle_plate) AS fuel_amount,';
	    $sql.= ' SUM(vehicle_distance) AS fuel_distance,';
	    $sql.= ' (SUM(item_total)/COUNT(DISTINCT period)) AS fuel_avg ';
	    $sql.= 'FROM resume_fuel ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final ' . ($agency ? 'AND id = :agency ' : '');
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('fuel_total', 'fuel_total');
	    $rsm->addScalarResult('fuel_amount', 'fuel_amount');
	    $rsm->addScalarResult('fuel_distance', 'fuel_distance');
	    $rsm->addScalarResult('fuel_avg', 'fuel_avg');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    return $query->getSingleResult();
	}
	
	private function getFuelXDistance(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT';
	    $sql.= ' TRIM(period) AS discr, ';
	    $sql.= ' SUM(item_total) AS fuel_total,';
	    $sql.= ' COUNT(DISTINCT vehicle_plate) AS fuel_amount,';
	    $sql.= ' SUM(vehicle_distance) AS fuel_distance ';
	    $sql.= 'FROM resume_fuel ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY discr';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('discr', 'period');
	    $rsm->addScalarResult('fuel_total', 'fuel');
	    $rsm->addScalarResult('fuel_amount', 'vehicles');
	    $rsm->addScalarResult('fuel_distance', 'distance');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    $data = [];
	    $format = $initial->format('Y') == $final->format('Y') ? '%b' : '%b/%y';
        while($initial < $final) {
            $data['label'][] = ucfirst(strftime($format, $initial->getTimestamp()));
            $data['fuel'][$initial->format('Ym')] = null;
            $data['distance'][$initial->format('Ym')] = null;
            $data['vehicles'][$initial->format('Ym')] = null;
            $initial->add(new \DateInterval('P1M'));
        }
        
        $result = $query->getResult();
	    foreach ($result as $item ) {
	        $data['fuel'][$item['period']] = $item['fuel'];
	        $data['distance'][$item['period']] = $item['distance'];
	        $data['vehicles'][$item['period']] = $item['vehicles'];
	    }
	    return $data;
	}
	
	
	private function getFuelOutlier(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT fuel, ROUND(vehicle_efficiency) AS efficiency, COUNT(transaction_id) AS score ';
	    $sql.= 'FROM resume_fuel_efficiency ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final AND vehicle_efficiency IS NOT NULL AND fuel != "A" ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY fuel, efficiency ';
	    $sql.= 'ORDER BY fuel, efficiency';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('fuel', 'fuel');
	    $rsm->addScalarResult('efficiency', 'x');
	    $rsm->addScalarResult('score', 'y');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    $result1 = $query->getArrayResult();
	    
	    $sql = 'SELECT fuel, vehicle_efficiency AS efficiency ';
	    $sql.= 'FROM resume_fuel_efficiency ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final AND vehicle_efficiency IS NOT NULL AND fuel != "A" ' . ($agency ? 'AND id = :agency ' : '');
	    
	    $sql2 = 'SELECT';
	    $sql2.= ' t1.fuel,';
	    $sql2.= ' COUNT(efficiency) AS total,';
	    $sql2.= ' ROUND(STD(t1.efficiency)) AS std,';
	    $sql2.= ' ROUND(AVG(t1.efficiency)) AS avg,';
	    $sql2.= ' (ROUND(AVG(t1.efficiency))-ROUND(STD(t1.efficiency))) AS min,';
	    $sql2.= ' (ROUND(AVG(t1.efficiency))+ROUND(STD(t1.efficiency))) AS max ';
	    $sql2.= 'FROM (' . $sql . ') t1 ';
	    $sql2.= 'GROUP BY t1.fuel';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('fuel', 'fuel');
	    $rsm->addScalarResult('total', 'total');
	    $rsm->addScalarResult('std', 'std');
	    $rsm->addScalarResult('avg', 'avg');
	    $rsm->addScalarResult('min', 'min');
	    $rsm->addScalarResult('max', 'max');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql2, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    $result2 = $query->getArrayResult();
	    
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
	    $sql = 'SELECT fuel AS discr, SUM(item_total) AS item_total, SUM(item_quantity) AS item_quantity ';
	    $sql.= 'FROM resume_fuel ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final OR period IS NULL ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY discr ';
	    $sql.= 'ORDER BY item_total desc';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('discr', 'discr');
	    $rsm->addScalarResult('item_total', 'item_total');
	    $rsm->addScalarResult('item_quantity', 'item_quantity');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    $data = [];
	    $result = $query->getResult();
	    $series = ['A' => 'Arla-32', 'D' => 'Diesel', 'E' => 'Etanol', 'G' => 'Gasolina'];
	    foreach ($series as $serie) {
	        $data['finance'][$serie] = 0;
	        $data['consume'][$serie] = 0;
	    }
	    
	    foreach ($result as $row) {
	        $data['finance'][$series[$row['discr']]] = (float) $row['item_total'];
	        $data['consume'][$series[$row['discr']]] = (float) $row['item_quantity'];
	    }
	    return $data;
	}
	
	private function getFuelPerAgency(\DateTime $initial, \DateTime $final) {
	    $sql = 'SELECT acronym, fuel AS discr, SUM(item_total) AS item_total ';
	    $sql.= 'FROM resume_fuel  ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final OR period IS NULL ';
	    $sql.= 'GROUP BY id, discr ';
	    $sql.= 'ORDER BY item_total desc';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('acronym', 'label');
	    $rsm->addScalarResult('discr', 'discr');
	    $rsm->addScalarResult('item_total', 'item_total');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    
	    $data = [];
	    $result = $query->getResult();
	    $series = ['A' => 'Arla-32', 'D' => 'Diesel', 'E' => 'Etanol', 'G' => 'Gasolina'];
	    foreach ($series as $serie) {
	        $data[$serie] = [];
	    }
	    
	    foreach ($result as $row) {
	        foreach ($series as $key => $serie) {
	            if ($key == $row['discr']) {
	                $data[$serie][$row['label']] = ['x' => $row['label'], 'y' => $row['item_total']];
	            } elseif( ! isset($data[$serie][$row['label']]) ) {
	                $data[$serie][$row['label']] = ['x' => $row['label'], 'y' => 0];
	            }
	        }
	    }
	    return $data;
	}
	
	private function getFixKPIs(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT';
	    $sql.= ' SUM(item_total) AS fix_total,';
	    $sql.= ' COUNT(DISTINCT vehicle_plate) AS fix_amount,';
	    $sql.= ' (SUM(item_total)/COUNT(DISTINCT period)) AS fix_avg ';
	    $sql.= 'FROM resume_fix ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final ' . ($agency ? 'AND id = :agency ' : '');
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('fix_total', 'fix_total');
	    $rsm->addScalarResult('fix_amount', 'fix_amount');
	    $rsm->addScalarResult('fix_avg', 'fix_avg');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    return $query->getSingleResult();
	}
	
	private function getFixXVehicles(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT';
	    $sql.= ' TRIM(period) AS discr, ';
	    $sql.= ' SUM(item_total) AS fix_total,';
	    $sql.= ' COUNT(DISTINCT vehicle_plate) AS fix_amount ';
	    $sql.= 'FROM resume_fix ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY discr';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('discr', 'period');
	    $rsm->addScalarResult('fix_total', 'score');
	    $rsm->addScalarResult('fix_amount', 'vehicles');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    $data = [];
	    $result = $query->getResult();
	    $format = $initial->format('Y') == $final->format('Y') ? '%b' : '%b/%y';
	    while($initial < $final) {
	        $data['label'][] = ucfirst(strftime($format, $initial->getTimestamp()));
	        $data['score'][$initial->format('Ym')] = null;
	        $data['vehicles'][$initial->format('Ym')] = null;
	        $initial->add(new \DateInterval('P1M'));
	    }
	    
	    foreach ($result as $item ) {
	        $data['score'][$item['period']] = $item['score'];
	        $data['vehicles'][$item['period']] = $item['vehicles'];
	    }
	    return $data;
	}
	
	private function getFixPartsXLabor(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT';
	    $sql.= ' item_type AS discr, ';
	    $sql.= ' SUM(item_total) AS fix_total ';
	    $sql.= 'FROM resume_fix ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY discr';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('discr', 'discr');
	    $rsm->addScalarResult('fix_total', 'score');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    $data = [];
	    $result = $query->getResult();
	    foreach ($result as $item) {
	        $data[$item['discr']] = (float) $item['score'];
	    }
	    return $data;
	}
	
	private function getFixPerType(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT';
	    $sql.= ' fixtype AS discr, ';
	    $sql.= ' SUM(item_total) AS fix_total ';
	    $sql.= 'FROM resume_fix ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY discr ';
	    $sql.= 'ORDER BY fix_total desc';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('discr', 'discr');
	    $rsm->addScalarResult('fix_total', 'score');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    $data = [];
	    $result = $query->getResult();
	    foreach ($result as $item) {
	        $data[$item['discr']] = (float) $item['score'];
	    }
	    return $data;
	}
	
	private function getFixPerSupplierType(\DateTime $initial, \DateTime $final, Agency $agency = null) {
	    $sql = 'SELECT';
	    $sql.= ' supplier_type AS discr, ';
	    $sql.= ' SUM(item_total) AS fix_total ';
	    $sql.= 'FROM resume_fix ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final ' . ($agency ? 'AND id = :agency ' : '');
	    $sql.= 'GROUP BY discr ';
	    $sql.= 'ORDER BY fix_total desc';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('discr', 'discr');
	    $rsm->addScalarResult('fix_total', 'score');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    $query->setParameter('agency', $agency ? $agency->getId() : null);
	    
	    $data = [];
	    $result = $query->getResult();
	    foreach ($result as $item) {
	        $data[$item['discr']] = (float) $item['score'];
	    }
	    return $data;
	}
	
	private function getFixPerAgency(\DateTime $initial, \DateTime $final) {
	    $sql = 'SELECT DISTINCT fixtype, SUM(item_total) AS total ';
	    $sql.= 'FROM resume_fix GROUP BY fixtype ORDER BY total desc';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('fixtype', 'fixtype');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    
	    $series = [];
	    $result = $query->getResult();
	    foreach ($result as $item) {
	        $series[] = $item['fixtype'];
	    }
	    
	    $sql = 'SELECT acronym, fixtype AS discr, SUM(item_total) AS item_total ';
	    $sql.= 'FROM resume_fix  ';
	    $sql.= 'WHERE period BETWEEN :initial AND :final ';
	    $sql.= 'GROUP BY id, discr ';
	    $sql.= 'ORDER BY item_total desc';
	    
	    $rsm = new ResultSetMapping();
	    $rsm->addScalarResult('acronym', 'label');
	    $rsm->addScalarResult('discr', 'discr');
	    $rsm->addScalarResult('item_total', 'total');
	    
	    $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
	    $query->setParameter('initial', (int) $initial->format('Ym'));
	    $query->setParameter('final', (int) $final->format('Ym'));
	    
	    $data = [];
	    $result = $query->getResult();
	    foreach ($result as $row) {
	        foreach ($series as $serie) {
	            if ($serie == $row['discr']) {
	                $data[$serie][$row['label']] = ['x' => $row['label'], 'y' => $row['total']];
	            } elseif( ! isset($data[$serie][$row['label']]) ) {
	                $data[$serie][$row['label']] = ['x' => $row['label'], 'y' => 0];
	            }
	        }
	    }
	    return $data;
	}
}
?>