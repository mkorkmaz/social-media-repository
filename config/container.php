<?php
declare(strict_types=1);


use SocialMediaRepository\Command;

$container = new Slim\Container;
$container['config'] = $config;
$container['commands'] =
    [
        Command\GetInstasCommand::class,
    ];
$container['soupmix'] = function($c) {
    $config = $c['config'];
    $adapter_config = [];
    $adapter_config['db_name'] = $config['mongodb']['db'];
    $adapter_config['connection_string']="mongodb://".$config['mongodb']['host'];
    $adapter_config['options'] =[];
    $client = new MongoDB\Client($adapter_config['connection_string'], $adapter_config['options']);
    return new Soupmix\MongoDB(['db_name' => $config['mongodb']['db']], $client);
};


$container['InstaRepository'] = function ($c) {
    $class = SocialMediaRepository\Domain\Instas\InstaRepository::class;
    return new $class($c->get('soupmix'));
};


$container['InstagramService'] = function ($c) {
    $config = $c['config'];
    $class = SocialMediaRepository\Domain\Instas\InstaService::class;
    $repository = $c->get('InstaRepository');
    return new $class($repository, $config);
};

return $container;