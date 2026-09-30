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
use local_rubricassistant\service\apply_input;
use local_rubricassistant\service\draft_store;
use local_rubricassistant\service\grading_form_writer;
use local_rubricassistant\service\sanitizer;

$cmid = required_param('cmid', PARAM_INT);
$token = required_param('token', PARAM_ALPHANUM);

$cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$context = context_module::instance($cm->id);
require_login($course, false, $cm);
require_sesskey();
access::require_use($context);

$reviewurl = new moodle_url('/local/rubricassistant/review.php', ['cmid' => $cmid, 'token' => $token]);

try {
    $draft = draft_store::get($token, $cmid);
    if (empty($draft['canapply'])) {
        throw new moodle_exception('cannotapply', 'local_rubricassistant');
    }

    $selected = apply_input::selected($draft);
    if (!$selected) {
        redirect($reviewurl, get_string('nothingselected', 'local_rubricassistant'), null, \core\output\notification::NOTIFY_WARNING);
    }

    $confirmregrade = optional_param('confirmregrade', 0, PARAM_BOOL) === 1;
    grading_form_writer::apply($context, $draft, $selected, $confirmregrade);
    draft_store::forget($token);

    redirect(
        new moodle_url('/local/rubricassistant/index.php', ['cmid' => $cmid]),
        get_string('applied', 'local_rubricassistant'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
} catch (Throwable $e) {
    redirect(
        $reviewurl,
        sanitizer::text($e->getMessage(), 2000),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}
