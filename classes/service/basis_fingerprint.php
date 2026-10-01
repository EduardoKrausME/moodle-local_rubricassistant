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

namespace local_rubricassistant\service;

/**
 * Fingerprint the deterministic Moodle data used to build an AI draft.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class basis_fingerprint {
    /**
     * Return a stable hash of the assignment and grading-form snapshot.
     *
     * @param array $assignment Assignment snapshot.
     * @param array $existing Grading-form snapshot.
     * @return string
     */
    public static function make(array $assignment, array $existing): string {
        $json = json_encode(
            ['assignment' => $assignment, 'existing' => $existing],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
        );
        return hash('sha256', $json === false ? '' : $json);
    }
}
