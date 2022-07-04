<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Sys\Import;
use Gesfrota\Model\Sys\ImportItem;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\Equipment;

class ImportPreProcessForm extends AbstractForm {
	
	/**
	 * @param array $data
	 * @param array $options
	 * @param Action $submit
	 * @param Action $cancel
	 */
    public function __construct(Action $submit, Action $cancel, Import $import, array $data, array $options ) {
		$this->buildPanel('Sistema', 'Gerenciar Importações');
		$form = $this->buildForm('import-preprocess-form');
		
		$fieldset = new Fieldset('Pré-processamento <small>'. $import->getDescription(). '</small>');
		
		foreach($data as $item) {
    		$input = new ComboBox($this->toFieldName($item['term']));
    		$input->setOptions($options);
    		$input->setValue($item['suggest']);
    		$input->setSpan(3);
    		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
    		$form->buildField($item['term'], $input, null, $fieldset);
		}
		
		$tab = new Tabbable('import-tabs');
		$tab->setPlacement(Tabbable::Left);
		
		$link = new NavLink('Upload');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$tab->addItem(new NavLink('Pré-processamento'), null, new TabPane($fieldset));
		
		$link = new NavLink('Transformação');
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
		
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Import $object, EntityManager $em ) {
		$data = $this->component->getData();
		unset($data['desc']);
		foreach ($object->getItems() as $item) {
		    $item instanceof ImportItem;
		    $fieldName = $this->toFieldName($item->getGroupBy());
		    if (isset($data[$fieldName])) {
		        $item->setAgency($em->find(Agency::getClass(), $data[$fieldName]));
		    }
		    if ( $item->isVehicle() ) {
		        $rep = $em->getRepository(Vehicle::getClass());
		        $criteria = ['plate' => $item->getData()[1]];
		    } else {
		        $rep = $em->getRepository(Equipment::getClass());
		        $criteria = ['assetCode' => $item->getData()[6], 'responsibleUnit' => $item->getAgency()];
		    }
		    if ($ref = $rep->findOneBy($criteria) ) {
		        $item->setReference($ref);
		    }
		}
		$object->setStatus(Import::PREPROCESSED);
	}
	
	/**
	 * @param string $groupBy
	 * @return string
	 */
	protected function toFieldName($groupBy) {
	    return str_replace(' ', '_', $groupBy);
	}
	
	/**
	 * @param string $fieldName
	 * @return string
	 */
	protected function toGroupBy($fieldName) {
	    return str_replace('_', ' ', $fieldName);
	}
	
}
?>