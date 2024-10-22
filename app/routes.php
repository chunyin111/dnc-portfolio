<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

$app->get('/', 'PhotoController:index')->setName('home');
$app->get('/contact', 'PhotoController:contact')->setName('contact');
$app->post('/contact', 'PhotoController:contactus');
$app->get('/about', 'PhotoController:about')->setName('about');
$app->get('/gallery/weddingceremony', 'GalleryController:get_wedding_ceremony')->setName('wedding_ceremony');
$app->get('/gallery/pre-wedding', 'GalleryController:get_pre_wedding')->setName('pre_wedding');
$app->get('/gallery/couple', 'GalleryController:get_couple')->setName('couple');
$app->get('/gallery/sister', 'GalleryController:get_sisterhood')->setName('sister');
$app->get('/gallery/family', 'GalleryController:get_family')->setName('family');
$app->get('/gallery/portrait', 'GalleryController:get_portrait')->setName('portrait');
$app->get('/post-detail', 'PhotoController:get_post_detail')->setName('post_detail');


$app->group('', function(){ //authentication guest
	$this->get('/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');
	$this->post('/auth/signup', 'AuthController:postSignUp');
	$this->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
	$this->post('/auth/signin', 'AuthController:postSignIn');
})->add(new GuestMiddleware($container));

$app->group('', function() { //auth for member
	$this->get('/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');
	$this->get('/auth/password/change', 'PasswordController:getChangePassword')->setName('auth.password.change');
	$this->post('/auth/password/change', 'PasswordController:postChangePassword');
	$this->get('/admin', 'AdminController:index')->setName('admin');
	$this->get('/admin/image', 'AdminController:upload_image')->setName('upload-image');
	$this->post('/admin/image', 'AdminController:only_upload_image');
	$this->post('/admin', 'AdminController:upload');
	$this->get('/contact-list', 'AdminController:contact_list')->setName('admin.contact');
	$this->get('/admin/image-list', 'AdminController:image_list')->setName('admin.picture-list');
	$this->get('/admin/queue-image', 'AdminController:queue_image')->setName('admin.queue-image');
	$this->post('/admin/queue-image', 'AdminController:queue');
	$this->get('/admin/remove-image', 'AdminController:remove_image')->setName('admin.remove-image');
	$this->post('/admin/remove-image', 'AdminController:update_image_status');
	$this->get('/admin/video', 'AdminController:video')->setName('admin.video');
	$this->post('/admin/video', 'AdminController:add_video');
})->add(new AuthMiddleware($container));


?>