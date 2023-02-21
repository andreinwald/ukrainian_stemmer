# Ukrainian stemmer
- Library for stemming, eg the process of reducing inflected (or sometimes derived) words to their word stem, base or root form—generally a written word form.
- Бібліотека для стемінгу, тобто скорочення слів до основи шляхом відкидання допоміжних частин, таких як закінчення чи суфікс.


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
