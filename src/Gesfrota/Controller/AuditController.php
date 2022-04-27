<?php
namespace Gesfrota\Controller;

use Doctrine\ORM\QueryBuilder;
use Gesfrota\Controller\Helper\Crud;
use Gesfrota\Controller\Helper\NotFoundEntityException;
use Gesfrota\Services\Log;
use Gesfrota\View\AuditLog;
use Gesfrota\View\AuditLogTable;
use Gesfrota\View\Layout;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Misc\Alert;

class AuditController extends AbstractController { 
		
	public function indexAction() {
		$table = new AuditLogTable(new Action($this), new Action($this, 'view'));
		$helper = new Crud($this->getEntityManager(), Log::class, $this);
		$helper->read($table, null, ['sort' => 'created', 'order' => 'desc', 'limit' => 25, 'processQuery' => function( QueryBuilder $query, array $data ) {
			if ( !empty($data['id']) ) {
				$query->andWhere('u.id IN (:id)');
				$query->setParameter('id', explode(',', $data['id']));
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
	
}
?>