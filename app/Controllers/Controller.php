<?php

namespace App\Controllers;
use Exception;
use Slim\Http\UploadedFile;

class Controller{

	protected $container;

	const THUMBNAIL = array(70, 340);
	const THUMBNAIL_ENDPOINT = array(70 => "/70", 340 => "/340");

	const WEDDING_CATEGORY = array(
		'Wedding-ceremony' => 1, 
		'Pre-wedding' => 2, 
		'Couple' => 3, 
		'Sister-hood' => 4, 
		'Family' => 5 , 
		'Portrait' => 6
	);

	public function __construct($container){
		$this->container = $container;
	}

	public function __get($property){
		if($this->container->{$property}){
			return $this->container->{$property};
		}
	}

	public function generate_thumbnail($size, $filename, $destination){

		$from_img_url = $this->container->file_url.'/'.$filename;
		list($originalWidth, $originalHeight) = getimagesize($from_img_url);
		$image_info = getimagesize($from_img_url);

		if(getimagesize($from_img_url) === 'false'){
			$this->flash->addMessage('error', __LINE__.'('.__FUNCTION__.')'.'generate thumbnail image url is empty.');
		}
		
		$ratio = $originalWidth / $originalHeight;
		$targetWidth = $targetHeight = min($size, max($originalWidth, $originalHeight));
		if ($ratio < 1) {
			$targetWidth = $targetHeight * $ratio;
		} else {
			$targetHeight = $targetWidth / $ratio;
		}
		
		$srcWidth = $originalWidth;
		$srcHeight = $originalHeight;
		$srcX = $srcY = 0;
		$targetWidth = $targetHeight = min($originalWidth, $originalHeight, $size);
		if ($ratio < 1) {
			$srcX = 0;
		    $srcY = ($originalHeight / 2) - ($originalWidth / 2);
		    $srcWidth = $srcHeight = $originalWidth;
		} else {
			$srcY = 0;
		    $srcX = ($originalWidth / 2) - ($originalHeight / 2);
		    $srcWidth = $srcHeight = $originalHeight;
		}
		
		try{
			$targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
			switch(strtolower($image_info['mime']))
			{
				case 'image/png':
					$original_img = imagecreatefrompng($from_img_url);
					break;
				case 'image/jpeg':
					$original_img = imagecreatefromjpeg($from_img_url);
					break;
				case 'image/gif':
					$original_img = imagecreatefromgif($from_img_url);
					break;
				default: die();
			}
			if(imagecopyresampled($targetImage, $original_img , 0, 0, $srcX, $srcY, $targetWidth, $targetHeight, $srcWidth, $srcHeight) === false){
				throw new Exception('image copy fail');
			}
			
			if(imagejpeg($targetImage, $destination.'/'.$filename) ===  false){
				throw new Exception('image convert jpeg fail');
			}
		} catch (Exception $e){
			$this->flash->addMessage('error',$e->getMessage());
		}
	}

	public function movetoUploadedFile($directory, UploadedFile $uploadedFile)
	{
	    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
	    $basename = bin2hex(random_bytes(8)); 
	    $filename = sprintf('%s.%0.8s', $basename, $extension);

	    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

	    return $filename;
	}
}

?>