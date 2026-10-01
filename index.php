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

/**
 * index.php
 *
 * @package   local_rubricassistant
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

use core\notification;
use local_rubricassistant\access;
use local_rubricassistant\form\request_form;
use local_rubricassistant\service\ai_client;
use local_rubricassistant\service\assignment_extractor;
use local_rubricassistant\service\basis_fingerprint;
use local_rubricassistant\service\draft_integrity;
use local_rubricassistant\service\draft_store;
use local_rubricassistant\service\grading_form_extractor;
use local_rubricassistant\service\prompt_builder;
use local_rubricassistant\service\sanitizer;

$cmid = required_param('cmid', PARAM_INT);
$cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$context = context_module::instance($cm->id);

require_login($course, false, $cm);
access::require_use($context);

$PAGE->set_url('/local/rubricassistant/index.php', ['cmid' => $cmid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('heading', 'local_rubricassistant'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->activityheader->disable();

$assignment = assignment_extractor::extract($cmid);
$existing = grading_form_extractor::extract($context);
$form = new request_form(null, [
    'cmid' => $cmid,
    'activemethod' => $existing['supported'] ? $existing['method'] : null,
    'hascriteria' => !empty($existing['criteria']),
]);

if ($data = $form->get_data()) {
    try {
        $operation = (string)$data->operation;
        $requestedmethod = (string)$data->targetmethod;
        $active = $existing['active'];

        if ($existing['supported'] && $existing['method']) {
            $targetmethod = $existing['method'];
            $canapply = true;
        } else if (empty($active)) {
            $targetmethod = $requestedmethod;
            $canapply = true;
        } else {
            $targetmethod = $requestedmethod;
            $canapply = false;
        }

        $messages = prompt_builder::build(
            $operation,
            $targetmethod,
            $assignment,
            $existing,
            sanitizer::text($data->objectives ?? '', 12000),
            sanitizer::text($data->teachercriteria ?? '', 12000)
        );
        $result = ai_client::generate($messages, $targetmethod, $operation);
        draft_integrity::assert_sources($result, $existing);

        $token = draft_store::put([
            'cmid' => $cmid,
            'operation' => $operation,
            'targetmethod' => $targetmethod,
            'activeatgeneration' => $active,
            'canapply' => $canapply,
            'assignment' => $assignment,
            'existing' => $existing,
            'basisfingerprint' => basis_fingerprint::make($assignment, $existing),
            'result' => $result,
        ]);

        redirect(new moodle_url('/local/rubricassistant/review.php', [
            'cmid' => $cmid,
            'token' => $token,
        ]));
    } catch (Throwable $e) {
        notification::error(get_string('aierror', 'local_rubricassistant', sanitizer::text($e->getMessage(), 2000)));
    }
}

$summary = [
    'assignmentname' => $assignment['name'],
    'statement' => $assignment['statement'],
    'competencies' => $assignment['competencies'],
    'hascompetencies' => !empty($assignment['competencies']),
    'activemethod' => $existing['supported'] && $existing['method']
        ? get_string('method:' . $existing['method'], 'local_rubricassistant')
        : ($existing['active'] ?: get_string('none', 'local_rubricassistant')),
    'supported' => $existing['supported'],
    'unsupported' => !empty($existing['active']) && !$existing['supported'],
    'criteria' => $existing['criteria'],
    'hascriteria' => !empty($existing['criteria']),
];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('heading', 'local_rubricassistant'));
echo $OUTPUT->notification(get_string('nosubmissions', 'local_rubricassistant'), 'info');
echo html_writer::tag('p', get_string('intro', 'local_rubricassistant'));
echo $OUTPUT->render_from_template('local_rubricassistant/summary', $summary);
$form->display();
echo $OUTPUT->footer();
