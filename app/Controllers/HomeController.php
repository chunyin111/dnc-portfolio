<?php 

namespace App\Controllers;

use \Slim\Views\Twig as View;

class HomeController extends Controller{

	public function index($request, $response){

		/*User::create([
			'email' => 'dummy@hotmail.com',
			'name' => 'dummy',
			'password' => 'qwe123',

		]);
		*/
		//$user = $this->db->table('users')->find(1);
		return $this->view->render($response, 'home.twig');
	}
}
?>