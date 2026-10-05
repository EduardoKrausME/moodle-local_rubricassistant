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

namespace local_rubricassistant\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use moodleform;

/**
 * Main assistant request form.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class request_form extends moodleform {
    /**
     * Define fields.
     *
     * @return void
     */
    protected function definition(): void {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'cmid', $customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $operations = [
            'create' => get_string('operation:create', 'local_rubricassistant'),
            'review' => get_string('operation:review', 'local_rubricassistant'),
            'compare' => get_string('operation:compare', 'local_rubricassistant'),
        ];
        $mform->addElement('select', 'operation', get_string('operation', 'local_rubricassistant'), $operations);
        $mform->setDefault('operation', !empty($customdata['hascriteria']) ? 'review' : 'create');

        $methods = [
            'rubric' => get_string('method:rubric', 'local_rubricassistant'),
            'guide' => get_string('method:guide', 'local_rubricassistant'),
        ];
        $mform->addElement('select', 'targetmethod', get_string('targetmethod', 'local_rubricassistant'), $methods);
        if (!empty($customdata['activemethod']) && isset($methods[$customdata['activemethod']])) {
            $mform->setDefault('targetmethod', $customdata['activemethod']);
            $mform->setConstant('targetmethod', $customdata['activemethod']);
            $mform->hardFreeze('targetmethod');
        }

        $mform->addElement('textarea', 'objectives', get_string('objectives', 'local_rubricassistant'), ['rows' => 5]);
        $mform->setType('objectives', PARAM_TEXT);
        $mform->addHelpButton('objectives', 'objectives', 'local_rubricassistant');

        $mform->addElement('textarea', 'teachercriteria', get_string('teachercriteria', 'local_rubricassistant'), ['rows' => 5]);
        $mform->setType('teachercriteria', PARAM_TEXT);
        $mform->addHelpButton('teachercriteria', 'teachercriteria', 'local_rubricassistant');

        $this->add_action_buttons(false, get_string('generate', 'local_rubricassistant'));
    }

    /**
     * Validate enums.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (!in_array($data['operation'] ?? '', ['create', 'review', 'compare'], true)) {
            $errors['operation'] = get_string('invalidparameter');
        }
        if (!in_array($data['targetmethod'] ?? '', ['rubric', 'guide'], true)) {
            $errors['targetmethod'] = get_string('invalidparameter');
        }
        return $errors;
    }
}
