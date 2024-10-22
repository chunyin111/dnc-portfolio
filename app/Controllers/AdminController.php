<?php 

namespace App\Controllers;

use \Slim\Views\Twig as View;
use App\Models\Photo;
use App\Models\Posts;
use App\Models\ContactUs;
use App\Models\Video;
use Respect\Validation\Validator as v;

class AdminController extends Controller{

	public function index($request, $response){

		$data['category'] = self::WEDDING_CATEGORY;

		return $this->view->render($response, 'admin.twig', array('data' => $data));
	}

	public function upload($request, $response){

		$directory = $this->container->upload_directory;

		$data = (array)$request->getParsedBody();

		$validation = $this->Validator->validate($request, [
			'category' => v::notEmpty(),
			'title' => v::notEmpty(),
			'description' => v::notEmpty(),
		]);

		if($validation->failed()){
			$this->flash->addMessage('error', 'Invalid Input, Please try again!');
			return $response->withRedirect($this->router->pathFor('admin'));
		}

		$filename = array();

		if($data['html5_uploader_count'] > 0){
			foreach($data as $name => $file_details){
				$file = substr($name, 0, 15);
				if($file == "html5_uploader_"){
					$split = explode("_", $name);
					$file_name = substr($name, (16 + strlen($split[2])));
					$num = $split['2'];
					if($file_name == "tmpname"){
						$pic['attach_file'][$num]['tmp_name'] = $file_details;
					}elseif($file_name == "name"){
						$pic['attach_file'][$num]['name'] = $file_details;
					}
				}
			}

			foreach($pic['attach_file'] as $upload_file){
				
				$upload_data['category'] = $data['category'];
				$upload_data['put'] = array('3');
				$filename[] = $this->moveUploadedFile($directory, $upload_file, $upload_data);
			}
			if($filename == false){
				$this->flash->addMessage('warning', 'Upload image fail, try agn later');
				return $response->withRedirect($this->router->pathFor('admin'));
			}

		}

		if($filename){
			$directory = $this->container->file_url;
			$post_data['filename'] = $filename;

			$is_width = array();
			$is_height = array();
			foreach($filename as $file){
				$f_photo = Photo::find($file);
				$v = $f_photo->getAttributes();
				list($width, $height) = getimagesize($directory.$v['pic_name']);
				if ($width > $height) {
				   $is_width[] = $file;
				} 
				else{
				   $is_height[] = $file;
				}
			}
			if($is_width){	
				$random = array_rand($is_width);
				$post_data['first_image'] = $is_width[$random];
			}else{
				$random = array_rand($is_height);
				$post_data['first_image'] = $is_height[$random];
			}
		}
		//post data
		foreach($post = array('category', 'title', 'description') as $field){
			$post_data[$field] = $data[$field];
		}

		if($post_data){
			$post = $this->AddPostDB($post_data);
		}
		if($post){
			$this->flash->addMessage('success', 'Success upload image and post');
			return $response->withRedirect($this->router->pathFor('admin'));
		}
	}

	function moveUploadedFile($directory, $file, $upload_data)
	{
		$FromDir = $directory.'/temp'; 
	    $extension = pathinfo($file['tmp_name'], PATHINFO_EXTENSION);
	    $basename = bin2hex(random_bytes(8));
	    $filename = sprintf('%s.%0.8s', $basename, $extension);

	    if (!file_exists($FromDir)) {
			@mkdir($FromDir);
		}
		if (!file_exists($directory)) {
			@mkdir($directory);
		}
		$upload_data['file_size'] = filesize($FromDir.'/'.$file['tmp_name']);
		if(copy($FromDir.'/'.$file['tmp_name'] , $directory.'/'.$file['tmp_name'])){
	 		$last_id = $this->AddPhotoDB($file['tmp_name'], $upload_data);
	 	}

		foreach(self::THUMBNAIL as $nail){
			$this->generate_thumbnail($nail, $file['tmp_name'], sprintf('%s%s', $directory, self::THUMBNAIL_ENDPOINT[$nail]));
		}

	 	return $last_id;
	}


	function AddPhotoDB($filename, $data){

		$get_sort = Photo::where('category', $data['category'])->latest('id')->first();
		$sort = $get_sort['attributes']['sort'];

		if($sort == 0 || $sort == null){
			$next_sort = 1; 
		}
		else{
			$next_sort = $sort + 1;
		}

		$photo = Photo::create([
			'pic_name' => $filename,
			'status' => 10,
			'put_at' => json_encode($data['put']),
			'size' => $data['file_size'],
			'category' => $data['category'],
			'sort' => $next_sort,

		]);
		return $photo['attributes']['id'];
	}

	function AddPostDB($data){

		$post = Posts::create([
			'title' => $data['title'],
			'description' => $data['description'],
			'first_image' => $data['first_image']?$data['first_image']:'',
			'image' => isset($data['filename'])?json_encode($data['filename']):'',
			'category' => $data['category']?$data['category']:'',
			'status' => 10,
		]);
		
		return $post;
	}

	public function upload_image($request, $response){

		$data['put_at'] = array('Slide' => 1, 'Gallery' => 4);
		$data['category'] = self::WEDDING_CATEGORY;

		return $this->view->render($response, 'upload-image.twig', array('data' => $data));
	}

