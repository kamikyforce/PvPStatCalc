<?php

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\Session\Store;
use Illuminate\Session\FileSessionHandler;

// Create the container
$container = new Container();

// Set the container as the global instance
Container::setInstance($container);

// Set up Facade
Facade::setFacadeApplication($container);

// Set up simple config as array (instead of Repository)
$config = [
    'session' => [
        'driver' => 'file',
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => false,
        'files' => __DIR__ . '/../storage/framework/sessions',
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => 'laravel_session',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ],
];
$container->instance('config', $config);

// Set up Filesystem
$filesystem = new Filesystem();
$container->instance('files', $filesystem);

// Set up Events
$events = new Dispatcher($container);
$container->instance('events', $events);

// Set up Translation
$translationLoader = new FileLoader($filesystem, __DIR__ . '/../resources/lang');
$translator = new Translator($translationLoader, 'en');
$container->instance('translator', $translator);

// Set up View
$viewResolver = new EngineResolver();
$bladeCompiler = new BladeCompiler($filesystem, __DIR__ . '/../storage/framework/views');

// Register view engines
$viewResolver->register('blade', function () use ($bladeCompiler) {
    return new CompilerEngine($bladeCompiler);
});

$viewResolver->register('php', function () use ($filesystem) {
    return new PhpEngine($filesystem);
});

// Set up view finder
$viewFinder = new FileViewFinder($filesystem, [__DIR__ . '/../resources/views']);

// Create view factory
$viewFactory = new Factory($viewResolver, $viewFinder, $events);
$viewFactory->setContainer($container);
$viewFactory->share('app', $container);

$container->instance('view', $viewFactory);

// Set up Session (simplified approach without SessionManager)
$sessionHandler = new FileSessionHandler($filesystem, __DIR__ . '/../storage/framework/sessions', 120);
$sessionStore = new Store('laravel_session', $sessionHandler);

$container->instance('session', $sessionStore);
$container->instance('session.store', $sessionStore);

// Register facades
$container->bind('Illuminate\\Support\\Facades\\View', function () use ($viewFactory) {
    return $viewFactory;
});

$container->bind('Illuminate\\Support\\Facades\\Session', function () use ($sessionStore) {
    return $sessionStore;
});

// Define CSRF helper functions
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    function old($key, $default = null) {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('request')) {
    function request() {
        return new class {
            public function is($path) {
                $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $currentPath = rtrim($currentPath, '/');
                if ($currentPath === '') $currentPath = '/';
                
                if ($path === '/') {
                    return $currentPath === '/' || $currentPath === '/index.php';
                }
                
                return $currentPath === $path;
            }
            
            public function path() {
                return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            }
            
            public function url() {
                return $_SERVER['REQUEST_URI'];
            }
        };
    }
}

if (!function_exists('session')) {
    function session($key = null, $default = null) {
        if ($key === null) {
            return $_SESSION;
        }
        return $_SESSION[$key] ?? $default;
    }
}

// Create necessary directories
if (!is_dir(__DIR__ . '/../storage/framework/views')) {
    mkdir(__DIR__ . '/../storage/framework/views', 0755, true);
}

if (!is_dir(__DIR__ . '/../storage/framework/sessions')) {
    mkdir(__DIR__ . '/../storage/framework/sessions', 0755, true);
}

if (!is_dir(__DIR__ . '/../resources/lang')) {
    mkdir(__DIR__ . '/../resources/lang', 0755, true);
}

return $container;