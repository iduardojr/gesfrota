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

/**
 * Ajudante de create-read-update-delete
 */
class Crud {
	
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
	 * Construtor
	 * 
	 * @param EntityManager $em
	 * @param string $entity
	 */
	public function __construct( EntityManager $em, $entity ) {
		$this->em = $em;
		$this->entity = $entity;
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
			$form->hydrate($this->object);
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
		$query = $request->getQuery();
		if ( isset($query['sort']) ) {
			$datasource->toggleOrder(trim($query['sort']));
		}
		if ( isset($query['reset']) ) {
			$datasource->setFilter(array());
		}
		if ( isset($query['page']) ) {
			$datasource->setPage((int) $query['page']);
		}
		if ( isset($query['limit']) ) {
			$datasource->setLimit((int) $query['limit']);
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
			$form->hydrate($this->object);
			$this->em->persist($this->object);
			$this->em->flush();
			return true;
		} 
		$form->extract($this->object);
		$form->getButtonByName('submit')->setLabel('Salvar');
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
	
}
?>