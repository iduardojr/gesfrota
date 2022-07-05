<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Import;
use Gesfrota\Model\Domain\ImportItem;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Pattern\Upload;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\XFileBox;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Form\Controls\SearchBox;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\Equipment;

class ImportUploadForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param boolean $showAgencies
	 */
    public function __construct( Action $submit, Action $cancel, Action $seekAgency, Action $searchAgency, $showAgencies = false) {
	    $this->buildPanel('Minha Frota', 'Nova Importação');
		$form = $this->buildForm('import-upload-form');
		
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
		$input->setSpan(3);
		$form->buildField(null, $input, null, $fieldset);
		
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
	public function extract( Import $object ) {
		$data['desc'] = $object->getDescription();
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Import $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setDescription($data['desc']);
		$fileName = date('YmdHis') . '-' . uniqid() . '.csv';
		$dirRoot = DIR_ROOT . str_replace('/', DIRECTORY_SEPARATOR, Import::DIR);
		if ( ! move_uploaded_file($data['file']['tmp_name'], $dirRoot . $fileName) ) {
		    throw new \ErrorException('Unable to move upload file to target Directory');
		}
		$object->setFileName($fileName);
		$object->setFileSize($data['file']['size']);
		
		$file = fopen($dirRoot . $fileName, 'r', true);
		
		if ( $header = fgetcsv($file, 0, ";") ) {
		    $object->setHeader($this->tranform($header));
		}
		$object->getItems()->clear();
		while ($data = fgetcsv($file, 0, ";")) {
		    $item = new ImportItem($object, $this->tranform($data));
		    if ( $item->isVehicle() ) {
		        $rep = $em->getRepository(Vehicle::getClass());
		        $criteria = ['plate' => $item->getData()[1]];
		    } else {
		        $rep = $em->getRepository(Equipment::getClass());
		        $criteria = ['assetCode' => $item->getData()[6], 'responsibleUnit' => $object->getAgency()];
		    }
		    if ($ref = $rep->findOneBy($criteria) ) {
		        $item->setReference($ref);
		    }
		    $object->getItems()->add($item);
		}
		
		$object->setStatus(Import::PREPROCESSED);
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	private function tranform(array $data) {
	    foreach($data as $i => $val) {
	        $data[$i] = utf8_encode($val);
	    }
	    return $data;
	}

}
?>