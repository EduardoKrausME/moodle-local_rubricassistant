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

/**
 * Build the structured AI request.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class prompt_builder {
    /**
     * Build normalized chat messages.
     *
     * @param string $operation create|review|compare.
     * @param string $method rubric|guide.
     * @param array $assignment Assignment data.
     * @param array $existing Existing grading form.
     * @param string $objectives Teacher objectives.
     * @param string $teachercriteria Teacher criteria.
     * @return array
     */
    public static function build(
        string $operation,
        string $method,
        array $assignment,
        array $existing,
        string $objectives,
        string $teachercriteria
    ): array {
        $schema = $method === 'rubric'
            ? [
                'id' => 'short local identifier',
                'sourceid' => 'existing Moodle criterion id or null',
                'operation' => 'add or update',
                'description' => 'plain text',
                'rationale' => 'plain text',
                'levels' => [
                    ['score' => 0, 'definition' => 'plain text'],
                    ['score' => 1, 'definition' => 'plain text'],
                ],
            ]
            : [
                'id' => 'short local identifier',
                'sourceid' => 'existing Moodle criterion id or null',
                'operation' => 'add or update',
                'name' => 'plain text',
                'description' => 'plain text',
                'markers' => 'plain text guidance for graders',
                'maxscore' => 10,
                'rationale' => 'plain text',
            ];

        $input = [
            'operation' => $operation,
            'targetmethod' => $method,
            'assignment' => [
                'name' => sanitizer::text($assignment['name'] ?? '', 255),
                'statement' => sanitizer::text($assignment['statement'] ?? '', 24000),
                'grade' => $assignment['grade'] ?? null,
                'competencies' => $assignment['competencies'] ?? [],
            ],
            'teacherinput' => [
                'objectives' => sanitizer::text($objectives, 12000),
                'criteria' => sanitizer::text($teachercriteria, 12000),
            ],
            'existinggradingform' => [
                'method' => $existing['method'] ?? null,
                'name' => sanitizer::text($existing['name'] ?? '', 255),
                'criteria' => $existing['criteria'] ?? [],
            ],
        ];

        $contract = [
            'criteria' => [$schema],
            'findings' => [[
                'type' => 'overlap|vague|progression|alignment|weight|subjective|other',
                'severity' => 'info|warning',
                'message' => 'plain text',
                'criterionids' => ['existing or proposed ids'],
            ]],
            'alignment' => [[
                'source' => 'assignment|objective|competency',
                'sourceid' => 'plain text identifier',
                'status' => 'aligned|partial|missing',
                'criterionids' => ['existing or proposed ids'],
                'message' => 'plain text',
            ]],
        ];

        $system = 'You are assisting a teacher with assessment design. '
            . 'Do not grade students, rank students, infer student ability, or make the final pedagogical decision. '
            . 'Never request or invent student submissions. Analyze only the supplied assignment, objectives, competencies, '
            . 'teacher notes and grading-form definition. Return strict JSON only, with no Markdown or HTML. '
            . 'Do not propose automatic saving. Existing criterion IDs must only be used when sourceid is actually present. '
            . 'For reviews, prefer targeted updates and additions; do not remove criteria automatically. '
            . 'Check overlap, vagueness, level progression, assignment alignment, effective score proportions and subjective language.';

        $user = "Required JSON contract:\n"
            . json_encode($contract, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . "\n\nInput:\n"
            . json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
    }
}
