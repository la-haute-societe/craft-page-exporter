# Craft Page Exporter

## Requirements

This plugin requires Craft CMS 3.0.0-RC1 or later.

## Installation

Add LHS repository in your `composer.json`

```
{
  [...]
  "repositories": [
    {
      "type": "composer",
      "url":  "http://packages.lahautesociete.int"
    }
  ]
}
```

Then execute : `composer require la-haute-societe\craft-page-exporter`

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