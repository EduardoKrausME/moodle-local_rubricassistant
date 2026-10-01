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

namespace local_rubricassistant\service;

use core_text;

/**
 * Plain-text sanitization helpers.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class sanitizer {
    /**
     * Convert arbitrary scalar text to safe plain text.
     *
     * @param mixed $value Input value.
     * @param int $maxlength Maximum characters.
     * @return string
     */
    public static function text(mixed $value, int $maxlength = 12000): string {
        if (!is_scalar($value) && $value !== null) {
            return '';
        }

        $text = (string)$value;
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';
        $text = clean_param($text, PARAM_TEXT);
        $text = trim($text);

        if (core_text::strlen($text) > $maxlength) {
            $text = core_text::substr($text, 0, $maxlength);
        }

        return $text;
    }

    /**
     * Normalize a compact identifier supplied by the model.
     *
     * @param mixed $value Input.
     * @return string
     */
    public static function id(mixed $value): string {
        return clean_param((string)$value, PARAM_ALPHANUMEXT);
    }
}
