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
 * Store short-lived AI drafts in the Moodle session.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class draft_store {
    /** Draft lifetime in seconds. */
    private const TTL = 3600;

    /**
     * Store a draft and return an opaque token.
     *
     * @param array $draft Draft data.
     * @return string
     */
    public static function put(array $draft): string {
        global $SESSION, $USER;

        $token = bin2hex(random_bytes(24));
        $draft['_userid'] = (int)$USER->id;
        $draft['_created'] = time();
        $SESSION->local_rubricassistant_drafts[$token] = $draft;
        self::purge();
        return $token;
    }

    /**
     * Fetch a draft for the current user and module.
     *
     * @param string $token Token.
     * @param int $cmid Course module id.
     * @return array
     */
    public static function get(string $token, int $cmid): array {
        global $SESSION, $USER;

        self::purge();
        $draft = $SESSION->local_rubricassistant_drafts[$token] ?? null;
        if (!is_array($draft)
            || (int)($draft['_userid'] ?? 0) !== (int)$USER->id
            || (int)($draft['cmid'] ?? 0) !== $cmid) {
            throw new moodle_exception('draftmissing', 'local_rubricassistant');
        }
        return $draft;
    }

    /**
     * Delete one draft.
     *
     * @param string $token Token.
     * @return void
     */
    public static function forget(string $token): void {
        global $SESSION;
        unset($SESSION->local_rubricassistant_drafts[$token]);
    }

    /**
     * Purge expired drafts.
     *
     * @return void
     */
    private static function purge(): void {
        global $SESSION;
        if (empty($SESSION->local_rubricassistant_drafts) || !is_array($SESSION->local_rubricassistant_drafts)) {
            return;
        }
        $cutoff = time() - self::TTL;
        foreach ($SESSION->local_rubricassistant_drafts as $token => $draft) {
            if ((int)($draft['_created'] ?? 0) < $cutoff) {
                unset($SESSION->local_rubricassistant_drafts[$token]);
            }
        }
    }
}
