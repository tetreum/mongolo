<?php

use \App\Controllers\User;
use \App\Controllers\Database;
use \App\Controllers\Document;
use \App\Controllers\Collection;
use \App\Controllers\Index;

$ensureLogged = function($request, $response, $next) use ($app)
{
    $app->getContainer()->get("session")->ensureLogged();

    return $next($request, $response);
};

$app->get('/', function($request, $response, $args) use ($app) {
    $ct = new Database($app, $response);
    $ct->exec('showServerInfo');
})->setName('home')->add($ensureLogged);

$app->get('/login', function($request, $response, $args) use ($app) {
    $ct = new User($app, $response);
    $ct->exec('showLogin');
})->setName('login');

$app->post('/login', function($request, $response, $args) use ($app) {
    $ct = new User($app, $response);
    $ct->json('doLogin');
});

$app->get('/logout', function($request, $response, $args) use ($app) {
    $ct = new User($app, $response);
    $ct->exec('logout');
})->setName('logout');

$app->group('/collection', function () use ($app)
{
    $app->get('/create', function($request, $response, $args) use ($app) {
        $ct = new Collection($app, $response);
        $ct->exec('showCreate');
    })->setName("collectionCreate");

    $app->get('/entries', function($request, $response, $args) use ($app) {
        $ct = new Collection($app, $response);
        $ct->exec('showEntries');
    })->setName("collectionEntries");

    $app->get('/query', function($request, $response, $args) use ($app) {
        $ct = new Collection($app, $response);
        $ct->exec('showQueryResult');
    });

    $app->get('/indexes', function($request, $response, $args) use ($app) {
        $ct = new Index($app, $response);
        $ct->exec('showList');
    })->setName("collectionIndexes");

})->add($ensureLogged);

/**
 * API calls, all outputs are expected to be JSON & formatted like:
 * {error: integer, result: mixed}
 * Controller.php#98 will take care of that automatically
 */
$app->group('/api', function () use ($app)
{
    $app->group('/db', function () use ($app)
    {
        $app->get('/list', function($request, $response, $args) use ($app) {
            $ct = new Database($app, $response);
            $ct->json('getList');
        });
        $app->post('/collections', function($request, $response, $args) use ($app) {
            $ct = new Database($app, $response);
            $ct->json('getCollections');
        });
    });

    $app->group('/collection', function () use ($app)
    {
        $app->post('/create', function($request, $response, $args) use ($app) {
            $ct = new Collection($app, $response);
            $ct->json('create');
        });

        $app->post('/rename', function($request, $response, $args) use ($app) {
            $ct = new Collection($app, $response);
            $ct->json('rename');
        });

        $app->post('/truncate', function($request, $response, $args) use ($app) {
            $ct = new Collection($app, $response);
            $ct->json('truncate');
        });

        $app->post('/drop', function($request, $response, $args) use ($app) {
            $ct = new Collection($app, $response);
            $ct->json('drop');
        });
    });

    $app->group('/document', function () use ($app)
    {
        $app->post('/get', function($request, $response, $args) use ($app) {
            $ct = new Document($app, $response);
            $ct->json('get');
        });

        $app->post('/save', function($request, $response, $args) use ($app) {
            $ct = new Document($app, $response);
            $ct->json('edit');
        });

        $app->post('/delete', function($request, $response, $args) use ($app) {
            $ct = new Document($app, $response);
            $ct->json('delete');
        });
    });

    $app->group('/index', function () use ($app)
    {
        $app->post('/create', function($request, $response, $args) use ($app) {
            $ct = new Index($app, $response);
            $ct->json('create');
        });

        $app->post('/delete', function($request, $response, $args) use ($app) {
            $ct = new Index($app, $response);
            $ct->json('delete');
        });
    });
})->add($ensureLogged);

