
# Change Log
All notable changes to Templ Cache will be documented in this file.
 
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.4.2] - 2024-11-28

### Fixed

- Automatically purge cache when Enfold theme generates stylesheets.

## [1.4.1] - 2024-03-28

### Fixed

- Automatically purge cache when Divi's static resources are removed.

## [1.4.0] - 2024-03-20

### Changed

- Changed how default purge cache action hooks are loaded.

### Fixed

- Automatically purge cache when Elementor assets are regenerated.

## [1.3.2] - 2023-03-21

### Fixed

- Removed reference to a non-existing CSS file.

## [1.3.1] - 2023-02-07

### Fixed

- Fixed a bug where publishing a new post didn't purge the cache.
- Don't purge cache when a post of post_type 'revision' is updated.

## [1.3.0] - 2023-01-17

### Added

- CDN Enabler extension: possibility to update hostname using WP-CLI.

### Fixed

- Made automatic purging of cache when new content is published the default setting.

### Removed

- Experimental WP Rocket integration.


## [1.2.3] - 2022-11-30
 
### Added

- Added support for WP's "Site Health" page cache detection, to enable detection of Templ Cache.


## [1.2.2] - 2022-05-30
 
### Fixed

- Removed a purge cache hook that caused the cache to be purged every time a page built with Elementor page was visited.
