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

require_once(__DIR__ . '/../../config.php');

use local_rubricassistant\access;
use local_rubricassistant\service\draft_store;
use local_rubricassistant\service\review_presenter;

$cmid = required_param('cmid', PARAM_INT);
$token = required_param('token', PARAM_ALPHANUM);
$cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$context = context_module::instance($cm->id);

require_login($course, false, $cm);
access::require_use($context);
$draft = draft_store::get($token, $cmid);

$PAGE->set_url('/local/rubricassistant/review.php', ['cmid' => $cmid, 'token' => $token]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('reviewtitle', 'local_rubricassistant'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->activityheader->disable();

$data = review_presenter::data($draft, $token);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reviewtitle', 'local_rubricassistant'));
echo $OUTPUT->notification(get_string('reviewnotice', 'local_rubricassistant'), 'info');
echo html_writer::tag('p', get_string('expires', 'local_rubricassistant'));
echo $OUTPUT->render_from_template('local_rubricassistant/review', $data);
echo $OUTPUT->footer();