	public function only_upload_image($request, $response){

		$directory = $this->container->upload_directory;
		$data = (array)$request->getParsedBody();

		$validation = $this->Validator->validate($request, [
			'put' => v::notEmpty(),
			'category' => v::notEmpty(),
		]);

		if($validation->failed()){
			$this->flash->addMessage('error', 'Invalid Input, Please try again!');
			return $response->withRedirect($this->router->pathFor('upload-image'));
		}


		$filename = array();

		if($data['html5_uploader_count'] > 0){
			foreach($data as $name => $file_details){
				$file = substr($name, 0, 15);
				if($file == "html5_uploader_"){
					$split = explode("_", $name);
					$file_name = substr($name, (16 + strlen($split[2])));
					$num = $split['2'];
					if($file_name == "tmpname"){
						$pic['attach_file'][$num]['tmp_name'] = $file_details;
					}elseif($file_name == "name"){
						$pic['attach_file'][$num]['name'] = $file_details;
					}
				}
			}
			foreach($pic['attach_file'] as $upload_file){
				foreach($o = array('put','category') as $field){
					$upload_data[$field] = $data[$field];
				}
				$filename[] = $this->moveUploadedFile($directory, $upload_file, $upload_data);
			}
			if(!$filename){
				$this->flash->addMessage('warning', 'Upload image fail, try again later');
				return $response->withRedirect($this->router->pathFor('upload-image'));
			}
			else{
				$this->flash->addMessage('success', 'Success upload image');
				return $response->withRedirect($this->router->pathFor('upload-image'));
			}
		}
		else{
		    $this->flash->addMessage('warning', 'Upload image fail, try again later');
				return $response->withRedirect($this->router->pathFor('upload-image'));
		}
	}

	public function contact_list($request, $response){

		$svr = self::WEDDING_CATEGORY;
		$contact_data = ContactUs::all();
		$data = array();
		foreach ($contact_data as $k => $v) {
			$data[] = $v['attributes'];
		}

		foreach($data as $i => $value){
			$data[$i]['services'] = json_decode($value['services']);

			foreach($data[$i]['services'] as $key => $y){
				$data[$i]['svr'][] = $svr[$y];
			}
			$data[$i]['services'] = implode(', ', $data[$i]['svr']);
			$data[$i]['created_at'] = date('d/m/Y h:i:s a', strtotime($value['created_at']));
			unset($data[$i]['svr']);
		}
		return $this->view->render($response, 'admin-contact.twig', array('data' => $data));
	}

	public function image_list($request, $response){

		$image_data = Photo::all();
		$data = array();
		foreach ($image_data as $k => $v) {
			$data[] = $v['attributes'];
		}
		return $this->view->render($response, 'admin-picture.twig');
	}

	public function queue_image($request, $response){
		$directory = $this->container->file_url;

		$queryparam = $request->getQueryParams();
		if(isset($queryparam['id'])){
			$cid = $queryparam['id'];
			$category_id = $cid;
		}
		else{
			$category_id = 1;
		}

		if($category_id){
			$data = array();
			$retrieve_data = Photo::where('category', $category_id)->where('status', 10)->orderBy('sort', 'ASC')->get();
			foreach ($retrieve_data as $key => $value) {
				$data[] = $value['attributes'];
			}

    		foreach ($data as $k => $v) {
				$data[$k]['url_image'] = $directory.$v['pic_name'];
			}

			return $this->view->render($response, 'admin-queue.twig' , array('data' => $data, 'category' => $category_id));
		}
	}

	public function queue($request, $response)
	{
		$data = (array)$request->getParsedBody();

		$sort_data = explode('&', $data['sort']);
		$sorting = array();
		foreach ($sort_data as $key => $value) {
			$sorting[] = str_replace('item[]=', '', $value);
		}

		$order = 1;
		foreach($sorting as $item){
			Photo::where('id', $item)->where('category', $data['category'])->update(['sort' => $order]);
			$order++;
		}

	}

	public function remove_image($request, $response)
	{
		$directory = $this->container->remove_pic_url;
		$data = array();
		$retrieve_data = Photo::where('status', 10)->orderBy('created_at', 'DESC')->get();
		foreach ($retrieve_data as $key => $value) {
			$data[] = $value['attributes'];
		}

		foreach ($data as $k => $v) {
			$data[$k]['url_image'] = $directory.$v['pic_name'];
		}

		return $this->view->render($response, 'admin-remove-image.twig' , array('data' => $data));
	}

	public function update_image_status($request, $response)
	{
		$data = (array)$request->getParsedBody();

		$validation = $this->Validator->validate($request, [
			'image' => v::notEmpty(),
		]);

		if($validation->failed()){
			$this->flash->addMessage('error', 'Invalid Input, Please try again!');
			return $response->withRedirect($this->router->pathFor('admin.remove-image'));
		}

		if(is_array($data['image'])){
			foreach($data['image'] as $id){
				$update = Photo::where('id', $id)->update(['status' => 20]);
			}

			if($update){
				$this->flash->addMessage('success', 'Success Remove image');
				return $response->withRedirect($this->router->pathFor('admin.remove-image'));
			}
		}
	}

	public function video($request, $response){

		return $this->view->render($response, 'admin-video.twig');
	}

	public function add_video($request, $response){

		$directory = $this->container->upload_directory;

		$data = (array)$request->getParsedBody();
		$upload = $request->getUploadedFiles();

	    $uploadedFile = $upload['poster'];
	    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
	        $filename = $this->movetoUploadedFile($directory, $uploadedFile);
	    }
		$validation = $this->Validator->validate($request, [
			'category' => v::notEmpty(),
			'title' => v::notEmpty(),
			'video' => v::notEmpty(),
		]);

		if($validation->failed()){
			$this->flash->addMessage('error', 'Invalid Input, Please try again!');
			return $response->withRedirect($this->router->pathFor('admin.video'));
		}

		$video = Video::create([
			'title' => $data['title'],
			'status' => 10,
			'category' => $data['category'],
			'video' => $data['video'],
			'poster' => $filename,
			'type' => 'text/html',
		]);

		if($video){
			$this->flash->addMessage('success', 'Success Add video');
			return $response->withRedirect($this->router->pathFor('admin.video'));
		}
	}
}
?>