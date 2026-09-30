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

/**
 * Permission tests.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class access_test extends \advanced_testcase {
    /**
     * All required capabilities are mandatory in the module context.
     *
     * @return void
     */
    public function test_can_use_requires_all_capabilities(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $user = $this->getDataGenerator()->create_user();
        $context = \context_module::instance((int)$assign->cmid);
        $roleid = create_role('Rubric reviewer', 'rubricreviewer', 'Test role');
        role_assign($roleid, $user->id, $context->id);
        $this->setUser($user);

        $this->assertFalse(access::can_use($context));

        foreach ([
            'local/rubricassistant:use',
            'moodle/grade:managegradingforms',
            'moodle/course:manageactivities',
            'mod/assign:grade',
        ] as $capability) {
            assign_capability($capability, CAP_ALLOW, $roleid, $context->id);
        }
        accesslib_clear_all_caches_for_unit_testing();

        $this->assertTrue(access::can_use($context));
    }

    /**
     * Missing permissions raise the normal Moodle capability exception.
     *
     * @return void
     */
    public function test_require_use_rejects_unprivileged_user(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $context = \context_module::instance((int)$assign->cmid);

        $this->expectException(\required_capability_exception::class);
        access::require_use($context);
    }
}
