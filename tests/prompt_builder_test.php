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

namespace local_rubricassistant;

use local_rubricassistant\service\prompt_builder;

/**
 * Prompt construction tests.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class prompt_builder_test extends \advanced_testcase {
    /**
     * The structured prompt includes design inputs but no student submission payload.
     *
     * @return void
     */
    public function test_create_draft_prompt_has_expected_contract(): void {
        $messages = prompt_builder::build(
            'create',
            'rubric',
            [
                'cmid' => 7,
                'id' => 3,
                'name' => 'Essay',
                'statement' => 'Compare A and B.',
                'grade' => 100,
                'competencies' => [],
            ],
            [
                'active' => null,
                'supported' => false,
                'method' => null,
                'name' => '',
                'criteria' => [],
                'hasactiveinstances' => false,
            ],
            'Explain trade-offs.',
            'Evidence and reasoning.'
        );

        $this->assertCount(2, $messages);
        $this->assertSame('system', $messages[0]['role']);
        $this->assertSame('user', $messages[1]['role']);
        $this->assertStringContainsString('"criteria"', $messages[1]['content']);
        $this->assertStringContainsString('Compare A and B.', $messages[1]['content']);
        $this->assertStringContainsString('Explain trade-offs.', $messages[1]['content']);
        $this->assertStringNotContainsString('"submissions"', $messages[1]['content']);
        $this->assertStringNotContainsString('"submission"', $messages[1]['content']);
    }
}
