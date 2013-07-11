Roundabout
==========

RESTful router based on the Symfony2 HttpFoundation.

## Highlights

- Extremely simple API
- RESTful support: GET, POST, PUT & DELETE
- Based on the Symfony2 HttpFoundation (it just works)
- Supports closures, functions and controller (class) callbacks
- Ability to bulk imports routes using arrays
- Custom controller instantiation (for IoC container integration)

## Getting started

### Installation

Roundabout is available via Composer:

```json
{
    "require": {
        "reinink/roundabout": "1.*"
    }
}
```

### Setup

```php
<?php

use \Reinink\Roundabout\Router;
use \Symfony\Component\HttpFoundation\Request;

// Include Composer autoloader
require 'vendor/autoload.php';

// Create request object
$request = Request::createFromGlobals();

// Create router
$router = new Router($request);
```

## Basic routes

```php
<?php

// Home page
$router->get(
    '/',
    function () {
        // do something
    }
);

// Contact page
$router->get(
    '/contact',
    function () {
        // do something
    }
);

// Process form post
$router->post(
    '/form-submit',
    function () {
        // do something
    }
);

// Secure (HTTPS) page
$router->getSecure(
    '/login',
    function () {
        // do something
    }
);

// PUT request
$router->put(
    '/resource',
    function () {
        // do something
    }
);

// DELETE request
$router->delete(
    '/resource',
    function () {
        // do something
    }
);
```

## More complicated routes

```php
<?php

// User profile
$router->get(
    '/user/([0-9]+)',
    function ($userId) {
        // do something with $userId
    }
);

// Output image
$router->get(
    '/photo/(xlarge|large|medium|small|xsmall)/([0-9]+)',
    function ($imageSize, $imageId) {
        // do something with $imageSize and $imageId
    }
);
```

## Working with controller classes

```php
<?php

// Home page
$router->get('/', 'Controller::index');

// Contact page
$router->get('/contact', 'Controller::contact');

// Process form post
$router->post('/form-submit', 'Controller::process_form');

// Secure (HTTPS) page
$router->getSecure('/login', 'Controller::login');
```

## Bulk route definitions

```php
<?php

$router->import(
    [
        [
            'path' => '/',
            'method' => 'GET',
            'secure' => false,
            'callback' => 'Controller::index'
        ],
        [
            'path' => '/contact',
            'method' => 'GET',
            'secure' => false,
            'callback' => 'Controller::contact'
        ],
        [
            'path' => '/form-submit',
            'method' => 'POST',
            'secure' => false,
            'callback' => 'Controller::process_form'
        ],
        [
            'path' => '/login',
            'method' => 'GET',
            'secure' => true,
            'callback' => 'Controller::login'
        ]
    ]
);
```

## Custom controller instantiation

By default Roundabout instantiates controller classes automatically, but this beahavior can be overridden using the `$instantiation_callback` paramater. This can be helpful in situations where you want to use an inversion of control container.

```php
<?php

// Your IoC container
$ioc = new Container;

// Create router
$router = new Router(
    $request,
    function ($class, $method, $parameters) use ($ioc) {

        $controller = $ioc->make($class);

        return call_user_func_array(array($controller, $method), $parameters);
    }
);
```