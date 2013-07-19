<?php
namespace Sigmat\View\AdministrativeUnit;

use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Form\Controls\Decorator\Mask;
use PHPBootstrap\Validate\Pattern\Pattern;
use PHPBootstrap\Validate\Pattern\Email;
use PHPBootstrap\Widget\Action\Action;
use Sigmat\View\AbstractForm;
use Sigmat\Model\AdministrativeUnit\AdministrativeUnit;
use PHPBootstrap\Widget\Misc\Breadcrumb;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Form\Controls\Label;

/**
 * Formulario
 */
class AdministrativeUnitForm extends AbstractForm {
	
	/**
	 * Construtor
	 * 
	 * @param Action $submit
	 * @param Action $cancel
	 * @param AdministrativeUnit $parent
	 */
	public function __construct( Action $submit, Action $cancel, AdministrativeUnit $parent = null ) {
		$this->buildPanel('Administração', 'Gerenciar Unidades Administrativas');
		$form = $this->buildForm('administrative-unit-form');
		
		$this->buildBreadcrumb($parent);
		
		$input = new TextBox('acronym');
		$input->setSpan(2);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->addFilter('strtoupper');
		$this->buildField('Sigla', $input);
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$this->buildField('Nome', $input);
		
		$input = new TextBox('contact');
		$input->setSpan(7);
		$this->buildField('Responsável', $input);
		
		$input = new TextBox('email');
		$input->setSpan(7);
		$input->setPattern(new Email('Por favor, informe um e-mail'));
		$this->buildField('E-mail', $input);
		
		$input = new TextBox('phone');
		$input->setSpan(2);
		$input->setMask(Mask::PhoneBR);
		$input->setPattern(new Pattern(Pattern::PhoneBR, 'Por favor, informe um telefone'));
		$this->buildField('Telefone', $input);
		
		$this->buildButton('submit', 'Incluir', $submit);
		$this->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( AdministrativeUnit $object ) {
		$data['name'] = $object->getName();
		$data['acronym'] = $object->getAcronym();
		$data['contact'] = $object->getContact();
		$data['email'] = $object->getEmail();
		$data['phone'] = $object->getPhone();
		$this->buildBreadcrumb($object->getParent());
		$this->component->setData($data);
	}
	
	/**
	 * @param AdministrativeUnit $parent
	 */
	protected function buildBreadcrumb( AdministrativeUnit $parent = null ) {
		$breadcrumb = $this->component->getByName('breadcrumb');
		if ( $breadcrumb !== null ) {
			$this->component->remove($breadcrumb);
		}
		if ( $parent !== null ) {
			while ( $parent !== null ) {
				$parents[] = $parent->getParent() !== null ? $parent->getName() : $parent->getAcronym();
				$parent = $parent->getParent();
			}
			$breadcrumb = new Breadcrumb('breadcrumb', '/', false);
			foreach( array_reverse($parents) as $parent ) {
				$breadcrumb->addItem($parent);
			}
			$this->component->prepend(new ControlGroup(new Label('Unidade Superior'), $breadcrumb));
			return $breadcrumb;
		} 
		
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( AdministrativeUnit $object ) {
		$data = $this->component->getData();
		$object->setName($data['name']);
		$object->setAcronym($data['acronym']);
		$object->setContact($data['contact']);
		$object->setEmail($data['email']);
		$object->setPhone($data['phone']);
	}

}
?>