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
 * Thin adapter around local_ai_bridge.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ai_client {
    /** Purpose configured in local_ai_bridge. */
    public const PURPOSE = 'rubricassistant-review';

    /**
     * Generate and validate an analysis.
     *
     * @param array $messages Normalized chat messages.
     * @param string $method rubric|guide.
     * @param string $operation Operation.
     * @return array
     */
    public static function generate(array $messages, string $method, string $operation): array {
        if (!class_exists('\\local_ai_bridge\\api')) {
            throw new moodle_exception('bridgeunavailable', 'local_rubricassistant');
        }

        $response = \local_ai_bridge\api::generate(self::PURPOSE, $messages);
        return response_validator::decode($response->text, $method, $operation);
    }
}
