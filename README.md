# cowitter

Asynchronous Twitter client compatible with mpyw/co Generator-based flows.

## Installing

Currently there are no stable versions, sorry xD

```
composer require mpyw/cowitter:@dev
```

## Examples

### Prepare requirements

```php
require 'vendor/autoload.php';

use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\Client;
use mpyw\Cowitter\HttpException;
```

### Create client

```php
$client = new Client(['CK', 'CS', 'AT', 'ATS']);
```

### Synchronous requests

```php
$statuses = $client->get('search/tweets', ['q' => 'cowitter'])->statuses;
var_dump($statuses);
```

```php
$client->post('statuses/update', ['status' => 'Cowitter is the best twitter library for PHP!']);
```

```php
$ids = [
    $client->postMultipart('media/upload', ['media' => new \CURLFile('photo01.png')])->media_id_string,
    $client->postMultipart('media/upload', ['media' => new \CURLFile('photo02.jpg')])->media_id_string,
];
$client->post('statuses/update', [
    'status' => 'My photos',
    'media_ids' => implode(',', $ids);
]);
```

```php
$client->streaming('user', function ($status) {
    if (!isset($status->text)) return;
    printf("%s(@s) - %s\n",
        $status->user->name,
        $status->user->screen_name,
        htmlspecialchars_decode($status->text, ENT_NOQUOTES)
    );
});
```

### Asynchronous requests

```php
Co::wait(function () use ($client) {
    $statuses = (yield $client->getAsync('search/tweets', ['q' => 'cowitter']))->statuses;
    var_dump($statuses);
});
```

```php
$tasks = [];
foreach ($i = 0; $i < 20; ++$i) {
    $tasks[] = client->postAsync('statuses/update', [
        'status' => str_repeat('!', $i + 1),
    ]);
}
Co::wait($tasks);
```

```php
Co::wait(function () use ($client) {
    $ids = yield [
        $client->postMultipartAsync('media/upload', ['media' => 'photo01'])->media_id_string,
        $client->postMultipartAsync('media/upload', ['media' => 'photo02'])->media_id_string,
    ];
    yield $client->postAsync('statuses/update', [
        'status' => 'My photos',
        'media_ids' => implode(',', $ids);
    ]);
});
```

```php
Co::wait($client->streamingAsync('statuses/filter', function ($status) use ($client) {
    if (!isset($status->text)) return;
    printf("%s(@s) - %s\n",
        $status->user->name,
        $status->user->screen_name,
        htmlspecialchars_decode($status->text, ENT_NOQUOTES)
    );
    yield Co::SAFE => [ // Ignore errors
        $client->postAsync('favorites/create', ['id' => $status->id_str]),
        $client->postAsync("statuses/retweet/{$status->id_str}"),
    ];
}, ['track' => 'PHP']));
```

```php
Co::wait(function () use ($client) {
    $file = new \SplFileObject('video.mp4');
    $on_progress = function ($percent) {
        if ($percent === null) {
            echo "Processing ...\n";
        } else {
            echo "Processing ... ({$percent}%)\n";
        }
    };
    echo "Uploading...\n";
    yield $client->postAsync('statuses/update', [
        'status' => 'My video',
        'media_ids' => (yield $client->uploadVideoAsync($file, $on_progress))->media_id_string,
    ]);
    echo "Done\n";
});

Co::wait($client->streamingAsync('statuses/filter', function ($status) use ($client) {
    if (!isset($status->text)) return;
    printf("%s(@s) - %s\n",
        $status->user->name,
        $status->user->screen_name,
        htmlspecialchars_decode($status->text, ENT_NOQUOTES)
    );
    yield Co::SAFE => [ // Ignore errors
        $client->postAsync('favorites/create', ['id' => $status->id_str]),
        $client->postAsync("statuses/retweet/{$status->id_str}"),
    ];
}, ['track' => 'PHP']));
```



### Handle exceptions

```php
try {

    // do stuff here
    $client->get(...);
    $client->post(...);

} catch (HttpException $e) {

    // cURL communication successful but something went wrong with Twitter APIs.
    $message = $e->getMessage();    // Message
    $code    = $e->getCode();       // Error code (-1 if not available)
    $status  = $e->getStatusCode(); // HTTP status code

} catch (CURLException $e) {

    // cURL communication failed.
    $message = $e->getMessage();    // Message    (equivalent to curl_error())
    $code    = $e->getCode();       // Error code (equivalent to curl_errno())

}
```


## Details

Read interfaces.

- [Client](src/ClientInterface.php)
- [Response](src/ResponseInterface.php)
- [Media](src/MediaInterface.php)
- [HttpException](src/HttpExceptionInterface.php)

## Todos

- Documentation
- Tests
- Improving codes
