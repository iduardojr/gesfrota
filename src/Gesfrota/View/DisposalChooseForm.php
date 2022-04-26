<?php
namespace Gesfrota\View;

use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\Disposal;
use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\ArrayDatasource;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Form\Controls\Fieldset;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\TabPane;
use PHPBootstrap\Widget\Nav\Tabbable;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Form\Controls\SearchBox;

class DisposalChooseForm extends AbstractForm {
	
	/**
	 * @param Action $next
	 * @param Action $cancel
	 * @param Action $seekAgency
	 * @param Action $searchAgency
	 * @param array $assets
	 * @param boolean $showAgencies
	 */
	public function __construct( Action $next, Action $cancel, Action $seekAgency, Action $searchAgency, array $assets, $showAgencies = false ) {
        $this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
		$form = $this->buildForm('disposal-choose-form');
		
		$general = new Fieldset('Nova Disposição');
		
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
			
			$form->buildField('Órgão', $input, null, $general);
		}
		
		$input = new TextBox('description');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Nome da Lista', $input, null, $general);
		
		$table = new DisposalAssetsTable('assets');
		$table->setDataSource(new ArrayDatasource($assets, 'id'));
		
		$form->register($table);
		$general->append($table);
			
		$tab = new Tabbable('disposal-tabs');
		$tab->setPlacement(Tabbable::Left);
		
		$tab->addItem(new NavLink('Seleção'), null, new TabPane($general));
		
		$link = new NavLink('Avaliação');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$link = new NavLink('Confirmação');
		$link->setDisabled(true);
		$tab->addItem($link);
		
		$form->append($tab);

		$form->buildButton('submit', 'Selecionar Ativos', $next);
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Disposal $object ) {
		$data['description'] = $object->getDescription();
		$data['assets'] = [];
		foreach ( $object->getAllAssets() as $item ) {
			$data['assets'][] = $item->getAsset()->getId();
		}
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Disposal $object, EntityManager $em ) {
		$data = $this->component->getData();
		$object->setDescription($data['description']);
		
		foreach ($data['assets'] as $key) {
			$asset = $em->find(FleetItem::getClass(), $key);
			$object->addAsset(new DisposalItem($asset));
		}
	}

}
?>