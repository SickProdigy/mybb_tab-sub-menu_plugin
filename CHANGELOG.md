# Changelog

All notable changes to Tab Sub Menu are documented here.

## [0.5.13] - 2026-08-29

### Added

- Automated PHP, JavaScript, translation, version, and release-file validation.
- Repeatable versioned ZIP packaging with archive-content verification.
- GitHub Actions validation artifacts and automatic GitHub releases for mirrored `vX.Y.Z` tags.
- A documented compatibility baseline in the README; manual release verification remains tracked in the release issue.

### Changed

- Both JavaScript cache versions are now derived from the plugin version.

### Breaking changes

- None.

## [0.5.12] - 2026-08-29

### Added

- Automatic versioned upgrades from any Admin CP request, with retryable error reporting.
- Configurable default tab, selection persistence, shareable URL state, flash-safe initialization, and installation-scoped storage.
- Accessible tab roles, keyboard navigation, visible focus, and empty-tab handling.
- Automatic stylesheet and template synchronization for existing and newly created themes.
- A configurable adapter for customized category markup.
- English, Spanish, French, and Simplified Chinese language files.

### Changed

- Admin settings use a structured row editor and expose the maintained stylesheet as an optional CSS reference.
- Routine upgrades no longer require deactivation and reactivation.

### Migration notes

- Upload all files, including language files, then visit any Admin CP page.
- Existing settings, tab labels, and administrator-authored custom CSS are preserved.
- The untouched legacy default CSS value from version 0.5.0 is removed so maintained responsive rules can update safely.
- Deactivation/reactivation remains supported as a fallback.

### Breaking changes

- None.
