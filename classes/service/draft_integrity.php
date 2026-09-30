<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// This file is part of Moodle - http://moodle.org/

namespace local_rubricassistant\service;

use moodle_exception;

/**
 * Cross-check AI suggestions against deterministic Moodle data.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class draft_integrity {
    /**
     * Reject source IDs invented by the model.
     *
     * @param array $result Validated AI result.
     * @param array $existing Existing grading form.
     * @return void
     */
    public static function assert_sources(array $result, array $existing): void {
        $ids = [];
        foreach (($existing['criteria'] ?? []) as $criterion) {
            $ids[(int)$criterion['id']] = true;
        }

        foreach (($result['criteria'] ?? []) as $criterion) {
            $sourceid = (int)($criterion['sourceid'] ?? 0);
            if ($sourceid > 0 && !isset($ids[$sourceid])) {
                throw new moodle_exception('sourcecriterionmissing', 'local_rubricassistant');
            }
        }
    }
}
