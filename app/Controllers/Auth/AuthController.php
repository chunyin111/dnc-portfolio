<?php 

namespace App\Controllers\Auth;

use App\Models\User;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

// use \Slim\Views\Twig as View;

class AuthController extends Controller{

	public function getSignOut($request, $response){
		$this->auth->logout();

		return $response->withRedirect($this->router->pathFor('auth.signin'));
	}
	public function getSignIn($request, $response){
		return $this->view->render($response, 'auth/signin.twig');
	}

	public function postSignIn($request, $response){
		$auth = $this->auth->attempt(
			$request->getParam('email'),
			$request->getParam('password')
		);
		
		if(!$auth){
			return $response->withRedirect($this->router->pathFor('auth.signin'));
		}

		$this->flash->addMessage('success', 'You has been sign in');
		return $response->withRedirect($this->router->pathFor('admin'));
	}

	public function getSignUp($request, $response){

		return $this->view->render($response, 'auth/signup.twig');
	}

	public function postSignUp($request, $response){


		$validation = $this->Validator->validate($request, [
			'email' => v::noWhitespace()->notEmpty()->email()->EmailAvailable(),
			'name' => v::notEmpty()->alpha(),
			'password' => v::noWhitespace()->notEmpty(),
		]);

		if($validation->failed()){
			$this->flash->addMessage('error', 'Invalid Input, Please try again!');
			return $response->withRedirect($this->router->pathFor('auth.signup'));
		}

		$user = User::create([
			'email' => $request->getParam('email'),
			'name' => $request->getParam('name'),
			'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),

		]);

		$this->auth->attempt($user->email, $request->getParam('password'));

		return $response->withRedirect($this->router->pathFor('admin'));
	}
}
?>