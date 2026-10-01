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

use moodle_url;

/**
 * Prepare escaped-by-Mustache review data without producing HTML.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class review_presenter {
    /**
     * Build template data.
     *
     * @param array $draft Draft.
     * @param string $token Draft token.
     * @return array
     */
    public static function data(array $draft, string $token): array {
        $method = (string)$draft['targetmethod'];
        $existingbyid = [];
        foreach (($draft['existing']['criteria'] ?? []) as $criterion) {
            $existingbyid[(int)$criterion['id']] = $criterion;
        }

        $criteria = [];
        foreach (($draft['result']['criteria'] ?? []) as $index => $proposal) {
            $source = null;
            if (!empty($proposal['sourceid'])) {
                $source = $existingbyid[(int)$proposal['sourceid']] ?? null;
            }

            $item = [
                'index' => $index,
                'id' => $proposal['id'],
                'sourceid' => $proposal['sourceid'] ?? null,
                'isnew' => empty($proposal['sourceid']),
                'description' => $proposal['description'],
                'rationale' => $proposal['rationale'] ?? '',
                'decisionname' => 'c' . $index . '_decision',
                'acceptid' => 'c' . $index . '_accept',
                'rejectid' => 'c' . $index . '_reject',
                'descriptionname' => 'c' . $index . '_description',
                'isrubric' => $method === 'rubric',
                'isguide' => $method === 'guide',
                'currentdescription' => $source['description'] ?? '',
                'currentname' => $source['name'] ?? '',
                'currentmaxscore' => $source['maxscore'] ?? null,
            ];

            if ($method === 'rubric') {
                $item['levels'] = [];
                foreach (($proposal['levels'] ?? []) as $levelindex => $level) {
                    $item['levels'][] = [
                        'score' => $level['score'],
                        'definition' => $level['definition'],
                        'scorename' => 'c' . $index . '_l' . $levelindex . '_score',
                        'definitionname' => 'c' . $index . '_l' . $levelindex . '_definition',
                    ];
                }
                $item['currentlevels'] = $source['levels'] ?? [];
            } else {
                $item['name'] = $proposal['name'] ?? '';
                $item['markers'] = $proposal['markers'] ?? '';
                $item['maxscore'] = $proposal['maxscore'] ?? 0;
                $item['namename'] = 'c' . $index . '_name';
                $item['markersname'] = 'c' . $index . '_markers';
                $item['maxscorename'] = 'c' . $index . '_maxscore';
            }
            $criteria[] = $item;
        }

        return [
            'cmid' => (int)$draft['cmid'],
            'token' => $token,
            'sesskey' => sesskey(),
            'formaction' => (new moodle_url('/local/rubricassistant/apply.php'))->out(false),
            'backurl' => (new moodle_url('/local/rubricassistant/index.php', ['cmid' => $draft['cmid']]))->out(false),
            'methodlabel' => get_string('method:' . $method, 'local_rubricassistant'),
            'criteria' => $criteria,
            'hascriteria' => !empty($criteria),
            'findings' => $draft['result']['findings'] ?? [],
            'hasfindings' => !empty($draft['result']['findings']),
            'alignment' => $draft['result']['alignment'] ?? [],
            'hasalignment' => !empty($draft['result']['alignment']),
            'canapply' => !empty($draft['canapply']) && !empty($criteria),
            'cannotapply' => empty($draft['canapply']),
            'hasactiveinstances' => !empty($draft['existing']['hasactiveinstances']),
        ];
    }
}
