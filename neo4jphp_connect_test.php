<?php
    require('vendor/autoload.php');

    $client = new Everyman\Neo4j\Client('localhost', 7474);
    $client->getTransport()
      ->setAuth('neo4j', 'soulmate');
    print_r($client->getServerInfo());
