<?php
namespace Gesfrota\View;

use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Table\ColumnSelect;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Form\Form;
use PHPBootstrap\Widget\Form\Inputable;
use PHPBootstrap\Common\ArrayIterator;
use PHPBootstrap\Widget\Table\DataSource;

abstract class TransferTable extends BuilderTable implements Inputable {

	/**
	 *
	 * @var ColumnSelect
	 */
	protected $select;

	/**
	 *
	 * @var array
	 */
	protected $value = [];

	/**
	 * @param string $name
	 * @param boolean $selectable
	 * @param DataSource $ds
	 */
	public function __construct($name, $selectable, DataSource $ds = null) {
		$this->select = new ColumnSelect($name);
		$table = $this;
		$this->select->setContextChecked(function ($data, $id) use ($table) {
			return in_array($id, $table->getValue());
		});
		parent::__construct($name);

		if ($selectable) {
			$this->addColumn($this->select);
		}
		$this->create();
		if ($ds) {
			$this->setDataSource($ds);
		}
	}
	
	/**
	 * Cria as colunas da tabela
	 */
	abstract protected function create();
	
	/**
	 *
	 * {@inheritdoc}
	 * @see \PHPBootstrap\Widget\AbstractWidget::setName()
	 */
	public function setName($name) {
		$this->name = $name . '-table';
		$this->select->setName($name);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \PHPBootstrap\Widget\AbstractWidget::getName()
	 */
	public function getName() {
		return $this->select->getName();
	}

	/**
	 *
	 * @see Inputable::prepare()
	 */
	public function prepare(Form $form) {
		$this->select->setForm($form);
	}

	/**
	 *
	 * @see Inputable::setValue()
	 */
	public function setValue($value) {
		$value = empty($value) ? [] : $value;
		if (! (is_array($value))) {
			throw new \InvalidArgumentException('value "' . $value .  '" is not array');
		}
		$this->value = $value;
	}

	/**
	 *
	 * @see Inputable::getValue()
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 *
	 * @param integer $span
	 * @throws \BadMethodCallException
	 */
	public function setSpan($span) {
		throw new \BadMethodCallException(__METHOD__ . ' not is support');
	}

	/**
	 *
	 * @param callback $transform
	 * @throws \BadMethodCallException
	 */
	public function setTransform($transform) {
		throw new \BadMethodCallException(__METHOD__ . ' not is support');
	}

	/**
	 *
	 * @see Inputable::valid()
	 */
	public function valid() {
		return true;
	}

	/**
	 *
	 * @see Inputable::getFailMessages()
	 */
	public function getFailMessages() {
		return new ArrayIterator([]);
	}
}
?>