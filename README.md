# Craft Page Exporter

## Requirements

This plugin requires Craft CMS 3.0.0-RC1 or later.

## Installation

`composer require la-haute-societe\craft-page-exporter`


## Configuration

```
    'entryContentExtractor' => function (Entry $entry) {
        $url = $entry->getUrl();
        $content = file_get_contents($url . '?pageExporterContext=1');
        return $content;
    },
```

## Usage

Select one or more entries from `admin/entries`, then select `Export` from context menu.

You can also one entry from his edit page.

## Development

### Assets

All sources are localised in `resources` folder in plugin's root folder.

To build assets, run these commands from plugin's root folder :

```bash
yarn        # Install node dependencies needed for building assets

yarn watch  # Build assets in development mode & watch them for changes
yarn dev    # Build assets in development mode
yarn build  # Build assets in production mode
```

### Transformers

You can use the transformers that you defined in `src/models/transformers` folder.

In `Settings.php`, add a new entry as follow :
```php
'transformers' => [
    [...]
    'TransformerClassName' => [
        'enabled' => true,
        'option1' => 'value1',
        'option2' => 'value2'
    ]
]
```
You will also need to add them in `export-modal` and `settings` templates.

The options that you defined will be added as properties of your class object. 

## TODO

- Automatiser build assets (yarn) dans le CI
- Corriger scroll popin
- Ajouter un bouton "Export" + "Custom export"
- Doc surcharger les sélécteurs
- Doc surcharger la fonction de récup du contenu (render vs "file_get_contents")
- Template twig dans le champ prefix
- Publier sur le store

