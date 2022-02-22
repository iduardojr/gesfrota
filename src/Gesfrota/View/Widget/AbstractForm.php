<?php
namespace Gesfrota\View\Widget;

use Doctrine\ORM\EntityManager;
use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Form\Inputable;
use PHPBootstrap\Widget\Button\Button;

abstract class AbstractForm extends Component {
	
	/**
	 * @var boolean
	 */
	protected $attached;
	
	/**
	 * @var Form
	 */
	protected $component;
	
	/**
	 * Liga os dados submetidos ao formulario
	 *
	 * @param array $submittedData
	 */
	public function bind( array $submittedData ) {
		$this->component->bind($submittedData);
	}

	/**
	 * Prepara o formulario e seus controles para a renderização
	 * 
	 * @return array
	 */
	public function prepare() {
		$this->component->prepare();
		return $this->component->getData();
	}

	/**
	 * Valida o formulário
	 *
	 * @return boolean
	 */
	public function valid() {
		return $this->component->valid();
	}

	/**
	 * Obtem as messagens de erro
	 *
	 * @return \ArrayIterator
	*/
	public function getMessages() {
		return $this->component->getFailMessages();
	}

	/**
	 * Obtem um input a partir do nome
	 *
	 * @param string $name
	 * @return Inputable
	 */
	public function getInputByName( $name ) {
		$input = $this->component->getByName($name); 
		if ( $input instanceof Inputable ) {
			return $input;
		}
		return null;
	}

	/**
	 * Obtem um botão a partir do nome
	 *
	 * @param string $name
	 * @return Button
	 */
	public function getButtonByName( $name ) {
		return $this->component->getButtonByName($name);
	}
	
	/**
	 * Extrai os dados do objeto para o formulario
	 *
	 * @param object $object
	 */
	public function extract( $object ) {
		throw new \BadMethodCallException('unsupported method');
	}
	
	/**
	 * Hidrata o objeto com os valores do formulario
	 *
	 * @param object $object
	 * @param EntityManager $em
	 */
	public function hydrate( $object, EntityManager $em = null ) {
		throw new \BadMethodCallException('unsupported method');
	}
	
	/**
	 * Constroi o formulario
	 * 
	 * @param string $name
	 * @return BuilderForm
	 */
	protected function buildForm( $name ) {
		if ( ! isset($this->component) ) {
			$this->component = new BuilderForm($name, $this);
			if ( $this->panel ) {
				$this->panel->append($this->component);
			}
		}
		return $this->component;
	}
	
	/**
	 * Obtem o componente
	 * 
	 * @return BuilderForm
	 */
	public function getBuilderForm() {
		return $this->component;
	}
}
?>