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

use context_module;
use Throwable;

/**
 * Extract assignment data without reading student submissions.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_extractor {
    /**
     * Extract the assignment statement and linked competencies.
     *
     * @param int $cmid Course module id.
     * @return array
     */
    public static function extract(int $cmid): array {
        global $DB;

        $cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        $assign = $DB->get_record('assign', ['id' => $cm->instance], '*', MUST_EXIST);

        $statement = content_to_text((string)$assign->intro, (int)$assign->introformat, ['filter' => false]);

        return [
            'cmid' => (int)$cm->id,
            'id' => (int)$assign->id,
            'name' => sanitizer::text($assign->name, 255),
            'statement' => sanitizer::text($statement, 24000),
            'grade' => is_numeric($assign->grade) ? (float)$assign->grade : null,
            'competencies' => self::extract_competencies($cm, $context),
        ];
    }

    /**
     * Extract competencies linked to the activity when the feature is enabled and visible to the user.
     *
     * @param stdClass $cm Course module.
     * @param context_module $context Context.
     * @return array
     */
    private static function extract_competencies(\stdClass $cm, context_module $context): array {
        if (!class_exists('\\core_competency\\api')) {
            return [];
        }

        if (!has_any_capability([
            'moodle/competency:coursecompetencyview',
            'moodle/competency:coursecompetencymanage',
        ], $context)) {
            return [];
        }

        try {
            $items = \core_competency\api::list_course_module_competencies_in_course_module($cm);
        } catch (Throwable) {
            return [];
        }

        $result = [];
        foreach ($items as $competency) {
            $description = content_to_text(
                (string)$competency->get('description'),
                (int)$competency->get('descriptionformat'),
                ['filter' => false]
            );
            $result[] = [
                'id' => (int)$competency->get('id'),
                'idnumber' => sanitizer::text($competency->get('idnumber'), 255),
                'shortname' => sanitizer::text($competency->get('shortname'), 255),
                'description' => sanitizer::text($description, 6000),
            ];
        }

        return $result;
    }
}
