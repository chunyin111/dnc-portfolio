<?php 

namespace App\Controllers;

use App\Models\Photo;
use \Slim\Views\Twig as View;
use App\Models\ContactUs;
use App\Models\Posts;
use App\Models\Video;
use Respect\Validation\Validator as v;

class PhotoController extends Controller{

	public function index($request, $response){
	    $M_URL = $this->container->image_url;
	    $slide = Photo::where('status', 10)->latest('created_at')->get();
		$slide_data = array();
		foreach ($slide as $k => $v) {
			$slide_data[] = $v['attributes'];
		}
		foreach($slide_data as $li => $s){
			$slide_data[$li]['put_at'] = json_decode($s['put_at']);
			$slide_data[$li]['pic_name'] = $M_URL.$s['pic_name'];
		}
		
		$slide_pic = array();
		foreach($slide_data as $i => $p){
			if(in_array(1, $p['put_at'])){
				list($width, $height) = getimagesize($p['pic_name']);
				if($width > $height){ //validate landscape
					$slide_pic[$i] = $p;
				}
			}
		}

		//post new
		$data = array(); 
		$post = Posts::take(10)->latest('created_at')->get();
		foreach ($post as $key => $value) {
			$data[] = $value['attributes'];
		}
        if($data){
            $photo_id = array();
    		foreach($data as $li => $result){
    			$photo_id[] = json_decode($result['image']);
    			if($result['first_image']){
    				$fphoto = Photo::find($result['first_image']);
    				if($fphoto){
    				    $data[$li]['first_image'] = $M_URL.$fphoto->getOriginal('pic_name');
    				}
    			}
    		}
    
    		$photos = array();
    		foreach ($photo_id as $n => $ids) {
    			$photos[] = Photo::whereIn('id', $ids)->get();
    		}
    		
    		foreach ($photos as $l => $image) {
    			foreach($image as $o => $p){
    				$data[$l]['pictures'][] = $M_URL.$p['attributes']['pic_name'];
    			}
    		}
    		$baseURL = $this->container->base_url;
			
    		$resultHTML = "";
			$i = 0;
    		foreach ($data as $post_data) {
    		    if($i % 2 == 0){
    		    	$extraclass= "col-1-2";
    		    }
    			else{
    				$extraclass="col-1-2 f-right";
    			}
    		    $resultHTML .= "<div class=\"row\">
    		    					<article>
    				    				<div class=\"$extraclass\">
    				    					<img class=\"img-padding\" src= ".$post_data['first_image']." alt = \"\">
    				    				</div>
    				    				<div class= \"col-1-2\">
    				    					<div class=\"entry-content t-center\">
    				    						<h3>".$post_data['title']."</h3>
    				    						<p class=\"text-shorten\">".$post_data['description']."</p>
    				    						<a class=\"button\" href= ".$baseURL.'post-detail?id='.$post_data['id'].">Read More</a>
    				    					</div>
    				    				</div>
    			    				</article>
    		    				</div>";
    		   
    		    $i++;
    		}
        }
        $directory = $this->container->file_url;
        $video_data = Video::where('status', 10)->latest('created_at')->get();
        $vdata = array();
		foreach ($video_data as $keys => $values) {
			$vdata[] = $values['attributes'];
		}

		$json_object = array();
		foreach($vdata as $o => $u){
			if($u['category'] == 100){
				$json_object[$o]['title'] = $u['title'];
				$json_object[$o]['type'] = $u['type'];
				$json_object[$o]['youtube'] = $u['video'];
			}
			else{
				$json_object[$o]['title'] = $u['title'];
				$json_object[$o]['type'] = $u['type'];
				$json_object[$o]['vimeo'] = $u['video'];
				$json_object[$o]['poster'] = $directory.$u['poster'];
			}
		}
			
		return $this->container->view->render($response, 'home.twig', array('slide' => $slide_pic?$slide_pic:'', 'html_data' => isset($resultHTML)?$resultHTML:'', 'video'=> $json_object));
	}

	public function contact($request, $response){
		$data['services'] = array('Wedding-ceremony' => 1, 'Pre-wedding' => 2, 'Couple' => 3, 'Sister-hood' => 4, 'Family' => 5 , 'Portrait' => 6);
		return $this->container->view->render($response, 'contact.twig', array('data' => $data));
	}

	public function about($request, $response){
		return $this->container->view->render($response, 'about.twig');
	}

	public function contactus($request, $response){
		$data = (array)$request->getParsedBody();

		$validation = $this->Validator->validate($request, [
			'email' => v::noWhitespace()->notEmpty()->email(),
			'first' => v::notEmpty()->alpha(),
			'last' => v::notEmpty()->alpha(),
			'number' => v::notEmpty(),
			'message' => v::notEmpty(),
			'venue' => v::notEmpty(),
			'services' => v::notEmpty(),
		]);
		if($validation->failed()){
			$this->flash->addMessage('error', 'Invalid Input, Please try again!');
			return $response->withRedirect($this->router->pathFor('contact'));
		}


		date_default_timezone_set('Asia/Kuala_Lumpur'); 
		$date = date("Y-m-d H:i:s"); 

		$contactus = ContactUs::create([
			'email' => $request->getParam('email'),
			'venue' => $request->getParam('venue'),
			'first' => $request->getParam('first'),
			'last' => $request->getParam('last'),
			'number' => $request->getParam('number'),
			'message' => $request->getParam('message'),
			'services' => json_encode($request->getParam('services')),
			'created_at' => $date,
		]);

		if($contactus){
		   	$this->flash->addMessage('success', 'Success Submit form.');
			return $response->withRedirect($this->router->pathFor('contact'));
		}
	}

	public function get_post_detail($request, $response){
		$queryparam = $request->getQueryParams();
		$M_URL = $this->container->image_url;
		$post_id = $queryparam['id'];
		if($post_id){

			$post = Posts::Where('id', $post_id)->get();
			$data = '';
			foreach ($post as $key => $value) {
				$data = $value['attributes'];
			}
			$image_id[] = json_decode($data['image']);

			foreach ($image_id as $n => $ids) {
				$photos[] = Photo::whereIn('id', $ids)->get();
			}
			foreach ($photos as $l => $image) {
				foreach($image as $o => $p){
					$data['pictures'][] = $M_URL.$p['attributes']['pic_name'];
				}
			}
			$time = strtotime($data['created_at']);
			$data['created_at'] = date("M d, Y", $time);
			return $this->container->view->render($response, 'post-detail.twig', array('data' => $data));
		}
		else{
			return $response->withRedirect($this->router->pathFor('home'));
		}

	}
	
}
?>