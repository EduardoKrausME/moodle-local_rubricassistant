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

namespace local_rubricassistant;

use advanced_testcase;
use local_rubricassistant\service\response_validator;
use moodle_exception;

/**
 * Tests strict AI response validation.
 *
 * @coversNothing
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class response_validator_test extends advanced_testcase {
    /**
     * A valid rubric draft is normalized and HTML is removed.
     *
     * @return void
     */
    public function test_valid_rubric_draft_is_sanitized(): void {
        $raw = json_encode([
            'criteria' => [[
                'id' => 'clarity',
                'sourceid' => null,
                'operation' => 'add',
                'description' => '<b>Clarity</b> of argument',
                'rationale' => '<script>alert(1)</script>Needed by the task',
                'levels' => [
                    ['score' => 0, 'definition' => '<i>Unclear</i>'],
                    ['score' => 2, 'definition' => 'Clear and supported'],
                ],
            ]],
            'findings' => [],
            'alignment' => [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $result = response_validator::decode($raw, 'rubric', 'create');

        $this->assertCount(1, $result['criteria']);
        $this->assertSame('Clarity of argument', $result['criteria'][0]['description']);
        $this->assertSame('Unclear', $result['criteria'][0]['levels'][0]['definition']);
        $this->assertStringNotContainsString('<', $result['criteria'][0]['rationale']);
    }

    /**
     * Invalid JSON must never be accepted opportunistically.
     *
     * @return void
     */
    public function test_invalid_json_is_rejected(): void {
        $this->expectException(moodle_exception::class);
        $fence = str_repeat(chr(96), 3);
        response_validator::decode($fence . 'json {"criteria": []} ' . $fence, 'rubric', 'review');
    }

    /**
     * Duplicate criteria are rejected.
     *
     * @return void
     */
    public function test_duplicate_criteria_are_rejected(): void {
        $criterion = [
            'description' => 'Evidence quality',
            'levels' => [
                ['score' => 0, 'definition' => 'No evidence'],
                ['score' => 1, 'definition' => 'Relevant evidence'],
            ],
        ];
        $raw = json_encode([
            'criteria' => [$criterion, $criterion],
            'findings' => [],
            'alignment' => [],
        ]);

        $this->expectException(moodle_exception::class);
        response_validator::decode($raw, 'rubric', 'review');
    }

    /**
     * Duplicate rubric level scores are invalid.
     *
     * @return void
     */
    public function test_duplicate_level_scores_are_rejected(): void {
        $raw = json_encode([
            'criteria' => [[
                'description' => 'Clarity',
                'levels' => [
                    ['score' => 1, 'definition' => 'Weak'],
                    ['score' => 1, 'definition' => 'Strong'],
                ],
            ]],
            'findings' => [],
            'alignment' => [],
        ]);

        $this->expectException(moodle_exception::class);
        response_validator::decode($raw, 'rubric', 'review');
    }

    /**
     * Marking guide criteria require valid score data.
     *
     * @return void
     */
    public function test_valid_marking_guide_draft(): void {
        $raw = json_encode([
            'criteria' => [[
                'name' => 'Argument',
                'description' => 'Quality of the argument',
                'markers' => 'Look for evidence and reasoning.',
                'maxscore' => 10,
            ]],
            'findings' => [],
            'alignment' => [],
        ]);

        $result = response_validator::decode($raw, 'guide', 'create');
        $this->assertSame(10.0, $result['criteria'][0]['maxscore']);
        $this->assertSame('Argument', $result['criteria'][0]['name']);
    }
}
