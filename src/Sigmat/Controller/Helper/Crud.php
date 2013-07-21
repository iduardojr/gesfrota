<?php
namespace Sigmat\Controller\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use PHPBootstrap\Widget\Misc\Alert;
use Sigmat\View\AbstractForm;
use Sigmat\View\EntityDatasource;
use Sigmat\View\AbstractList;
use Sigmat\Model\Entity;
use PHPBootstrap\Mvc\Http\Cookie;
use PHPBootstrap\Common\ArrayCollection;
use PHPBootstrap\Common\Enum;
use Sigmat\Model\Deleting;
use PHPBootstrap\Mvc\Controller;

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
	 * @var Controller
	 */
	protected $controller;
	
	/**
	 * @var ArrayCollection
	 */
	protected $listeners;
	
	/**
	 * @var array
	 */
	protected $exceptions;
	
	/**
	 * Construtor
	 * 
	 * @param EntityManager $em
	 * @param string $entity
	 * @param Controller $controller
	 */
	public function __construct( EntityManager $em, $entity, Controller $controller ) {
		$this->em = $em;
		$this->entity = $entity;
		$this->controller = $controller;
		$this->listeners = new ArrayCollection();
		$this->exceptions = array();
	}
	
	/**
	 * Atribui uma exceção
	 * 
	 * @param \Exception $e
	 */
	public function setException( \Exception $e ) {
		$this->exceptions[get_class($e)] = $e;
	}
	
	/**
	 * Cria uma nova entidade
	 * 
	 * @param AbstractForm $form
	 * @param Entity $entity
	 * @throws InvalidRequestDataException
	 * @throws Exception
	 * @return boolean
	 */
	public function create( AbstractForm $form, $entity = null ) {
		$request = $this->controller->getRequest();
		$this->object = $entity;
		if ( $this->object == null ) {
			$this->object = new $this->entity;
		}
		$form->extract($this->object);
		if ( $request->isPost() ) {
			$form->bind($request->getPost());
			if ( ! $form->valid() ) {
				throw $this->getException(new InvalidRequestDataException());
			}
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
	 * @param AbstractList $list
	 * @param QueryBuilder $query
	 * @param array $defaults
	 */
	public function read( AbstractList $list, QueryBuilder $query = null, array $defaults = array() ) {
		$request = $this->controller->getRequest();
		$response = $this->controller->getResponse();
		if ( $query == null ) {
			$query = $this->em->getRepository($this->entity)->createQueryBuilder('u');
		}
		$storage = $request->getCookie('storage');
		if ( $storage !== null ) {
			$storage = json_decode($storage, true);
			if ( $storage['identify'] == md5($this->entity) ) {
				$defaults = array_merge($defaults, isset($storage['data']) ? $storage['data'] : array());
			} else {
				$storage = array();
				$storage['identify'] = md5($this->entity);
			}
		} else {
			$storage = array();
			$storage['identify'] = md5($this->entity);
		}
		$get = $request->getQuery();
		$datasource = new EntityDatasource($query, $defaults);
		if ( $request->isPost() ) {
			unset($storage['data']['filter']);
			$datasource->setFilter($request->getPost());
			if ( $datasource->hasFilter() ) {
				$list->setAlert(new Alert($datasource->getTotal() . ' resultados encontrados pela sua pesquisa', Alert::Info));
				$storage['data']['filter'] = $datasource->getFilter();
			}
		}
		if ( isset($get['reset']) ) {
			$datasource->setFilter(array());
			unset($storage['data']['filter']);
		}
		if ( isset($get['sort']) ) {
			$datasource->toggleOrder(trim($get['sort']));
			$storage['data']['sort'] = $datasource->getSort();
			$storage['data']['order'] = $datasource->getOrder();
		}
		if ( isset($get['page']) ) {
			$datasource->setPage((int) $get['page']);
			$storage['data']['page'] = $datasource->getPage();
		}
		if ( isset($get['limit']) ) {
			$datasource->setLimit((int) $get['limit']);
			$storage['data']['limit'] = $datasource->getLimit();
		}
		$list->setDatasource($datasource);
		$response->setCookie(new Cookie('storage', json_encode($storage)));
	}
	
	/**
	 * Atualiza uma entidade
	 * 
	 * @param AbstractForm $form
	 * @param Entity|integer $entity
	 * @throws NotFoundEntityException
	 * @throws InvalidRequestDataException
	 * @return boolean
	 */
	public function update( AbstractForm $form, $entity ) {
		$request = $this->controller->getRequest();
		if ( $entity instanceof Entity ) {
			$this->object = $entity;
		} else {
			$this->object = $this->em->find($this->entity, ( int ) $entity);
		}
		if ( ! $this->object ) {
			throw $this->getException(new NotFoundEntityException());
		}
		$form->extract($this->object);
		$form->getButtonByName('submit')->setLabel('Salvar');
		if ( $request->isPost() ) {
			$form->bind($request->getPost());
			if ( ! $form->valid() ) {
				throw $this->getException(new InvalidRequestDataException());
			}
			$form->hydrate($this->object, $this->em);
			$this->trigger(self::PrePersist, array($this->object, $this->em));
			$this->em->persist($this->object);
			$this->em->flush();
			return true;
		} 
		return false;
	}
	
	/**
	 * Remove uma entidade
	 *
	 * @param Entity|integer $entity
	 * @throws NotFoundEntityException
	 * @throws Exception
	 */
	public function delete( $entity ) {
		if ( $entity instanceof Entity ) {
			$this->object = $entity;
		} else {
			$this->object = $this->em->find($this->entity, ( int ) $entity);
		}
		if ( ! $this->object ) {
			throw $this->getException(new NotFoundEntityException());
		}
		if ( $this->object instanceof Deleting ) {
			$this->object->delete();
		} else {
			$this->em->remove($this->object);
		}
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
	 * @param callback $handler
	 * @throws \UnexpectedValueException
	 * @throws \InvalidArgumentException
	 */
	public function attach( $event, $handler ) {
		if ( ! is_callable($handler) ) {
			throw new \InvalidArgumentException('handler not is callable');
		}
		$this->listeners->set(Enum::ensure($event, $this), $handler);
	}

	/**
	* Remove um evento do helper e retorna o closure:
	* - Crud.PrePersist
	*
	* @param string $event
	* @return callback
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
	
	/**
	 * Obtem uma exceção ou retorna a default
	 *
	 * @param \Exception $default
	 * @return \Exception
	 */
	protected function getException( \Exception $default ) {
		$key = get_class($default);
		if ( isset($this->exceptions[$key])) {
			return $this->exceptions[$key];
		}
		return $default;
	}
	
}
?>