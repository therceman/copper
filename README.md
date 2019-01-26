Copper is a **PHP Framework** based on [Symfony][1] with routing and simplified Controller -> View (Templating) system

See [The Copper Skeleton][0] - a minimal and empty Copper project which you can base your new projects on

Installation
------------

```
composer require rceman/copper
```

Folder Structure
------------
```
 /
  - config
    - routes.php
  - public
    - index.php
  - src
    - Controller
      - HomeController.php
  - templates
    - index.php
```

Configuration (Getting Started)
------------

create file **`/public/index.php`**
```
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new Copper\Kernel();
$kernel->handle(Request::createFromGlobals())->send();
```

Configuration (Advanced)
------------

update file **`/composer.json`**
```
{
    ...
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

run console command
```
composer update
```

create file **`/templates/index.php`**
```
<?php /** @var \Copper\Component\Templating\ViewHandler $view */ ?>

<?= $view->render('header') ?>

<body>
<h4><?= $view->out($view->data('message')) ?></h4>
</body>
```

create file **`/src/Controller/HomeController.php`**
```
<?php

namespace App\Controller;

use Copper\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function getIndex()
    {
        $parameters = [
            'head_title' => 'App :: Home',
            'head_meta_description' => 'Application based on Copper PHP Framework',
            'head_meta_author' => '{enter your name here}',
            'message' => 'Welcome to your Application!'
        ];

        return $this->render('index', $parameters);
    }
}
```

create file **`/config/routes.php`**
```
<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use App\Controller\HomeController;

return function (RoutingConfigurator $routes) {
    $routes->add('index', '/')
        ->controller([HomeController::class, 'getIndex'])
        ->methods(['GET']);
};
```

Configuration (Optional)
------------
create file **`/.gitignore`**
```
vendor/
composer.lock
composer.phar
```

Framework modules
------------

* Routes config module (using config/routes.php)
* Controller module (using Controller/*Controller.php)
* View (Templating) module (using templates/*.php)

Routes config module :: Classes
----
### Classes
* [[RoutingConfigurator][2]] (Symfony) - Routing Configurator
  * Instances
    * `$routes` - default instance
  * Methods (basic)
     * `->add($name, $path)` - Adds a route.
       * `->controller($controller)` - Adds controller to a route.
       * `->methods($methods)` - Sets the HTTP methods (e.g. `'POST'`) this route is restricted to
       * `->requirements($requirements)` - Adds route requirements (e.g. `['page' => '\d+']`)  
       * `->defaults($defaults)` - Adds route defaults (e.g. `['page' => 1]`)  

View (Templating) module :: Variables, Methods and Classes
----
### Variables
* `$view->request_method`  - Request method GET or POST
* `$view->client_ip`       - Client's IP address
* `$view->controller_name` - Controller name
* `$view->route_name`      - Route name
### Methods
* `$view->route($key, $default = null)`   - Route parameter by key
* `$view->post($key, $default = null)`    - POST parameter by key
* `$view->query($key, $default = null)`   - GET parameter by key
* `$view->cookies($key, $default = null)` - Cookies parameter by key
* `$view->data($key, $default = null)`    - Template parameter by key
* `$view->out($value)`  - Escape HTML code and output as string
* `$view->out($array)`  - Escape HTML code and output as formatted array
* `$view->render($template)`  - Render template
* `$view->path($name, $parameters = [], $relative = false)`      - Returns the relative URL to named route
* `$view->url($name, $parameters = [], $schemeRelative = false)` - Returns the absolute URL to named route
### Classes 
* [[ViewOutput][3]] - Output processor (escape, format, etc.)
  * Instances
    * `$view->output` - default instance
  * Methods
    * `->raw($value)`  - Output as string (no escape)
    * `->js($value)`   - Escape Javascript code and output as string
    * `->json($array)` - Format Array as JSON string (no escape)
    * `->text($value)` - Escape HTML code and output as string
    * `->dump($array)` - Escape HTML code and output as formatted array
* [[ParameterBag][4]] (Symfony) - Collection of Request parameters
  * Instances
    * `$view->routeBag`   - Route parameters (iterable)
    * `$view->postBag`    - POST parameters (iterable)
    * `$view->queryBag`   - GET parameters (iterable)
    * `$view->cookiesBag` - Cookies parameters (iterable)
    * `$view->dataBag`    - Template parameters (iterable)
  * Methods (basic)
    * `->all()`             - Returns an array with parameters
    * `->get($key)`         - Returns a parameter by name
    * `->set($key, $value)` - Sets a parameter by name
    * `->has($key)`         - Returns true if the parameter is defined
    * `->remove($key)`      - Removes a parameter
    * `->getInt($key, $default = 0)`         - Returns the parameter value converted to integer
    * `->getBoolean($key, $default = false)` - Returns the parameter value converted to boolean
    
Controller module :: Methods and Classes
----
### Methods
* `$this->render($view, $parameters = [])` - Returns a Response with rendered view
* `$this->renderView($view, $parameters = [])` - Returns a rendered view
* `$this->json($data, $status = 200, $headers = [])` - Returns a JsonResponse that uses json_encode
* `$this->redirectToRoute($route, $parameters = [], $status = 302)` - Returns a RedirectResponse to the given route
* `$this->redirect($url, $status = 302)` - Returns a RedirectResponse to the given URL
### Classes
* [[Request][5]] (Symfony) - Request represents an HTTP request
  * Instances
    * `$this->request` - default instance
* [[RequestContext][6]] (Symfony) - Holds information about the current request
  * Instances
    * `$this->requestContext` - default instance
* [[RouteCollection][7]] (Symfony) - A RouteCollection represents a set of Route instances
  * Instances
    * `$this->routes` - default instance

[0]: https://github.com/rceman/copper_skeleton
[1]: https://symfony.com
[2]: https://github.com/symfony/routing/blob/3.4/Loader/Configurator/RoutingConfigurator.php
[3]: https://github.com/rceman/copper/blob/master/src/Component/Templating/ViewOutput.php
[4]: https://github.com/symfony/http-foundation/blob/3.4/ParameterBag.php
[5]: https://github.com/symfony/http-foundation/blob/3.4/Request.php
[6]: https://github.com/symfony/routing/blob/3.4/RequestContext.php
[7]: https://github.com/symfony/routing/blob/3.4/RouteCollection.php
