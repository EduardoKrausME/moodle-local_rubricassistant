# Changelog

This changelog documents only the two latest **published** releases of `local_rubricassistant`, verified on October 24, 2026. Each entry describes the changes between a published Git tag and its immediately preceding published tag. The October 20, 2026 `-core_text` commit occurred after release `1.0.4` and is intentionally excluded.

## [1.0.4] - 2026-10-15

### Assets
- Replaced the previous `pix/icon.svg` asset with a new `pix/icon.png` plugin icon.

### CI maintenance
- Updated the PostgreSQL CI service from version 16 to 17.
- Updated the MariaDB CI service from version 10 to 11.4, using the updated container environment variable and the `healthcheck.sh --connect --innodb_initialized` health check.

### Release metadata
- Updated the existing `version.php` metadata to release `1.0.4` (Moodle plugin version `2026101500`).

No plugin application logic files were changed between these two tags.

**Full comparison:** [1.0.3...1.0.4](https://github.com/EduardoKrausME/moodle-local_rubricassistant/compare/1.0.3...1.0.4)

## [1.0.3] - 2026-10-12

### CI compatibility
- Updated the PHP 8.3 Moodle test matrix from the Moodle 5.2 stable branch to the Moodle 5.3 stable branch.
- Added an explicit checkout of the `moodle-local_ai_bridge` dependency in the CI job and configured `moodle-plugin-ci install` to include the checked-out extra plugin.

### Release workflow security and packaging
- Changed the release workflow to read the triggering commit message through the `RELEASE_MESSAGE` environment variable instead of embedding it directly inside the shell script, reducing shell-injection risk.
- Reworked release workflow steps and messaging for extraction of version tags and GitHub release creation.
- Removed the previous custom ZIP archive packaging step and its release-asset attachment from the historical workflow. This changelog documents that removal; it does not change any workflow.

### Release metadata
- Updated the existing `version.php` metadata to release `1.0.3` (Moodle plugin version `2026101200`).

No plugin application logic files were changed between these two tags.

**Full comparison:** [1.0.2...1.0.3](https://github.com/EduardoKrausME/moodle-local_rubricassistant/compare/1.0.2...1.0.3)
