<?php
namespace Gesfrota\Controller;

use Gesfrota\View\Layout;
use Gesfrota\Model\Domain\Request;
use Gesfrota\Services\Log;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\Model\Domain\RequestFreight;
use Doctrine\ORM\Query\ResultSetMapping;
use Gesfrota\Model\Domain\Agency;
use Doctrine\ORM\Query\Expr\Join;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Fleet;

class IndexController extends AbstractController {
	
	public function indexAction() {
		$layout = new Layout('index/index.phtml');
		
		$initial = new \DateTime('first day of Jan ' .date('Y') .' today');
		$final = new \DateTime("now");
		if ($this->request->isPost()) {
			$date = $this->request->getPost();
			if (! empty($date['initial'])) {
				$initial = new \DateTime('first day of ' . \DateTime::createFromFormat('m/Y', $date['initial'])->format('M Y'));
				$initial->setTime(00, 00, 00);
			}
			if (! empty($date['final'])) {
				$final = new \DateTime('last day of ' . \DateTime::createFromFormat('m/Y', $date['final'])->format('M Y'));
				$final->setTime(23, 59, 59);
			}
		}
		$layout->initial = $initial;
		$layout->final = $final;
		
		$layout->isDashboardFleetManager = false;
		$agency = $this->getAgencyActive();
		if ( ! $agency->isGovernment() ) {
			$layout->isDashboardFleetManager = true;
			$layout->request_x_driver = $this->getRequestsPerDriver($initial, $final, $agency);
			$layout->activities = $this->getActivitiesRecent($agency);
		} else {
			$agency = null;
			$layout->request_per_agency = $this->getRequestPerAgency($initial, $final);
			$layout->fleet_per_agency = $this->getFleetPerAgency($initial, $final);
		}
		
		$layout->KPIs = $this->getRequestKPIs($initial, $final, $agency);
		$layout->request_x_distance = $this->getRequestsXDistance($initial, $final, $agency);
		$layout->trips_x_freight = $this->getTripsXFreight($initial, $final, $agency);
		
		$layout->fleet_per_type = $this->getFleetPerType($agency);
		
		$layout->fleet_per_family = $this->getFleetPerFamily($agency);
		$layout->vehicle_x_equipament = $this->getVehicleXEquipament($agency);
		
		return $layout;
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
		$initial = clone $initial;
		$final = clone $final;
		
		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('opened', 'opened');
		$rsm->addScalarResult('request', 'request');
		$rsm->addScalarResult('distance', 'distance');
		
		$sql = 'SELECT CONCAT(YEAR(r0_.opened_at), MONTH(r0_.opened_at)) AS opened, COUNT(r0_.id) AS request, SUM(r0_.odometer_final - r0_.odometer_initial) AS distance ';
		$sql.= 'FROM requests r0_ ';
		if ($agency) {
			$sql.= 'INNER JOIN administrative_units a1_ ON r0_.requester_unit_id = a1_.id AND a1_.agency_id = ' . $agency->getId() . ' ';
		}
		$sql.= 'WHERE (r0_.opened_at BETWEEN ? AND ? AND r0_.status = ?) ';
		$sql.= 'GROUP BY opened';
		
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
		$builder->join('u.driver', 'd');
		$builder->where('u.openedAt BETWEEN :initial AND :final AND u.status = :status');
		$builder->setParameter('initial', $initial);
		$builder->setParameter('final', $final);
		$builder->setParameter('status', Request::FINISHED);
		$builder->groupBy('u.driver');
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
		
		$builder->from(Request::getClass(), 'u');
		$builder->join('u.requesterUnit', 'un');
		$builder->join('un.agency', 'a');
		$builder->where('u.openedAt BETWEEN :initial AND :final AND u.status = :status');
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
		$builder1->where('u5.active = true AND u5.responsibleUnit = a.id');
		$builder->addSelect('( ' . $builder1->getDQL() . ' ) AS HIDDEN total');
		
		$builder->from(FleetItem::getClass(), 'u');
		$builder->join('u.responsibleUnit', 'a');
		$builder->groupBy('a.id');
		$builder->addOrderBy('total', 'desc');
		
		$result = $builder->getQuery()->getResult();
		$data = [];
		foreach ($result as $item) {
			foreach (FleetItem::getFleetAllowed() as $key => $serie) {
				$data[$serie][] = ['x' => $item['label'], 'y' => (int) $item['score'.$key]];
			}
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
	
	
}
?>