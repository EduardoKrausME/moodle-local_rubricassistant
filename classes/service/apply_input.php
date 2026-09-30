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
 * Read and validate teacher-edited criteria from the confirmation form.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class apply_input {
    /**
     * Return only explicitly accepted criteria, including teacher edits.
     *
     * @param array $draft Draft.
     * @return array
     */
    public static function selected(array $draft): array {
        $items = [];
        $method = (string)$draft['targetmethod'];

        foreach (($draft['result']['criteria'] ?? []) as $index => $criterion) {
            $decision = optional_param('c' . $index . '_decision', 'reject', PARAM_ALPHA);
            if ($decision !== 'accept') {
                continue;
            }

            $edited = $criterion;
            $edited['description'] = optional_param(
                'c' . $index . '_description',
                $criterion['description'] ?? '',
                PARAM_TEXT
            );

            if ($method === 'rubric') {
                foreach (($criterion['levels'] ?? []) as $levelindex => $level) {
                    $edited['levels'][$levelindex]['score'] = optional_param(
                        'c' . $index . '_l' . $levelindex . '_score',
                        (float)$level['score'],
                        PARAM_FLOAT
                    );
                    $edited['levels'][$levelindex]['definition'] = optional_param(
                        'c' . $index . '_l' . $levelindex . '_definition',
                        $level['definition'] ?? '',
                        PARAM_TEXT
                    );
                }
            } else {
                $edited['name'] = optional_param('c' . $index . '_name', $criterion['name'] ?? '', PARAM_TEXT);
                $edited['markers'] = optional_param('c' . $index . '_markers', $criterion['markers'] ?? '', PARAM_TEXT);
                $edited['maxscore'] = optional_param(
                    'c' . $index . '_maxscore',
                    (float)($criterion['maxscore'] ?? 0),
                    PARAM_FLOAT
                );
            }
            $items[] = $edited;
        }

        if (!$items) {
            return [];
        }

        $validated = response_validator::decode(json_encode([
            'criteria' => $items,
            'findings' => [],
            'alignment' => [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $method, 'review');

        self::assert_not_duplicate_of_existing($validated['criteria'], $draft['existing']['criteria'] ?? []);
        return $validated['criteria'];
    }

    /**
     * Prevent an accepted suggestion from duplicating another existing criterion.
     *
     * @param array $selected Selected criteria.
     * @param array $existing Existing criteria.
     * @return void
     */
    private static function assert_not_duplicate_of_existing(array $selected, array $existing): void {
        foreach ($selected as $proposal) {
            $proposalkey = self::key($proposal);
            foreach ($existing as $criterion) {
                if ((int)($proposal['sourceid'] ?? 0) === (int)$criterion['id']) {
                    continue;
                }
                if ($proposalkey !== '' && $proposalkey === self::key($criterion)) {
                    throw new moodle_exception(
                        'invalidairesponse',
                        'local_rubricassistant',
                        '',
                        get_string('duplicatecriterion', 'local_rubricassistant')
                    );
                }
            }
        }
    }

    /**
     * Build a normalized duplicate-detection key.
     *
     * @param array $criterion Criterion.
     * @return string
     */
    private static function key(array $criterion): string {
        $value = trim(($criterion['name'] ?? '') . ' ' . ($criterion['description'] ?? ''));
        $value = \core_text::strtolower($value);
        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }
}
