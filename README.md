# Ukrainian stemmer

## Installing
```console
composer require andreinwald/ukrainian_stemmer
```

## Using
```php
use UkrainianStemmer\Stemmer;

$short = Stemmer::stemWord('український');

echo $short; // will display "українськ"
```
