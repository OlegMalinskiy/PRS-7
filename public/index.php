<?php

use App\Headers;
use App\Request;

require '../vendor/autoload.php';

$headers = Headers::createFromGlobals();
print_r($headers);

$request = (new Request())
    ->withHeader('Auth', 'Bearer Token')
    ->withAddedHeader('auth', ['New Bearer Token', 'Just Token'])
    ->withAddedHeader('Api_Auth', 'New Bearer Token        ')
    ->withHeader('X-Auth', 'X-Token')
    ->withAddedHeader('X-AUTH', 'NEW X-Token')
    ->withHeader('Version', '1')
    ->withoutHeader('versIon');

foreach ($request->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        echo sprintf("%s: %s", $name, $value);
        echo PHP_EOL;
    }
}

print_r($request->getHeaders());
print_r($request->getHeader('AUTH'));
print_r($request->getHeader('AA'));
print_r($request->getHeaderLine('X-AUTH'));
var_dump($request->getHeaderLine('X'));
