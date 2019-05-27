# Craft Page Exporter

This plugin allows you to export entries in ZIP format.
The archive contains the HTML rendering of the entry with images, videos, scripts and styles attached to the page.

## Requirements

This plugin requires Craft CMS 3.0.0-RC1 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store
Go to the Plugin Store in your project’s Control Panel and search for “Page exporter”. Then click on the “Install” button in its modal window.


#### With Composer
Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project

# tell Composer to load the plugin
composer require la-haute-societe/craft-page-exporter

# tell Craft to install the plugin
./craft install/plugin craft-page-exporter
```

## Usage

Select one or more entries from `admin/entries`, then select `Export` from context menu.

You can also export one entry from his edit page.

## Configuration

You can configure the plugin's behavior from the plugin settings page. 
Or, for more options, from the configuration file `config/craft-page-exporter.php`.

Example of (simple) configuration file :
```php
<?php

return [
    'inlineStyles'      => true,
    'inlineScripts'     => true,
    'exportPathFormat'  => 'media/{filename}-{hash}{extension}',
    'exportUrlFormat'   => 'media/{filename}-{hash}{extension}',
];
```

### Configuration settings

Configuration array can contain the following settings:

#### `inlineStyles`
*Default: ``true``*

Whether external stylesheets must be inlined in the main HTML page inside a `style` tag.

If true, external stylesheets content will be moved inside a ``<style>`` tags.

If false, external stylesheets will be left in external files.


#### `inlineScripts`
*Default: ``true``*

Whether external scripts must be inlined in the main HTML page inside a `script` tag.

If true, external scripts content will be moved inside a ``<style>`` tags.

If false, external scripts will be left in external files.


#### `exportPathFormat`
*Default: ``{dirname}/{basename}``*

Format of the asset path in the ZIP archive. 
This path is relative to the root of the archive. 

It's possible to keep the structure of the original folders: ``{dirname}/{basename}``,

or to put all assets in a single folder: ``media/{filename}{extension}``,

in this case it is preferable to add a hash in the file name to avoid any collision: ``media/{filename}-{hash}{extension}``

The following variables are available:

| Variables | Values |
| --------- | ------ |
| `{filename}`    | `filename` | 
| `{extension}`   | ``.png`` (the dot is already contains in the value) | 
| `{basename}`    | `filename.png` | 
| `{dirname}`     | `/path/to/folder` | 
| `{hash}`        | `c023d66f` | 
| `{year}`        | `date('Y')` | 
| `{month}`       | `date('m')` | 
| `{day}`         | `date('d')` | 
| `{hour}`        | `date('H')` | 
| `{minute}`      | `date('i')` | 
| `{second}`      | `date('s')` |

You can also use any Twig expression like:

`my-folder/{{ "now"|date("Y-m") }}/{{ hash[:1] }}/{{ hash[1:1] }}/{{hash}}/{{ filename|upper }}{{extension}}`

which will create this path:

``\my-folder\2019-05\1\8\18f4a488\MY-IMAGE.png``


#### `exportUrlFormat`
*Default: ``{dirname}/{basename}``*

Format of the asset URL in the ZIP archive. 

In most cases, this path should correspond to the ``exportPathFormat`` setting.

This parameter accepts the same variables as ``exportPathFormat``.

If you plan to place your assets on a CDN for example, you can specify an absolute URL : 
```https://my.cdn.example.com/xyz/{dirname}/{basename}```
 

#### `assetTransformers`


#### `entryContentExtractor`


#### `customSelectors`


#### `sourcePathTransformer`


## Contributing

### Building assets

All sources are localised in `resources` folder in plugin's root folder.

To build assets, run these commands from plugin's root folder :

```bash
yarn        # Install node dependencies needed for building assets

yarn watch  # Build assets in development mode & watch them for changes
yarn dev    # Build assets in development mode
yarn build  # Build assets in production mode
```
