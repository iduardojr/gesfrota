<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Util\CURL;

/**
 * Lugar
 * @Embeddable
 */
class Place implements \ArrayAccess {
	
	/**
	 * Parametros padrões
	 * @var	string
	 */
	private static $PARAMETERS = [];
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $place;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;
	
	public function __construct($id, $description) {
		$this->setPlace($id);
		$this->setDescription($description);
	}
	
	/**
	 * @param array $defaults
	 */
	public static function setParameters(array $defaults) {
		if (isset($defaults['region']) && !isset($defaults['components'])) {
			$defaults['components'] = 'country:' . $defaults['region'];
		}
		Place::$PARAMETERS = $defaults;
	}

	/**
	 * @return string
	 */
	public function getPlace() {
		return $this->place;
	}

	/**
	 * @param string $id
	 */
	public function setPlace($id) {
		$this->place = $id;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}
	
	public function __toString() {
		return $this->description;
	}

	/**
	 * 
	 * @param string $query
	 * @param array $params
	 * @return Place[]
	 */
	public static function autocomplete($query, array $params = []) {
		$response = self::execute(__FUNCTION__, array_merge($params, ['input' => $query]), 'predictions');
		$places = [];
		foreach($response as $dto) {
			$places[] = new Place($dto->place_id, $dto->description);
		}
		return $places;
	}
	
	/**
	 * @param string $query
	 * @param array $params
	 * @return Place[]
	 */
	public static function textsearch($query, array $params = []) {
		$response = self::execute(__FUNCTION__, array_merge($params, ['query' => $query]), 'results');
		$places = [];
		foreach($response as $dto) {
			$places[] = new Place($dto->place_id, $dto->name);
		}
		return $places;
	}
	
	/**
	 * @param Place $obj
	 * @return \stdClass
	 */
	public static function details(Place $obj) {
		return self::execute(__FUNCTION__, ['place_id' => $obj->place], 'result');
	}
	
	/**
	 * @param string $service
	 * @param array $params
	 * @param string $fieldReturn
	 * @throws InvalidRequest
	 * @throws RequestDenied
	 * @throws \ErrorException
	 * @return array
	 */
	private static function execute($service, array $params, $fieldReturn) {
		$params = array_merge(Place::$PARAMETERS, $params);
		$url = 'https://maps.googleapis.com/maps/api/place/';
		if (getenv('APPLICATION_ENV') != 'development') {
			$output = CURL::connect($url . $service . '/json?' . http_build_query($params));
		} else {
			$output = file_get_contents($url . $service . '/json?' . http_build_query($params));
		}
		$response = json_decode($output);
		switch ($response->status) {
			case 'OK': 
				return $response->{$fieldReturn};
				
			case 'ZERO_RESULTS':
				return [];
				
			case 'INVALID_REQUEST':
				$exception = 'Request was malformed, generally due to missing required query parameter';
				if (isset($response->error_message)) {
					$exception = $response->error_message;
				}
				throw new InvalidRequest($exception);
				
			case 'REQUEST_DENIED':
				$exception = 'Request denied, generally due to missing payment';
				if (isset($response->error_message)) {
					$exception = $response->error_message;
				}
				throw new RequestDenied($exception);
				
			case 'UNKNOWN_ERROR':
				$exception = 'Unknown error';
				if (isset($response->error_message)) {
					$exception = $response->error_message;
				}
				throw new \ErrorException($exception);
		}
		
	}
	public function offsetGet($offset) {
		return $this->offsetExists($offset) ?  $this->{$offset} : null;
	}

	public function offsetExists($offset) {
		return property_exists($this, $offset);
	}

	public function offsetUnset($offset) {
		if ($this->offsetExists($offset)) {
			$this->{$offset} = null;
		}
	}

	public function offsetSet($offset, $value) {
		if (! is_null($offset) && $this->offsetExists($offset)) {
			$this->{$offset} = $value;
		}
	}


}

class InvalidRequest extends \ErrorException {
	
}

class RequestDenied extends \ErrorException {
	
}