<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/slimphp/Twig-View
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/Twig-View/blob/master/LICENSE.md (MIT License)
 */

namespace Slim\Views;


class TwigExtension extends \Twig_Extension
{
    /**
     * @var \Slim\Interfaces\RouterInterface
     */
    private $router;

    /**
     * @var string|\Slim\Http\Uri
     */
    private $uri;

    public function __construct($router, $uri)
    {
        $this->router = $router;
        $this->uri = $uri;
    }

    public function getName()
    {
        return 'slim';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('path_for', array($this, 'pathFor')),
            new \Twig_SimpleFunction('base_url', array($this, 'baseUrl')),
            new \Twig_SimpleFunction('upload', array($this, 'controllerURL')),
            new \Twig_SimpleFunction('is_current_path', array($this, 'isCurrentPath')),
        ];
    }

    public function pathFor($name, $data = [], $queryParams = [], $appName = 'default')
    {
        return $this->router->pathFor($name, $data, $queryParams);
    }

    public function baseUrl()
    {
        if (is_string($this->uri)) {
            return $this->uri;
        }
        if (method_exists($this->uri, 'getBaseUrl')) {
            return $this->uri->getBaseUrl();
        }
    }

    public function isCurrentPath($name)
    {
        return $this->router->pathFor($name) === $this->uri->getPath();
    }

    public function controllerURL()
    {   
        define('CONFIG_DIR_NAME', 'config');
        define('M_ROOT', substr(dirname(__FILE__), 0, strlen(dirname(__FILE__)) - (strlen(CONFIG_DIR_NAME) + 1)));
        define('WORK_DIR', str_replace('\\','/',substr(M_ROOT, strlen(rtrim($_SERVER['DOCUMENT_ROOT'], '/')))));
        define('URL_PROTOCOL','https');
        define('DOMAIN', 'dncphotographer');
        define('M_URL', URL_PROTOCOL.'://'.($_SERVER['HTTP_HOST']? $_SERVER['HTTP_HOST'] : DOMAIN).'/controller/');
        return M_URL."upload.php";
    }

    /**
     * Set the base url
     *
     * @param string|Slim\Http\Uri $baseUrl
     * @return void
     */
    public function setBaseUrl($baseUrl)
    {
        $this->uri = $baseUrl;
    }
}
