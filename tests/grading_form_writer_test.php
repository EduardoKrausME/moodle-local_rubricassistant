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
use context_module;
use gradingform_controller;
use gradingform_rubric_controller;
use local_rubricassistant\service\assignment_extractor;
use local_rubricassistant\service\basis_fingerprint;
use local_rubricassistant\service\grading_form_extractor;
use local_rubricassistant\service\grading_form_writer;
use stdClass;

/**
 * Human-confirmed grading-form application tests.
 *
 * @coversNothing
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grading_form_writer_test extends advanced_testcase {
    /**
     * Applying one accepted update preserves every unselected criterion.
     *
     * @return void
     */
    public function test_apply_one_criterion_preserves_unselected_criteria(): void {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $context = context_module::instance((int)$assign->cmid);
        $manager = get_grading_manager($context, 'mod_assign', 'submissions');
        $manager->set_active_method('rubric');
        $controller = $manager->get_controller('rubric');

        $definition = new stdClass();
        $definition->name = 'Existing rubric';
        $definition->description_editor = ['text' => '', 'format' => FORMAT_PLAIN, 'itemid' => 0];
        $definition->status = gradingform_controller::DEFINITION_STATUS_READY;
        $definition->rubric = [
            'options' => gradingform_rubric_controller::get_default_options(),
            'criteria' => [
                'NEWID1' => $this->rubric_criterion(0, 'Evidence', 'No evidence', 'Strong evidence'),
                'NEWID2' => $this->rubric_criterion(1, 'Structure', 'Unclear structure', 'Clear structure'),
            ],
        ];
        $controller->update_definition($definition);

        $before = grading_form_extractor::extract($context);
        $this->assertCount(2, $before['criteria']);
        $evidenceid = $before['criteria'][0]['id'];
        $structureid = $before['criteria'][1]['id'];

        $assignmentsnapshot = assignment_extractor::extract((int)$assign->cmid);
        $draft = [
            'targetmethod' => 'rubric',
            'activeatgeneration' => 'rubric',
            'assignment' => ['name' => 'Assignment'],
            'existing' => $before,
            'basisfingerprint' => basis_fingerprint::make($assignmentsnapshot, $before),
        ];
        $selected = [[
            'id' => 'evidence-update',
            'sourceid' => $evidenceid,
            'operation' => 'update',
            'description' => 'Use of evidence',
            'rationale' => 'More specific',
            'levels' => [
                ['score' => 0.0, 'definition' => 'No relevant evidence'],
                ['score' => 4.0, 'definition' => 'Relevant evidence is integrated into the argument'],
            ],
        ]];

        grading_form_writer::apply($context, $draft, $selected, false);
        $after = grading_form_extractor::extract($context);

        $this->assertCount(2, $after['criteria']);
        $byid = array_column($after['criteria'], null, 'id');
        $this->assertSame('Use of evidence', $byid[$evidenceid]['description']);
        $this->assertSame('Structure', $byid[$structureid]['description']);
    }

    /**
     * Build one rubric criterion fixture.
     *
     * @param int $sortorder Sort order.
     * @param string $description Description.
     * @param string $low Low level.
     * @param string $high High level.
     * @return array
     */
    private function rubric_criterion(int $sortorder, string $description, string $low, string $high): array {
        return [
            'sortorder' => $sortorder,
            'description' => $description,
            'descriptionformat' => FORMAT_PLAIN,
            'levels' => [
                'NEWID' . ($sortorder * 2 + 1) => [
                    'score' => 0,
                    'definition' => $low,
                    'definitionformat' => FORMAT_PLAIN,
                ],
                'NEWID' . ($sortorder * 2 + 2) => [
                    'score' => 4,
                    'definition' => $high,
                    'definitionformat' => FORMAT_PLAIN,
                ],
            ],
        ];
    }
}
