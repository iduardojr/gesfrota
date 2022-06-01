<?php
namespace Gesfrota\Model\Listener;

use Gesfrota\Model\Domain\User;
use Gesfrota\Util\Crypt;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class UserListener {
	
	/**
	 * @param User $user
	 * @postUpdate
	 * @prePersist
	 */
	public function resetPassword(User $user) { 
		if ( $user->isChangePassword() && getenv('APPLICATION_ENV') != 'development' ) {
			
			$password = Crypt::decode($user->getPassword());
			
			$mailer = new PHPMailer(true);
			$mailer->CharSet='UTF-8';
			$mailer->setLanguage('br');
			$mailer->setFrom('noreply@gesfrota.com.br', 'Gesfrota');
			$mailer->addAddress($user->getEmail(), $user->getName());
			
			$mailer->isHTML(true);
			$mailer->Subject = 'Redefinição de senha';
			$mailer->Body    = '<p>Olá ' .$user->getFirstName() . ', </p>';
			$mailer->Body   .= '<p>Sua senha de acesso ao <a href="https://' . $_SERVER['HTTP_HOST']. '" target="_blank">Sistema Gesfrota</a> foi redefinida para: ' . $password . '</p>';
			$mailer->Body   .= '<p>É importante que você acesse sua conta e altere sua senha.</p>';
			$mailer->Body   .= '<p>Obrigado<br /> Equipe Gesfrota</p>';
			
			$mailer->send();
				
		}
	}
	
}
