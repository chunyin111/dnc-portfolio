<?php 

use Respect\Validation\Validator as v;


session_start();

require __DIR__.'/../vendor/autoload.php';

$config = [
	'settings' => [
		'displayErrorDetails' => true,
		
    	'database' => [
    	    	'driver' => 'mysql',
    			'host' => 'localhost',
    			'database' => 'dncphot_dnc',
    			'username' => 'root',
    			'password' => '',
    			'charset' => 'utf8',
    			'collation' => 'utf8_unicode_ci',
    			'prefix' => '',
        ],
        'mode' => 'development',
	],

];

$app = new \Slim\App($config);

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => false
    ));
});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => true
    ));
});

$container = $app->getContainer();

$GLOBALS['FILE_PATH'] = '/images/';
$GLOBALS['THUMBNAIL_PATH'] = '"/images/70/';
if($container['settings']['mode'] == 'development'){
    $GLOBALS['FILE_PATH'] = '/dnc/images/';
    $GLOBALS['THUMBNAIL_PATH'] = '/dnc/images/70/';
}

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['database']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule){
	return $capsule;
};

$container['auth'] = function($container){
	return new \App\Auth\Auth;
};


$container['flash'] = function($container){
	return new \Slim\Flash\Messages;
};

$container['view'] = function($container){
	$view = new \Slim\Views\Twig(__DIR__.'/../resources/views', [
		'debug' => true, 
		'cache' => false,
	]);
	$view->addExtension(new \Slim\Views\TwigExtension(
		$container->router,
		$container->request->getUri()	
	));

	$view->getEnvironment()->addGlobal('auth', [
		'check' => $container->auth->check(),
		'user' => $container->auth->user()
	]);

	$view->getEnvironment()->addGlobal('flash', $container->flash);


	$view->addExtension(new \Twig_Extension_Debug());
	return $view;
};

$container['Validator'] = function($container){
	return new \App\Validation\Validator;
};

$container['HomeController'] = function($container){
	return new \App\Controllers\HomeController($container);
};

$container['AuthController'] = function($container){
	return new \App\Controllers\Auth\AuthController($container);
};

$container['PasswordController'] = function($container){
	return new \App\Controllers\Auth\PasswordController($container);
};

$container['GalleryController'] = function($container){
	return new \App\Controllers\GalleryController($container);
};

$container['PhotoController'] = function($container){
	return new \App\Controllers\PhotoController($container);
};

$container['AdminController'] = function($container){
	return new \App\Controllers\AdminController($container);
};


$container['csrf'] = function($container){
	$guard = new \Slim\Csrf\Guard;
    $guard->setPersistentTokenMode(true);
    return $guard;
};

$container['upload_directory'] = function($container){

    /*live*/
	return $_SERVER['DOCUMENT_ROOT'].'/dnc/images';
};

$container['base_url'] = function($container){
	// output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF']; 
    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index ) 
    $pathInfo = pathinfo($currentPath); 
    
    // output: localhost
    $hostName = $_SERVER['HTTP_HOST']; 
    
    // output: http://
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
    
    return $protocol.$hostName.$pathInfo['dirname'].'/';
};

$container['file_url'] = function($container){
	// output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF']; 
    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index ) 
    $pathInfo = pathinfo($currentPath); 
    
    // output: localhost
    $hostName = $_SERVER['HTTP_HOST']; 
    
    // output: http://
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
    
    // return: http://localhost/myproject/
    return sprintf("%s%s%s", $protocol, $hostName, $GLOBALS['FILE_PATH']);
};

//70 size resolution picture
$container['thumb_url'] = function($container){ //display gallery
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF']; 
    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index ) 
    $pathInfo = pathinfo($currentPath); 
    
    // output: localhost
    $hostName = $_SERVER['HTTP_HOST']; 
    
    // output: http://
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
    
    // return: http://localhost/myproject/
    return sprintf("%s%s%s", $protocol, $hostName, $GLOBALS['THUMBNAIL_PATH']);
};

//340 size resolution picture
$container['remove_pic_url'] = function($container){ //display gallery
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF']; 
    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index ) 
    $pathInfo = pathinfo($currentPath); 
    
    // output: localhost
    $hostName = $_SERVER['HTTP_HOST']; 
    
    // output: http://
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
    
    // return: http://localhost/myproject/
    /*live*/
    return $protocol.$hostName."/dnc/images/340/";
}; 

$container['image_url'] = function($container){ //display gallery
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF']; 
    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index ) 
    $pathInfo = pathinfo($currentPath); 
    
    // output: localhost
    $hostName = $_SERVER['HTTP_HOST']; 

    if($hostName == 'localhost'){
        $hostName .= '/'.explode('/', $_SERVER['REQUEST_URI'])[1];
        // return: http://localhost/myproject/
    }

    // output: http://
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
    
    /*live*/
    return $protocol.$hostName."/images/";
};

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldinputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));
$app->add($container->csrf);

//validation rules
v::with('App\\Validation\\Rules\\');

require __DIR__.'/../app/routes.php';

?>