# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## Unreleased
## changed
- remove the old buildchain (based on vue-cli)


## [1.5.2] - 2023-04-14
- Change plugin icon
- Fix an issue with the buildchain


## [1.5.1] - 2022-08-26
### Changed
- Use the Craft Guzzle client (thanks [@markdrzy][], fixes [#36][] & [#37][])
### Fixed
- Bug that prevented installing the plugin using the
  `craft plugin/install craft-page-exporter` command (fixes [#33][] & [#34],
  thanks [@BillBushee][])


## [1.5.0] - 2022-02-03
### Added
- Force the [`generateTransformsBeforePageLoad`](https://craftcms.com/docs/3.x/config/config-settings.html#generatetransformsbeforepageload)
  Craft general setting when exporting to ensure all images are exported
### Fixed
- Fix a bug that broke the Export element action
- Fix a bug that caused the export button not to be shown in entry edit pages
- Fix Readme


## [1.4.0] - 2021-12-14
- Refactor frontend asset management to get rid of outdated dependencies


## [1.3.0] - 2021-10-20
### Added
- Add archive filename in config, so the user can define his own ZIP filename
  before exporting
### Fixed
- Fix HTML asset filename which could contain the wrong locale
- Fix inlineStyles and inlineScripts config which was not considered by default


## [1.2.2] - 2021-07-05
### Fixed
- Fix a bug introduced in 1.2.0 that caused
  `craft.pageExporter.isInExportContext()` to always return `false`
- Fix a bug introduced in 1.2.0 that caused explicitly registered assets not to
  be exported anymore


## [1.2.1] - 2021-06-11
### Fixed
- Fix a bug caused by asset URLs containing a querystring


## [1.2.0] - 2021-05-12
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


## [1.1.9] - 2021-01-03
### Fixed
- Fix classes import with new namespace


## [1.1.8] - 2020-03-11
### Fixed
- Fixed: PSR-4 compliance of the PageExporterVariable class


## [1.1.7] - 2020-03-11
### Fixed
- Fixed: Set current site instead of current language


## [1.1.6] - 2019-11-05
### Fixed
- Fix Craft 3.3 incompatibility


## [1.1.5] - 2019-08-19
### Fixed
- Fix minor compatibility issue with Craft 3.2.10
- Fix issue #2 "html" and "doctype" tags are now in the export


## [1.1.3] - 2019-06-23
### Fixed
- Use site selected by user instead of default site (Fix #1)


## [1.1.2] - 2019-06-12
### Fixed
- Path of explicitly registered assets


## [1.1.1] - 2019-06-11
### Fixed
- Path and URL of assets in external stylesheets


## [1.1.0] - 2019-06-10
### Added
- User permissions


## [1.0.0] - 2019-06-01
### Added
- Initial release


[#33]: https://github.com/la-haute-societe/craft-page-exporter/issues/33
[#34]: https://github.com/la-haute-societe/craft-page-exporter/issues/34
[#36]: https://github.com/la-haute-societe/craft-page-exporter/issues/36
[#37]: https://github.com/la-haute-societe/craft-page-exporter/issues/37
[@BillBushee]: https://github.com/BillBushee
[@markdrzy]: https://github.com/markdrzy

[1.0.0]: https://github.com/la-haute-societe/craft-page-exporter/releases/tag/1.0.0
[1.1.0]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.0.0...1.1.0
[1.1.1]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.1.0...1.1.1
[1.1.2]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.1.1...1.1.2
[1.1.3]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.1.2...1.1.3
[1.1.5]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.1.3...1.1.5
[1.1.6]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.1.5...1.1.6
[1.1.7]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.1.6...1.1.7
[1.1.8]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.1.7...1.1.8
[1.1.9]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.1.8...1.1.9
[1.2.0]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.1.9...1.2.0
[1.2.1]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.2.0...1.2.1
[1.2.2]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.2.1...1.2.2
[1.3.0]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.2.2...1.3.0
[1.4.0]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.3.0...1.4.0
[1.5.0]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.4.0...1.5.0
[1.5.1]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.5.0...1.5.1
[1.5.2]: https://github.com/la-haute-societe/craft-page-exporter/compare/1.5.2...1.5.2
