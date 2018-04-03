# GTranslator
Translate between multiple languages using Google Translate.

## Usage
### Simple text translate
```
<?php

require_once('GTranslator.php');

$text = 'Text to translate.';
$sourceLanguage = 'en';
$targetLanguage = 'pl';

$gTranslator = new GTranslator($sourceLanguage, $targetLanguage);
echo $gTranslator->translateText($text);

```
### Translate array of translations and export to PHP file
```
<?php

require_once('GTranslator.php');

$sourceLanguage = 'en';
$targetLanguage = 'pl';

$translations = [
    'en' => [
        'first' => 'Translate text.',
        'second' => 'Translate array.',
        'third' => 'Update file with translations.',
    ]
];

$gTranslator = new GTranslator($sourceLanguage, $targetLanguage);

$translations['pl'] = $gTranslator->translateArray($translations['en']);

$gTranslator->exportToPhpFile($translations, 'translations.php');

```

### Update PHP file with translations
```
<?php

require_once('GTranslator.php');

$gTranslator = new GTranslator();
$gTranslator->updateTranslations('translations.php', ['pl', 'de', 'es']);

```
PHP file with translations:
```
// translations.php
<?php
return [
    'en' => [
        'key' => 'Text to translate.',
    ]
];

```
