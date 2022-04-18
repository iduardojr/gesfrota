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

class DisposalChooseForm extends AbstractForm {
	
	/**
	 * @param Action $next
	 * @param Action $cancel
	 * @param array $assets
	 */
    public function __construct( Action $next, Action $cancel, array $assets ) {
        $this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
		$form = $this->buildForm('disposal-choose-form');
		
		$general = new Fieldset('Nova Disposição');
		
		$input = new TextBox('description');
		$input->setAutoComplete(true);
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$form->buildField('Descrição', $input, null, $general);
		
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