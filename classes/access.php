<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_rubricassistant;

use context_module;

/**
 * Access checks for the assistant.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class access {
    /**
     * Whether the current user can use the assistant in a module context.
     *
     * @param context_module $context Module context.
     * @return bool
     */
    public static function can_use(context_module $context): bool {
        return has_capability('local/rubricassistant:use', $context)
            && has_capability('moodle/grade:managegradingforms', $context)
            && has_capability('moodle/course:manageactivities', $context)
            && has_capability('mod/assign:grade', $context);
    }

    /**
     * Require every capability needed to review and change an assignment grading form.
     *
     * @param context_module $context Module context.
     * @return void
     */
    public static function require_use(context_module $context): void {
        require_capability('local/rubricassistant:use', $context);
        require_capability('moodle/grade:managegradingforms', $context);
        require_capability('moodle/course:manageactivities', $context);
        require_capability('mod/assign:grade', $context);
    }
}
