<?php

namespace Reinink\Roundabout;

class Router
{
    protected $request;
    protected $instantiation_callback;
    protected $routes;

    public function __construct(\Symfony\Component\HttpFoundation\Request $request, Callable $instantiation_callback = null)
    {
        $this->request = $request;
        $this->instantiation_callback = $instantiation_callback;
        $this->routes = array();
    }

    public function get($path, $callback)
    {
        $this->bind($path, 'get', false, $callback);
    }

    public function getSecure($path, $callback)
    {
        $this->bind($path, 'get', true, $callback);
    }

    public function post($path, $callback)
    {
        $this->bind($path, 'post', false, $callback);
    }

    public function postSecure($path, $callback)
    {
        $this->bind($path, 'post', true, $callback);
    }

    public function put($path, $callback)
    {
        $this->bind($path, 'put', false, $callback);
    }

    public function putSecure($path, $callback)
    {
        $this->bind($path, 'put', true, $callback);
    }

    public function delete($path, $callback)
    {
        $this->bind($path, 'delete', false, $callback);
    }

    public function deleteSecure($path, $callback)
    {
        $this->bind($path, 'delete', true, $callback);
    }

    public function import(Array $routes)
    {
        foreach ($routes as $route) {

            if (!isset($route['path'])) {
                trigger_error('The "path" variable is missing from imported route.', E_USER_ERROR);
            }

            if (!isset($route['callback'])) {
                trigger_error('The "callback" variable is missing from imported route.', E_USER_ERROR);
            }

            if (!isset($route['secure'])) {
                $route['secure'] = false;
            }

            if (!isset($route['method'])) {
                $route['method'] = 'GET';
            }

            $this->bind($route['path'], $route['method'], $route['secure'], $route['callback']);
        }
    }

    public function bind($path, $method, $secure, $callback)
    {
        if (!is_string($path)) {
            trigger_error('Route path must be a string, ' . gettype($path) . ' given.', E_USER_ERROR);
        }

        if (!is_string($method)) {
            trigger_error('Route method must be a string, ' . gettype($method) . ' given.', E_USER_ERROR);
        }

        if (!is_bool($secure)) {
            trigger_error('Route secure must be a boolean, ' . gettype($secure) . ' given.', E_USER_ERROR);
        }

        if (!is_callable($callback, true)) {
            trigger_error('Route callback must be a valid callback, ' . gettype($callback) . ' given.', E_USER_ERROR);
        }

        $this->routes[] = array(
            'path' => $path,
            'method' => 'get',
            'secure' => false,
            'callback' => $callback
        );
    }

    private function match(&$route)
    {
        if (!$this->request->isMethod($route['method']) and strtolower($this->request->query->get('method')) !== strtolower($route['method'])) {
            return false;
        }

        if ($this->request->isSecure() !== $route['secure']) {
            return false;
        }

        if (preg_match('#^' . $route['path'] . '$#', $this->request->getPathInfo(), $matches) === 1) {

            array_shift($matches);

            $route['parameters'] = $matches;

        } else {
            return false;
        }

        return true;
    }

    public function run()
    {
        foreach ($this->routes as $route) {

            if ($this->match($route)) {

                if (is_object($route['callback']) or function_exists($route['callback'])) {

                    return call_user_func_array($route['callback'], $route['parameters']);

                } else if (is_string($route['callback'])) {

                    list($class, $method) = explode('::', $route['callback']);

                    if (is_null($this->instantiation_callback)) {
                        return call_user_func_array(array(new $class, $method), $route['parameters']);
                    } else {
                        return call_user_func_array($this->instantiation_callback, array($class, $method, $route['parameters']));
                    }
                }
            }
        }

        return false;
    }
}
