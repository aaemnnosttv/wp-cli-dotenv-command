# Changelog

## v2.0.0 (2018-05-21)
- Drop support for PHP 5.5
- Remove dependency on beloved `tightenco/collect` library
- Package now has zero dependencies (other than WP-CLI)

## v1.0.2 (2016-08-30)
- Fixed a regression when installed with `tightenco/collect` 5.3 and up

## v1.0.1 (2016-08-15)
- Fixed a regression where commented-out variables were showing up in `list`

## v1.0.0 (2016-08-06)
- Almost complete rewrite
- All sub-commands covered by functional tests
- Added ability to specify the wrapping quote type when setting a new definition with `set`
- Added ability to filter results by keys matching one or more glob patterns with `list`
- Enhanced `salts generate` to automatically detect placeholder values and update without `--force` or `regenerate`

## v0.2 (2016-05-02)
- Require PHP 5.5+
- Added initial tests
- Add `--force` option for `init` sub-command

## v0.1 (2015-08-04)
- Initial Release!
