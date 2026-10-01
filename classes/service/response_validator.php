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
use JsonException;
use moodle_exception;

/**
 * Validate and sanitize strict JSON returned by AI.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class response_validator {
    /**
     * Decode a response.
     *
     * @param string $raw Raw AI text.
     * @param string $method rubric|guide.
     * @param string $operation create|review|compare.
     * @return array
     */
    public static function decode(string $raw, string $method, string $operation): array {
        if (!in_array($method, ['rubric', 'guide'], true)
            || !in_array($operation, ['create', 'review', 'compare'], true)
            || core_text::strlen($raw) > 2000000) {
            self::invalid('invalidjson');
        }

        try {
            $data = json_decode(trim($raw), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new moodle_exception('invalidairesponse', 'local_rubricassistant', '', get_string('invalidjson', 'local_rubricassistant'), $e->getMessage());
        }

        if (!is_array($data)
            || !array_key_exists('criteria', $data)
            || !array_key_exists('findings', $data)
            || !array_key_exists('alignment', $data)
            || !is_array($data['criteria'])
            || !is_array($data['findings'])
            || !is_array($data['alignment'])) {
            throw new moodle_exception('invalidairesponse', 'local_rubricassistant', '', get_string('invalidjson', 'local_rubricassistant'));
        }

        if (count($data['criteria']) > 100 || count($data['findings']) > 200 || count($data['alignment']) > 200) {
            self::invalid('invalidjson');
        }

        $criteria = [];
        $seen = [];
        foreach ($data['criteria'] as $index => $criterion) {
            if (!is_array($criterion)) {
                self::invalid('invalidcriterion');
            }
            $clean = $method === 'rubric'
                ? self::rubric_criterion($criterion, $index)
                : self::guide_criterion($criterion, $index);

            $duplicatekey = core_text::strtolower(trim(($clean['name'] ?? '') . ' ' . $clean['description']));
            $duplicatekey = preg_replace('/\s+/u', ' ', $duplicatekey) ?? $duplicatekey;
            if (isset($seen[$duplicatekey])) {
                self::invalid('duplicatecriterion');
            }
            $seen[$duplicatekey] = true;
            $criteria[] = $clean;
        }

        if ($operation === 'create' && !$criteria) {
            self::invalid('invalidcriterion');
        }

        return [
            'criteria' => $criteria,
            'findings' => self::findings($data['findings']),
            'alignment' => self::alignment($data['alignment']),
        ];
    }

    /**
     * Validate one rubric criterion.
     *
     * @param array $criterion Raw criterion.
     * @param int $index Position.
     * @return array
     */
    private static function rubric_criterion(array $criterion, int $index): array {
        $description = sanitizer::text($criterion['description'] ?? '', 6000);
        if ($description === '' || !isset($criterion['levels']) || !is_array($criterion['levels'])) {
            self::invalid('invalidcriterion');
        }

        if (count($criterion['levels']) > 20) {
            self::invalid('invalidlevels');
        }

        $levels = [];
        $scores = [];
        foreach ($criterion['levels'] as $level) {
            if (!is_array($level) || !isset($level['score']) || !is_numeric($level['score'])) {
                self::invalid('invalidlevels');
            }
            $score = (float)$level['score'];
            $definition = sanitizer::text($level['definition'] ?? '', 4000);
            if (!is_finite($score) || $definition === '' || isset($scores[(string)$score])) {
                self::invalid('invalidlevels');
            }
            $scores[(string)$score] = true;
            $levels[] = ['score' => $score, 'definition' => $definition];
        }
        if (count($levels) < 2) {
            self::invalid('invalidlevels');
        }
        usort($levels, static fn(array $a, array $b): int => $a['score'] <=> $b['score']);

        $sourceid = self::sourceid($criterion['sourceid'] ?? null);
        return [
            'id' => sanitizer::id($criterion['id'] ?? ('c' . ($index + 1))) ?: ('c' . ($index + 1)),
            'sourceid' => $sourceid,
            'operation' => $sourceid ? 'update' : 'add',
            'description' => $description,
            'rationale' => sanitizer::text($criterion['rationale'] ?? '', 3000),
            'levels' => $levels,
        ];
    }

    /**
     * Validate one guide criterion.
     *
     * @param array $criterion Raw criterion.
     * @param int $index Position.
     * @return array
     */
    private static function guide_criterion(array $criterion, int $index): array {
        $name = sanitizer::text($criterion['name'] ?? '', 255);
        $description = sanitizer::text($criterion['description'] ?? '', 6000);
        $maxscore = $criterion['maxscore'] ?? null;
        $maxscore = is_numeric($maxscore) ? (float)$maxscore : 0.0;
        if ($name === '' || $description === '' || !is_finite($maxscore) || $maxscore <= 0) {
            self::invalid('invalidguide');
        }

        $sourceid = self::sourceid($criterion['sourceid'] ?? null);
        return [
            'id' => sanitizer::id($criterion['id'] ?? ('c' . ($index + 1))) ?: ('c' . ($index + 1)),
            'sourceid' => $sourceid,
            'operation' => $sourceid ? 'update' : 'add',
            'name' => $name,
            'description' => $description,
            'markers' => sanitizer::text($criterion['markers'] ?? '', 6000),
            'maxscore' => $maxscore,
            'rationale' => sanitizer::text($criterion['rationale'] ?? '', 3000),
        ];
    }

    /**
     * Normalize source id.
     *
     * @param mixed $value Value.
     * @return int|null
     */
    private static function sourceid(mixed $value): ?int {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return null;
        }
        if (!is_numeric($value) || (int)$value <= 0) {
            self::invalid('invalidcriterion');
        }
        return (int)$value;
    }

    /**
     * Sanitize findings.
     *
     * @param array $items Raw findings.
     * @return array
     */
    private static function findings(array $items): array {
        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $result[] = [
                'type' => sanitizer::id($item['type'] ?? 'other') ?: 'other',
                'severity' => in_array(($item['severity'] ?? ''), ['info', 'warning'], true) ? $item['severity'] : 'info',
                'message' => sanitizer::text($item['message'] ?? '', 4000),
                'criterionids' => self::idlist($item['criterionids'] ?? []),
            ];
        }
        return $result;
    }

    /**
     * Sanitize alignment entries.
     *
     * @param array $items Raw alignment.
     * @return array
     */
    private static function alignment(array $items): array {
        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $status = (string)($item['status'] ?? 'partial');
            $result[] = [
                'source' => sanitizer::id($item['source'] ?? 'assignment') ?: 'assignment',
                'sourceid' => sanitizer::text($item['sourceid'] ?? '', 255),
                'status' => in_array($status, ['aligned', 'partial', 'missing'], true) ? $status : 'partial',
                'criterionids' => self::idlist($item['criterionids'] ?? []),
                'message' => sanitizer::text($item['message'] ?? '', 4000),
            ];
        }
        return $result;
    }

    /**
     * Sanitize a list of local/existing ids.
     *
     * @param mixed $items Items.
     * @return array
     */
    private static function idlist(mixed $items): array {
        if (!is_array($items)) {
            return [];
        }
        $result = [];
        foreach ($items as $item) {
            $id = sanitizer::text($item, 100);
            if ($id !== '') {
                $result[] = $id;
            }
        }
        return array_values(array_unique($result));
    }

    /**
     * Throw a translated validation error.
     *
     * @param string $stringkey Error string key.
     * @return never
     */
    private static function invalid(string $stringkey): never {
        throw new moodle_exception(
            'invalidairesponse',
            'local_rubricassistant',
            '',
            get_string($stringkey, 'local_rubricassistant')
        );
    }
}
