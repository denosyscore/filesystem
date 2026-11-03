# Changelog

## v0.3.0

- Use an installable shared configuration read contract for the filesystem manager and provider.
- Declare the container dependency used by the provider and allow standalone hosts to omit the framework storage-path binding.
- Add a standalone integration test on PHP 8.2 and 8.5.

**Compatibility:** Direct `FilesystemManager` callers must pass a `ConfigurationReaderInterface` implementation. Framework hosts must bind the shared contract before resolving the provider; existing framework configuration can implement both interfaces.
