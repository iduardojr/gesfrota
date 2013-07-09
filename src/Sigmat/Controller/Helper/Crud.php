<?php
namespace Sigmat\Controller\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use PHPBootstrap\Mvc\Http\HttpRequest;
use PHPBootstrap\Mvc\Session\Session;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\AbstractForm;
use Sigmat\View\EntityDatasource;
use Sigmat\View\AbstractList;
use Sigmat\Model\Entity;
use PHPBootstrap\Common\ArrayCollection;
use PHPBootstrap\Common\Enum;

/**
 * Ajudante de create-read-update-delete
 */
class Crud {
	
	// Eventos
	const PrePersist = 'pre-persist';
	
	/**
	 * @var EntityManager
	 */
	protected $em;
	
	/**
	 * @var string
	 */
	protected $entity;
	
	/**
	 * @var Entity
	 */
	protected $object;
	
	/**
	 * @var ArrayCollection
	 */
	protected $listeners;
	
	/**
	 * Construtor
	 * 
	 * @param EntityManager $em
	 * @param string $entity
	 */
	public function __construct( EntityManager $em, $entity ) {
		$this->em = $em;
		$this->entity = $entity;
		$this->listeners = new ArrayCollection();
	}
	
	/**
	 * Cria uma nova entidade
	 * 
	 * @param HttpRequest $request
	 * @param AbstractForm $form
	 * @throws InvalidRequestDataException
	 * @throws Exception
	 * @return boolean
	 */
	public function create( HttpRequest $request, AbstractForm $form ) {
		if ( $request->isPost() ) {
			$form->bind($request->getPost());
			if ( ! $form->valid() ) {
				throw new InvalidRequestDataException();
			}
			$this->object = new $this->entity;
			$form->hydrate($this->object, $this->em);
			$this->trigger(self::PrePersist, array($this->object, $this->em));
			$this->em->persist($this->object);
			$this->em->flush();
			return true;
		}
		return false;
	}
	
	/**
	 * Busca um conjundo de entidades
	 *  
	 * @param HttpRequest $request
	 * @param Session $session
	 * @param AbstractList $list
	 * @param QueryBuilder $query
	 * @param array $defaults
	 * @return EntityDatasource
	 */
	public function read( HttpRequest $request, Session $session, AbstractList $list, QueryBuilder $query = null, array $defaults = array() ) {
		if ( $query == null ) {
			$query = $this->em->getRepository($this->entity)->createQueryBuilder('u');
		}
		$datasource = new EntityDatasource($query, $session, $defaults);
		if ( $request->isPost() ) {
			$datasource->setFilter($request->getPost());
		}
		$get = $request->getQuery();
		if ( isset($get['sort']) ) {
			$datasource->toggleOrder(trim($get['sort']));
		}
		if ( isset($get['reset']) ) {
			$datasource->setFilter(array());
		}
		if ( isset($get['page']) ) {
			$datasource->setPage((int) $get['page']);
		}
		if ( isset($get['limit']) ) {
			$datasource->setLimit((int) $get['limit']);
		}
		$list->setDatasource($datasource);
		if ( $session->alert ) {
			$list->setAlert($session->alert);
			$session->alert = null;
		} elseif ( $request->isPost() && $datasource->hasFilter() ) {
			$list->setAlert(new Alert($datasource->getTotal() . ' resultados encontrados pela sua pesquisa', Alert::Info));
		}
		return $datasource;
	}
	
	/**
	 * Atualiza uma entidade
	 * 
	 * @param integer $id
	 * @param HttpRequest $request
	 * @param AbstractForm $form
	 * @throws NotFoundEntityException
	 * @throws InvalidRequestDataException
	 * @return boolean
	 */
	public function update( $id, HttpRequest $request, AbstractForm $form ) {
		$this->object = $this->em->find($this->entity, ( int ) $id);
		if ( ! $this->object ) {
			throw new NotFoundEntityException();
		}
		if ( $request->isPost() ) {
			$form->bind($request->getPost());
			if ( ! $form->valid() ) {
				throw new InvalidRequestDataException();
			}
			$form->hydrate($this->object, $this->em);
			$this->trigger(self::PrePersist, array($this->object, $this->em));
			$this->em->persist($this->object);
			$this->em->flush();
			return true;
		} 
		$form->getButtonByName('submit')->setLabel('Salvar');
		$form->extract($this->object);
		return false;
	}
	
	/**
	 * Remove uma entidade
	 *
	 * @param integer $id
	 * @throws NotFoundEntityException
	 * @throws Exception
	 */
	public function delete( $id ) {
		$this->object = $this->em->find($this->entity, ( int ) $id);
		if ( ! $this->object ) {
			throw new NotFoundEntityException();
		}
		$this->em->remove($this->object);
		$this->em->flush();
	}
	
	/**
	 * Obtem a entidade
	 * 
	 * @return Entity
	 */
	public function getEntity() {
		return $this->object;
	}
	
	/**
	 * Atribui um evento ao crud:
	 * - Crud.PrePersist
	 *
	 * @param string $event
	 * @param \Closure $handler
	 * @throws \UnexpectedValueException
	 */
	public function attach( $event, \Closure $handler ) {
		$this->listeners->set(Enum::ensure($event, $this), $handler);
	}

	/**
	 * Remove um evento do helper e retorna o closure:
	 * - Crud.PrePersist
	 *
	 * @param string $event
	 * @return \Closure
	 * @throws \UnexpectedValueException
	 */
	public function detach( $event ) {
		return $this->listeners->removeKey(Enum::ensure($event, $this));
	}

	/**
	 * Dispara um evento e retorna se deve seguir ou não com o padrão do evento: 
	 * - Crud.PrePersist
	 *
	 * @param string $event
	 * @param array $data
	 * @return boolean
	 * @throws \RuntimeException
	 * @throws \UnexpectedValueException
	 */
	protected function trigger( $event, array $data = array() ) {
		$event = Enum::ensure($event, $this);
		if ( $this->listeners && $this->listeners->containsKey($event) ) {
			$handler = $this->listeners->get($event);
			if ( call_user_func_array($handler, $data) === false ) {
				return false;
			}
		}
		return true;
	}
}
?>