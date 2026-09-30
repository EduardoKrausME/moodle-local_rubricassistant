<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Version metadata for local_rubricassistant.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_rubricassistant';
$plugin->version = 2026093001;
$plugin->requires = 2024100700;
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.0';
$plugin->dependencies = [
    'local_ai_bridge' => 2026093001,
];
