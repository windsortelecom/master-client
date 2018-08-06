# Windsor Telecom Platform API Client

> Currently in alpha, do not use in production

## Usage

```php
<?php

$guzzle = new GuzzleHttp\Client([
    'base_uri' => '' // set me
]);

$client = new Windsor\Master\Client($guzzle);
$client->authenticate(''); // set me

try {
    $response = $client->get('contracts');
    $data = json_decode($response, true);
    
    foreach ($data['data'] as $contracts) {
        // do something
    }
} catch (Windsor\Master\Exception $e) {
    echo $e->getMessage()."\n";

    if ($e->getType() == 'unknown') {
        echo 'Request ref: '.$e->getRef()."\n";
    }
}
```
