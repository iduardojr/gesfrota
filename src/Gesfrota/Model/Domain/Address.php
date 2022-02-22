<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Util\CURL;

/**
 * Endereço
 * @Embeddable
 */
class Address {
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $street;
	
	/**
	 * @Column(type="string")
	 * @var integer
	 */
	protected $number;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $complement;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $district;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $city;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $state;
	
	/**
	 * @Column(type="string", name="zip_code")
	 * @var string
	 */
	protected $zipCode;
	
	/**
	 * Construtor
	 * 
	 * @param string $zipCode
	 */
	public function __construct( $zipCode = null ) {
		if ( func_num_args() ) {
			$data = self::find($zipCode);
			$this->setZipCode($zipCode);
			$this->setStreet($data['street']);
			$this->setDistrict($data['district']);
			$this->setCity($data['city']);
			$this->setState($data['state']);
		}
	}
	
	/**
	 * Obtem $street
	 *
	 * @return string
	 */
	public function getStreet() {
		return $this->street;
	}

	/**
	 * Obtem $number
	 *
	 * @return number
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * Obtem $complement
	 *
	 * @return string
	 */
	public function getComplement() {
		return $this->complement;
	}

	/**
	 * Obtem $district
	 *
	 * @return string
	 */
	public function getDistrict() {
		return $this->district;
	}

	/**
	 * Obtem $city
	 *
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Obtem $state
	 *
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * Obtem $zipCode
	 *
	 * @return string
	 */
	public function getZipCode() {
		return $this->zipCode;
	}

	/**
	 * Atribui $street
	 *
	 * @param string $street
	 */
	public function setStreet( $street ) {
		$this->street = $street;
	}

	/**
	 * Atribui $number
	 *
	 * @param number $number
	 */
	public function setNumber( $number ) {
		$this->number = (int) $number;
	}

	/**
	 * Atribui $complement
	 *
	 * @param string $complement
	 */
	public function setComplement( $complement ) {
		$this->complement = $complement;
	}

	/**
	 * Atribui $district
	 *
	 * @param string $district
	 */
	public function setDistrict( $district ) {
		$this->district = $district;
	}

	/**
	 * Atribui $city
	 *
	 * @param string $city
	 */
	public function setCity( $city ) {
		$this->city = $city;
	}

	/**
	 * Atribui $state
	 *
	 * @param string $state
	 */
	public function setState( $state ) {
		$this->state = $state;
	}

	/**
	 * Atribui $zipCode
	 *
	 * @param string $zipCode
	 */
	public function setZipCode( $zipCode ) {
		$this->zipCode = $zipCode;
	}

	/**
	 * Converte o endereço em uma string
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->getStreet() . ($this->getNumber() > 0 ? ", " . $this->getNumber() : '')
		. " " . $this->getComplement() . " " . $this->getDistrict()
		. "\n" . $this->getCity() . "/" . $this->getState()
		. "\nCEP " . $this->getZipCode();
	}
	
	/**
	 * Obtem as unidades federativas descritivas
	 * 
	 * @return	array
	 */
	public static function getUFDescription() {
		return array('AC' => 'Acre',
					 'AL' => 'Alagoas',
					 'AM' => 'Amazonas',
					 'AP' => 'Amap&aacute;',
					 'BA' => 'Bahia',
				 	 'CE' => 'Cear&aacute;',
					 'DF' => 'Distrito Federal',
					 'ES' => 'Esp&iacute;rito Santo',
					 'GO' => 'Goi&aacute;s',
				 	 'MA' => 'Maranh&atilde;o',
					 'MG' => 'Minas Gerais',
					 'MS' => 'Mato Grosso do Sul',
					 'MT' => 'Mato Grosso',
					 'PA' => 'Par&aacute;',
					 'PB' => 'Para&iacute;ba',
					 'PE' => 'Pernambuco',
				 	 'PI' => 'Piau&iacute;',
					 'PR' => 'Paran&aacute;',
					 'RJ' => 'Rio de Janeiro',
					 'RN' => 'Rio Grande do Norte',
					 'RO' => 'Rond&ocirc;nia',
					 'RR' => 'Roraima',
					 'RS' => 'Rio Grande do Sul',
					 'SC' => 'Santa Catarina',
					 'SE' => 'Sergipe',
					 'SP' => 'S&atilde;o Paulo',
					 'TO' => 'Tocantins');
	}
	
	/**
	 * Obtem as unidades federativas
	 *
	 * @return	array
	 */
	public static function getUF() {
		$uf = array_keys(self::getUFDescription());
		return array_combine($uf, $uf);
	}
	
	/**
	 * Busca um endereço a partir do cep
	 * 
	 * @param string $zipCode
	 * @return array
	 */
	public static function find( $zipCode ) {
		$url = 'http://shopping.correios.com.br/WBM/include/script/remoteBuscaEndereco.aspx';
		$url.= '?F=selEndereco&P0='.$zipCode;
		$content = CURL::connect($url);
		$pattern = '/<textarea name="jsrs_Payload" id="jsrs_Payload">(.+)<\/textarea>/';
		$data = array();
		preg_match($pattern, $content, $data);
		$data = explode(':-:', $data[1]);
		return array('street' => $data[4],
					 'district' => $data[3],
					 'city' => $data[2],
					 'state' => $data[1]);
	}
}
?>