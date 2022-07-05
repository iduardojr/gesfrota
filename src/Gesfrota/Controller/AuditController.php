<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Model\Domain\AdministrativeUnit;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\Disposal;
use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\OwnerCompany;
use Gesfrota\Model\Domain\OwnerPerson;
use Gesfrota\Model\Domain\RequestFreight;
use Gesfrota\Model\Domain\RequestTrip;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\ServiceCard;
use Gesfrota\Model\Domain\ServiceProvider;
use Gesfrota\Model\Domain\Survey;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\VehicleFamily;
use Gesfrota\Model\Domain\VehicleMaker;
use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\Services\Log;
use Gesfrota\View\AuditLog;
use Gesfrota\View\AuditLogTable;
use Gesfrota\View\Layout;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class AuditController extends AbstractController { 
		
	public function indexAction() {
		$table = new AuditLogTable(new Action($this), new Action($this, 'view'), $this->getOptClassname());
		$helper = new Crud($this->getEntityManager(), Log::class, $this);
		$helper->read($table, null, ['sort' => 'created', 'order' => 'desc', 'limit' => 20, 'processQuery' => function( QueryBuilder $query, array $data ) {
			if ( !empty($data['id']) ) {
				$query->andWhere('u.id IN (:id)');
				$query->setParameter('id', explode(',', $data['id']));
			}
			
			if ( !empty($data['object-class'])) {
				$query->andWhere('u.className = :className');
				$query->setParameter('className', $data['object-class']);
				
				if (!empty($data['object-id'])) {
					$query->andWhere('u.oid = :oid');
					$query->setParameter('oid', $data['object-id']);
				}
			}
			if ( !empty($data['uri']) ) {
				$query->andWhere('u.referer LIKE :uri');
				$query->setParameter('uri', '%' . $data['uri'] . '%');
			}
			if ( !empty($data['user']) ) {
				$query->join('u.user', 'u1');
				$query->andWhere('u1.name LIKE :user');
				$query->setParameter('user', '%' . $data['user'] . '%');
			}
			if ( !empty($data['agency']) ) {
				$query->join('u.agency', 'a');
				$query->andWhere('a.name LIKE :agency OR a.acronym LIKE :agency');
				$query->setParameter('agency', '%' . $data['agency'] . '%');
			}
			if ( !empty($data['date-initial']) ) {
				$date = new \DateTime($data['date-initial'] . ' ' . $data['time-initial']);
				$query->andWhere('u.created >= :initial');
				$query->setParameter('initial', $date);
			}
			if ( !empty($data['date-final']) ) {
				$date = new \DateTime($data['date-final'] . ' ' . $data['time-final']);
				$query->andWhere('u.created <= :final');
				$query->setParameter('final', $date);
			}
		}]);
		$table->setAlert($this->getAlert());
		return new Layout($table);
	}
	
	public function viewAction() {
		try {
			$key = $this->request->getQuery('key');
			$log = $this->getEntityManager()->find(Log::class, $key);
			if (! $log ) {
				throw new NotFoundEntityException('Não foi possível visualizar detalhes do log. Log <em>#' . $key . '</em> não encontrado.');
			}
			return new Layout(new AuditLog($log, new Action($this)));
		} catch (\Exception $e) {
			$this->setAlert(new Alert('<strong>Ops! </strong>' . $e->getMessage(), Alert::Danger));
			$this->forward('/');
		}
		
	}
	
	/**
	 * @return array
	 */
	private function getOptClassname() {
		
		$opt = [];
		$opt[] = Agency::getClass();
		$opt[] = AdministrativeUnit::getClass();
		
		$opt[] = OwnerPerson::getClass();
		$opt[] = OwnerCompany::getClass();
		$opt[] = ServiceProvider::getClass();
		
		$opt[] = Requester::getClass();
		$opt[] = Driver::getClass();
		$opt[] = FleetManager::getClass();
		$opt[] = Manager::getClass();
		
		$opt[] = VehicleFamily::getClass();
		$opt[] = VehicleMaker::getClass();
		$opt[] = VehicleModel::getClass();
		$opt[] = Vehicle::getClass();
		$opt[] = Equipment::getClass();
		$opt[] = ServiceCard::getClass();
		
		$opt[] = RequestTrip::getClass();
		$opt[] = RequestFreight::getClass();
		
		$opt[] = Disposal::getClass();
		$opt[] = DisposalItem::getClass();
		$opt[] = Survey::getClass();
		
		return $opt;
	}
	
}
?>