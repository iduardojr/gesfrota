<?php
namespace Sigmat\View;

use PHPBootstrap\Widget\Table\DataSource;

/**
 * Datasource vazio
 */
class EmptyDatasource implements DataSource {
	
	/**
	 * @see DataSource::getIdentify()
	 */
	public function getIdentify() {
		return null;
	}

	/**
	 * @see DataSource::fetch()
	 */
	public function fetch() {
		return array();
	}

	/**
	 * @see DataSource::next()
	 */
	public function next() {
		return false;
	}

	/**
	 * @see DataSource::getSort()
	 */
	public function getSort() {
		return null;
	}

	/**
	 * @see DataSource::getOrder()
	 */
	public function getOrder() {
		return self::Asc;
	}

	/**
	 * @see DataSource::getLimit()
	 */
	public function getLimit() {
		return 0;
	}

	/**
	 * @see DataSource::getOffset()
	 */
	public function getOffset() {
		return 0;
	}

	/**
	 * @see DataSource::getTotal()
	 */
	public function getTotal() {
		return 0;
	}

	/**
	 * @see DataSource::reset()
	 */
	public function reset() {
		return null;
	}

	/**
	 * @see DataSource::__get()
	 */
	public function __get( $name ) {
		return null;
	}
	
}
?>