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

use local_rubricassistant\service\grading_form_extractor;

/**
 * Existing grading-form extraction tests.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grading_form_extractor_test extends \advanced_testcase {
    /**
     * Existing Moodle rubric is read through the advanced grading controller.
     *
     * @return void
     */
    public function test_extract_existing_rubric(): void {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'grade' => 100,
        ]);
        $context = \context_module::instance((int)$assign->cmid);
        $manager = get_grading_manager($context, 'mod_assign', 'submissions');
        $manager->set_active_method('rubric');
        $controller = $manager->get_controller('rubric');

        $definition = new \stdClass();
        $definition->name = 'Essay rubric';
        $definition->description_editor = ['text' => '', 'format' => FORMAT_PLAIN, 'itemid' => 0];
        $definition->description = '';
        $definition->descriptionformat = FORMAT_PLAIN;
        $definition->status = \gradingform_controller::DEFINITION_STATUS_READY;
        $definition->rubric = [
            'options' => \gradingform_rubric_controller::get_default_options(),
            'criteria' => [
                'NEWID1' => [
                    'sortorder' => 0,
                    'description' => 'Evidence quality',
                    'descriptionformat' => FORMAT_PLAIN,
                    'levels' => [
                        'NEWID1' => [
                            'score' => 0,
                            'definition' => 'No relevant evidence',
                            'definitionformat' => FORMAT_PLAIN,
                        ],
                        'NEWID2' => [
                            'score' => 4,
                            'definition' => 'Relevant evidence supports the argument',
                            'definitionformat' => FORMAT_PLAIN,
                        ],
                    ],
                ],
            ],
        ];
        $controller->update_definition($definition);

        $result = grading_form_extractor::extract($context);

        $this->assertTrue($result['supported']);
        $this->assertSame('rubric', $result['active']);
        $this->assertSame('Essay rubric', $result['name']);
        $this->assertCount(1, $result['criteria']);
        $this->assertSame('Evidence quality', $result['criteria'][0]['description']);
        $this->assertSame(4.0, $result['criteria'][0]['maxscore']);
        $this->assertSame(100.0, $result['criteria'][0]['effectiveweight']);
    }

    /**
     * Existing marking guide is normalized through the core guide controller.
     *
     * @return void
     */
    public function test_extract_existing_marking_guide(): void {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'grade' => 100,
        ]);
        $context = \context_module::instance((int)$assign->cmid);
        $manager = get_grading_manager($context, 'mod_assign', 'submissions');
        $manager->set_active_method('guide');
        $controller = $manager->get_controller('guide');

        $definition = new \stdClass();
        $definition->name = 'Essay guide';
        $definition->description_editor = ['text' => '', 'format' => FORMAT_PLAIN, 'itemid' => 0];
        $definition->description = '';
        $definition->descriptionformat = FORMAT_PLAIN;
        $definition->status = \gradingform_controller::DEFINITION_STATUS_READY;
        $definition->guide = [
            'options' => \gradingform_guide_controller::get_default_options(),
            'comments' => [],
            'criteria' => [
                'NEWID1' => [
                    'sortorder' => 0,
                    'shortname' => 'Reasoning',
                    'description' => 'Quality of reasoning',
                    'descriptionformat' => FORMAT_PLAIN,
                    'descriptionmarkers' => 'Look for explicit links between evidence and claims.',
                    'descriptionmarkersformat' => FORMAT_PLAIN,
                    'maxscore' => 10,
                ],
            ],
        ];
        $controller->update_definition($definition);

        $result = grading_form_extractor::extract($context);

        $this->assertTrue($result['supported']);
        $this->assertSame('guide', $result['active']);
        $this->assertCount(1, $result['criteria']);
        $this->assertSame('Reasoning', $result['criteria'][0]['name']);
        $this->assertSame(10.0, $result['criteria'][0]['maxscore']);
        $this->assertSame(100.0, $result['criteria'][0]['effectiveweight']);
    }

}
