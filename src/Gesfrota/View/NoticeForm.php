<?php
namespace Gesfrota\View;

use Gesfrota\Model\Sys\Notice;
use Gesfrota\View\Widget\AbstractForm;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\RichText;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Embed;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalConfirm;
use PHPBootstrap\Widget\Modal\TgModalLoad;
use PHPBootstrap\Widget\Modal\TgModalOpen;

class NoticeForm extends AbstractForm {
	
	/**
	 * @param Action $submit
	 * @param Action $cancel
	 * @param Action $remove
	 * @param Action $views
	 */
    public function __construct( Action $submit, Action $cancel, Action $remove = null, Action $views = null ) {
		$this->buildPanel('Comunicação', 'Gerenciar Notificações');
		$form = $this->buildForm('notice-form');
		$form->setStyle(null);
		
		$main= new Box(10);
		$content = new BuilderForm('notice-main-form');
		$main->append($content);
		
		$input = new TextBox('title');
		$input->setSpan(12);
		$input->setRequired(new Required(null, 'Por favor, preencha esse campo'));
		$input->setPlaceholder('Título do aviso');
		$content->buildField('Título', $input, true);
		$content->unregister($input);
		$form->register($input);
		
		$input = new RichText('body', RichText::Advanced);
		$input->setRows(20);
		$input->setSpan(12);
		$main->append($input);
		$form->register($input);
		
		$aside= new Box(2);
		
		$info = new BuilderForm('notice-aside-form');
		$info->setStyle(null);
		$info->append(new Title('Publicação', 5));
		
		$aside->append($info);
		
	    $modal = new Modal('modal-views-read', new Title('Lido por', 3));
	    $modal->setWidth(850);
	    $form->append($modal);
	    
		$input = new TextBox('views');
		$input->setDisabled(true);
		$input->setSpan(12);
		$embed = $input;
		if ($views) {
		    $input->setSpan(9);
		    $embed = new Embed([$input, new Button([new Icon('icon-eye-open'),'⁣'], new TgModalLoad($views, $modal))]);
		}
		$info->buildField('Lido por', $embed, false);
		$info->unregister($input);
		$form->register($input);
		
		$input = new TextBox('created-at');
		$input->setSpan(12);
		$input->setDisabled(true);
		$info->buildField('Criado em', $input, false);
		$info->unregister($input);
		$form->register($input);
		
		$input = new TextBox('updated-at');
		$input->setSpan(12);
		$input->setDisabled(true);
		$info->buildField('Atualizado em', $input, false);
		$info->unregister($input);
		$form->register($input);
		
		$input = new ComboBox('active');
		$input->setSpan(12);
		$input->setOptions([1 => 'Publicado', -1 => 'Não Publicado']);
		$info->buildField('Status', $input, false);
		$info->unregister($input);
		$form->register($input);
		
		$form->append(new Row(true, [$main, $aside]));
		
		$form->buildButton('submit', 'Incluir', $submit);
		if ($remove) {
		    $confirm = new Modal('modal-remove-confirm', new Title('Confirme', 3));
		    $confirm->setBody(new Paragraph('Você deseja excluir definitivamente esta Disposição?'));
		    $confirm->setWidth(350);
		    $confirm->addButton(new Button('Ok', new TgModalConfirm(), Button::Primary));
		    $confirm->addButton(new Button('Cancelar', new TgModalClose()));
		    $this->panel->append($confirm);
		    
		    $form->buildButton('remove', 'Excluir', new TgModalOpen($confirm, new TgLink($remove)));
		}
		$form->buildButton('cancel', 'Cancelar', $cancel);
	}
	
	/**
	 * @see AbstractForm::extract()
	 */
	public function extract( Notice $object ) {
		$data['title'] = $object->getTitle();
		$data['body'] = $object->getBody();
		$data['active'] = $object->getActive() ? 1 : -1;
		$data['views'] = $object->getReadAmount() . ' usuário' . ( $object->getReadAmount() > 1 ? 's' : '');
		$data['created-at'] = $object->getCreatedAt()->format('d/m/Y H:i:s');
		$data['updated-at'] = $object->getUpdatedAt()->format('d/m/Y H:i:s');
		
		$remove = $this->component->getButtonByName('remove');
		if ($remove) {
		    $remove->setDisabled(! $object->canDelete());
    	}
		$this->component->setData($data);
	}

	/**
	 * @see AbstractForm::hydrate()
	 */
	public function hydrate( Notice $object ) {
		$data = $this->component->getData();
		$object->setTitle($data['title']);
		$object->setBody($data['body']);
		$object->setActive($data['active'] == 1);
	}

}
?>