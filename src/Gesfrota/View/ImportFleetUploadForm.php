<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\ImportFleet;
use Gesfrota\Model\Domain\ImportFleetItem;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Pattern\Upload;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\XFileBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Misc\Well;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use Gesfrota\Model\Domain\Vehicle;

class ImportFleetUploadForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param boolean $showAgencies
	 */
    public function __construct( Action $submit, Action $cancel, Action $seekAgency, Action $searchAgency, $showAgencies = false) {
	    $this->buildPanel('Minha Frota', 'Nova Importação');
		$form = $this->buildForm('import-fleet-upload-form');
		
		$fieldset = new Fieldset('Enviar Arquivo para Importação');
		
		if ($showAgencies) {
		    $modal = new Modal('agency-search', new Title('Órgãos', 3));
		    $modal->setWidth(600);
		    $modal->addButton(new Button('Cancelar', new TgModalClose()));
		    $form->append($modal);
		    
		    $input = [];
		    $input[0] = new TextBox('agency-id');
		    $input[0]->setSuggestion(new Seek($seekAgency));
		    $input[0]->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		    $input[0]->setSpan(1);
		    
		    $input[1] = new SearchBox('agency-name', $searchAgency, $modal);
		    $input[1]->setEnableQuery(false);
		    $input[1]->setSpan(6);
		    
		    $form->buildField('Órgão', $input, null, $fieldset);
		}
		
		$input = new TextBox('desc');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->setValue('Importação feita na ' . ucfirst(utf8_encode(strftime('%A, %d %B %G %T', strtotime('now')))));
		$form->buildField('Descrição', $input, null, $fieldset);
		
		$input = new XFileBox('file');
		$input->setPlaceholder('Escolha um arquivo .csv');
		$input->setPattern(new Upload(['text/csv' => 'csv'], 'Informe um arquivo .csv'));
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Arquivo', $input, null, $fieldset);
		
		$input = new CheckBox('auto', 'Importar automaticamente os veículos, se os dados estiverem completos');
		$form->buildField(null, $input, null, $fieldset);
		
		$text[]= '<p>O arquivo deve ter o tamanho máximo de <code>'. ini_get('upload_max_filesize') . 'B</code>
                     e os seus valores devem ser separados por <code>;</code> e delimitados por <code>"</code>.</p>';
		$text[]= '<p>A primeira linha é o cabeçalho do arquivo correspondendo ao nome dos campos 
                     e as demais linhas é um registro com os seus respectivos valores formatados da seguinte forma e sequência:</p>';
		$text[]= '<dl class="dl-horizontal">
                     <dt>Placa</dt>                 <dd>AAA9*999</dd>
                     <dt>[FIPE]</dt>                <dd>999999-9</dd>   
                     <dt>[Cod. Patrimonial]</dt>    <dd><i>alfanúmerico</i></dd>
                     <dt>Modelo</dt>                <dd><i>alfanumérico</i></dd>
                     <dt>Categoria</dt>             <dd>EQUIPAMENTO|VEICULO<i>&lt;alfanumérico&gt;</i></dd>
                     <dt>Fabricante</dt>            <dd><i>alfanumérico</i></dd>
                     <dt>Tipo da Frota</dt>         <dd>PROPRIA|LOCADA|ACAUTELADA|CEDIDA</dd>
                     <dt>Renavam</dt>               <dd><i>numérico</i></dd>
                     <dt>Chassi / Nº de Série</dt>  <dd><i>alfanúmerico</i></dd>
                     <dt>Motor</dt>                 <dd>GASOLINA|ETANOL|FLEX|DIESEL</dd>
                     <dt>Ano Fabricação</dt>        <dd>9999</dd>
                     <dt>Ano Modelo</dt>            <dd>9999</dd>
                     <dt>Hodômetro</dt>             <dd><i>numérico</i></dd>
                     <dt>CNPJ Proprietário</dt>     <dd><i>numérico</i></dd>
                     <dt>Razão Social</dt>          <dd><i>alfanumérico</i></dd>
                  </dl>';
		$text[]= '<p>As seguintes definições representam um caractere 
                     alfabético <code>A</code>, numérico <code>9</code> e alfanumérico <code>*</code>. 
                     As colunas entre <code>[]</code> são opcionais, sendo possível o arquivo ter 13 ou 15 colunas (arquivo reduzido ou expandido).</p>';

		$form->buildField(null, new Well('info', new Panel(implode('', $text))), null, $fieldset);
		
		$tab = new Tabbable('import-tabs');
		$tab->setPlacement(Tabbable::Left);
		$tab->addItem(new NavLink('Upload do Arquivo'), null, new TabPane($fieldset));
		
		$link = new NavLink('Pré-processamento');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$form->append($tab);

		$form->buildButton('submit', 'Executar', $submit);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( ImportFleet $object ) {
		$data['desc'] = $object->getDescription();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( ImportFleet $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setDescription($data['desc']);
		$fileName = date('YmdHis') . '-' . uniqid() . '.csv';
		$dirRoot = DIR_ROOT . str_replace('/', DIRECTORY_SEPARATOR, ImportFleet::DIR);
		if ( ! move_uploaded_file($data['file']['tmp_name'], $dirRoot . $fileName) ) {
		    throw new \ErrorException('Unable to move upload file to target Directory');
		}
		$object->setFileName($fileName);
		$object->setFileSize($data['file']['size']);
		
		$file = fopen($dirRoot . $fileName, 'r', true);
		
		if ( $line = fgetcsv($file, 0, ";") ) {
		    $object->setHeader($this->transform($line));
		}
		$object->getItems()->clear();
		if ($object->getId() == 0) {
		    $em->persist($object);
		}
		$em->flush($object);
		if ($data['auto']) {
    		while ($line = fgetcsv($file, 0, ";")) {
    		    $item = new ImportFleetItem($object, $this->transform($line));
    		    if (! $item->toPreProcess($em) && $item->isVehicle() ) {
    		        $vehicle = $item->toTransform($em);
    		        if ($this->vehicleValid($vehicle)) {
    		            $em->persist($vehicle);
    		            $em->flush($vehicle);
    		            $item->setReference($vehicle);
    		        }
    		    }
    		    $em->persist($item);
    		    $em->flush($item);
    		    $em->detach($item);
    		    if ($vehicle) {
    		        $em->detach($vehicle);
    		    }
    		}
		} else {
		    while ($line = fgetcsv($file, 0, ";")) {
		        $item = new ImportFleetItem($object, $this->transform($line));
		        $item->toPreProcess($em);
		        
		        $em->persist($item);
		        $em->flush($item);
		        $em->detach($item);
		    }
		}
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	private function transform(array $data) {
	    foreach($data as $i => $val) {
	        $data[$i] = utf8_encode($val);
	    }
	    return $data;
	}
	
	/**
	 * @param Vehicle $vehicle
	 * @return boolean
	 */
	private function vehicleValid(Vehicle $vehicle) {
	    if ( empty($vehicle->getPlate()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getModel()->getId()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getAssetCode()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getFleet()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getRenavam()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getVin()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getEngine()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getYearManufacture()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getYearModel()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getOwner()) ) {
	        return false;
	    }
	    if ( empty($vehicle->getResponsibleUnit()) ) {
	        return false;
	    }
	    return true;
	}

}
?>