<?php 

namespace App\Controllers\Auth;

use App\Models\User;
use App\Controllers\controller;
use Respect\Validation\Validator as v;

// use \Slim\Views\Twig as View;

class PasswordController extends Controller{

	public function getChangePassword($request, $response){
		return $this->view->render($response, 'auth/password/change.twig');
	}

	public function postChangePassword($request, $response){

		$validation = $this->Validator->validate($request, [

			'password_old' => v::noWhitespace()->notEmpty()->matchesPassword($this->auth->user()->password),
			'password' => v::noWhitespace()->notEmpty()
		]);

		if($validation->failed()){
			return $response->withRedirect($this->router->pathFor('auth.password.change'));
		}

		$this->auth->user()->setPassword($request->getParam('password'));

		$this->flash->addMessage('success', 'Your Password was Changed.');
		return $response->withRedirect($this->router->pathFor('admin'));
	}
}
?>