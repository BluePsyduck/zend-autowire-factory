# Changelog

## 1.1.0 - 2019-07-08

### Added

- `AliasArrayInjectorFactory` to resolve an array of container aliases.
- `injectAliasArray()` as shortcut to register the `AliasArrayInjectorFactory` in the container config.

### Changed

- `ConfigReaderFactory::register()` to `readConfig()` as a shortcut to register the `ConfigReaderFactory` in the 
  container config.

## 1.0.1 - 2019-05-30

### Fixed

- Zend's ConfigAggregator caching failed to load cache when using the `ConfigReaderFactory`.

## 1.0.0 - 2019-05-22

- Initial version of the library containing the `AutoWireFactory` and `ConfigReaderFactory`.
