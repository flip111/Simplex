<?php

require_once __DIR__.'/../vendor/autoload.php';

use Simplex\StringResponseListener;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;

$routes = include __DIR__.'/../src/app.php';
$container = include __DIR__.'/../src/container.php';

$container->register('listener.string_response', StringResponseListener::class);
$container->getDefinition('dispatcher')
    ->addMethodCall('addSubscriber', array(new Reference('listener.string_response')))
;
$container->register('listener.response', HttpKernel\EventListener\ResponseListener::class)
    ->setArguments(array('%charset%'))
;
$container->setParameter('charset', 'UTF-8');
$container->register('matcher', Routing\Matcher\UrlMatcher::class)
    ->setArguments(array('%routes%', new Reference('context')))
;
$container->setParameter('routes', include __DIR__.'/../src/app.php');

$request = Request::createFromGlobals();

$response = $container->get('framework')->handle($request);

$response->send();
