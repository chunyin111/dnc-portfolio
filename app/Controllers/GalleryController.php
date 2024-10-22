<?php 

namespace App\Controllers;

use App\Models\Photo;
use \Slim\Views\Twig as View;

class GalleryController extends Controller{
	public function get_wedding_ceremony($request, $response){

		$thumb_dir = $this->container->thumb_url;
		$directory = $this->container->file_url;
		$photo_data = Photo::where('category', 1)->where('status', 10)->orderBy('sort', 'ASC')->get();

		$data = array();
		foreach ($photo_data as $k => $v) {
			$data[] = $v['attributes'];
		}
		foreach ($data as $key => $value) {
			$data[$key]['thumb_image'] = $thumb_dir.$value['pic_name'];
			$data[$key]['url_image'] = $directory.$value['pic_name'];
		}
		
		return $this->view->render($response, 'wedding.twig', array('data' => $data));
	}

	public function get_pre_wedding($request, $response){

		$thumb_dir = $this->container->thumb_url;
		$directory = $this->container->file_url;
		$photo_data = Photo::where('category', 2)->where('status', 10)->orderBy('sort', 'ASC')->get();

		$data = array();
		foreach ($photo_data as $k => $v) {
			$data[] = $v['attributes'];
		}
		foreach ($data as $key => $value) {
			$data[$key]['thumb_image'] = $thumb_dir.$value['pic_name'];
			$data[$key]['url_image'] = $directory.$value['pic_name'];
		}
		return $this->view->render($response, 'pre-wedding.twig', array('data' => $data));
	}

	public function get_couple($request, $response){

		$thumb_dir = $this->container->thumb_url;
		$directory = $this->container->file_url;
		$photo_data = Photo::where('category', 3)->where('status', 10)->orderBy('sort', 'ASC')->get();

		$data = array();
		foreach ($photo_data as $k => $v) {
			$data[] = $v['attributes'];
		}
		foreach ($data as $key => $value) {
			$data[$key]['thumb_image'] = $thumb_dir.$value['pic_name'];
			$data[$key]['url_image'] = $directory.$value['pic_name'];
		}
		return $this->view->render($response, 'couple.twig', array('data' => $data));
	}

	public function get_sisterhood($request, $response){

		$thumb_dir = $this->container->thumb_url;
		$directory = $this->container->file_url;
		$photo_data = Photo::where('category', 4)->where('status', 10)->orderBy('sort', 'ASC')->get();

		$data = array();
		foreach ($photo_data as $k => $v) {
			$data[] = $v['attributes'];
		}
		foreach ($data as $key => $value) {
			$data[$key]['thumb_image'] = $thumb_dir.$value['pic_name'];
			$data[$key]['url_image'] = $directory.$value['pic_name'];
		}
		return $this->view->render($response, 'sisterhood.twig', array('data' => $data));
	}

	public function get_family($request, $response){

		$thumb_dir = $this->container->thumb_url;
		$directory = $this->container->file_url;
		$photo_data = Photo::where('category', 5)->where('status', 10)->orderBy('sort', 'ASC')->get();

		$data = array();
		foreach ($photo_data as $k => $v) {
			$data[] = $v['attributes'];
		}
		foreach ($data as $key => $value) {
			$data[$key]['thumb_image'] = $thumb_dir.$value['pic_name'];
			$data[$key]['url_image'] = $directory.$value['pic_name'];
		}
		return $this->view->render($response, 'family.twig', array('data' => $data));
	}

	public function get_portrait($request, $response){

		$thumb_dir = $this->container->thumb_url;
		$directory = $this->container->file_url;
		$photo_data = Photo::where('category', 6)->where('status', 10)->orderBy('sort', 'ASC')->get();

		$data = array();
		foreach ($photo_data as $k => $v) {
			$data[] = $v['attributes'];
		}
		foreach ($data as $key => $value) {
			$data[$key]['thumb_image'] = $thumb_dir.$value['pic_name'];
			$data[$key]['url_image'] = $directory.$value['pic_name'];
		}
		return $this->view->render($response, 'portrait.twig', array('data' => $data));
	}
}
?>