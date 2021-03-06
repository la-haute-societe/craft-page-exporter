# Craft Page Exporter Changelog


## 1.2.2 - 2021-07-05
### Fixed
- Fix a bug introduced in 1.2.0 that caused
  `craft.pageExporter.isInExportContext()` to always return `false`
- Fix a bug introduced in 1.2.0 that caused explicitly registered assets not to
  be exported anymore


## 1.2.1 - 2021-06-11
### Fixed
- Fix a bug caused by asset URLs containing a querystring


## 1.2.0 - 2021-05-12
### Added
- Add compatibility with SEOmatic plugin
- Use an HTTP request to retrieve the source of the pages to export instead of
  having Craft render them (helps with SEOmatic compatibility)
- Add support for all meta having a `property` attribute containing `image`
  (e.g. `og:image` & `twitter:image`)
### Fixed
- Fix manually registered asset (using
  `{{ craft.pageExporter.registerAsset() }}`) missing in exported archive
- Fix favicon missing in exported archive
- Fix missing export button in entry edit page
- Fix empty assets in the exported archive when URL contains a querystring


## 1.1.9 - 2021-01-03
### Fixed
- Fix classes import with new namespace


## 1.1.8 - 2020-03-11
### Fixed
- Fixed: PSR-4 compliance of the PageExporterVariable class


## 1.1.7 - 2020-03-11
### Fixed
- Fixed: Set current site instead of current language


## 1.1.6 - 2019-11-05
### Fixed
- Fix Craft 3.3 incompatibility


## 1.1.5 - 2019-08-19
### Fixed
- Fix minor compatibility issue with Craft 3.2.10
- Fix issue #2 "html" and "doctype" tags are now in the export


## 1.1.3 - 2019-06-23
### Fixed
- Use site selected by user instead of default site (Fix #1)


## 1.1.2 - 2019-06-12
### Fixed
- Path of explicitly registered assets


## 1.1.1 - 2019-06-11
### Fixed
- Path and URL of assets in external stylesheets


## 1.1.0 - 2019-06-10
### Added
- User permissions


## 1.0.0 - 2019-06-01
### Added
- Initial release
