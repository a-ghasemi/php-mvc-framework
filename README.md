# SIMPHA (*a simple MVC framework over PHP*)

## Getting Started
1. clone it
2. run `composer install` on the root folder
3. run `composer dump-autoload -o` on the root folder
3. run `cp .env.example .env` on the root folder
4. [in **app** folder] create your classes (routes will generate automatically from these classes)
5. [in **app** folder] create your views
6. if you like it, **star** it

## Serve on local browser
1. cd `public` folder
2. run `php -S 127.0.0.1:8081`
3. browse `127.0.0.1:8081` at web browser

## Documentation
### Routing
route format is: example.com/foo/bar/slashed_params

means if you want above route, follow these steps:
1. create FooController.php in app/controllers
2. create `pubic function bar()` in it
3. specify request type, `post_bar()`
* your options are HTTP request types: `get_`,`post_`,`put_`,`patch_`,`delete_`,...
* if you are going to accept all kind of methods, use `any_`
* no route.php file, because this is **SIMPLE MVC**

## Views
if you want to return a view as a result of a route in corresponded function (that described in Routing section), do this:
> return View::show('home.index')

this means, send <root>/app/views/home/index.blade.php to client
* You have powerful **blade engine** here, many thanks to @jenssegers
* Do not echo anything, just return the response

## Models
my next TODO ...

## Artisan Commands
run artisan by `php artisan`

You can create your own commands simply by creating its class in `app/commands`

For a command like `php artisan foo:bar params` just create a `Foo.php` as a class and define `public funtion bar()` in it

For a command like `php artisan foo params` define method `public function index()` like Demo class

To get version number of *Simpha*, run `php artisan version`

## .env
You have .env file here, and an ENV_Parser helps to parse it.

Read env flags like this:
> env_get('flag_label','default_value')

## Contribution
If you want to participate in this repo, you always welcomed.
Parts to contribution:
* Adding New Features
* Testing
* Performance Improvement
* Clean Code advices
