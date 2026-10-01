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
 * Plugin callbacks for local_rubricassistant.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rubricassistant\access;

/**
 * Add a contextual link while viewing an assignment.
 *
 * @param settings_navigation $settingsnav Settings navigation.
 * @param context $context Current context.
 * @return void
 */
function local_rubricassistant_extend_settings_navigation(settings_navigation $settingsnav, context $context): void {
    global $PAGE;

    if (!$context instanceof context_module || empty($PAGE->cm) || $PAGE->cm->modname !== 'assign') {
        return;
    }

    if (!access::can_use($context)) {
        return;
    }

    $url = new moodle_url('/local/rubricassistant/index.php', ['cmid' => $PAGE->cm->id]);
    $settingsnav->add(
        get_string('pluginname', 'local_rubricassistant'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'local_rubricassistant'
    );
}
