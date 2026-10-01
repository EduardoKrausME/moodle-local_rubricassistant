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
use local_rubricassistant\service\assignment_extractor;

/**
 * Assignment extraction tests.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class assignment_extractor_test extends advanced_testcase {
    /**
     * Assignment extraction returns plain text and no submission data.
     *
     * @return void
     */
    public function test_extract_assignment_without_submissions(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Evidence essay',
            'intro' => '<p>Compare <strong>two approaches</strong> and justify the conclusion.</p>',
            'introformat' => FORMAT_HTML,
            'grade' => 100,
        ]);

        $result = assignment_extractor::extract((int)$assign->cmid);

        $this->assertSame('Evidence essay', $result['name']);
        $this->assertStringContainsString('Compare two approaches', $result['statement']);
        $this->assertStringNotContainsString('<strong>', $result['statement']);
        $this->assertArrayNotHasKey('submissions', $result);
        $this->assertArrayNotHasKey('users', $result);
    }
}
