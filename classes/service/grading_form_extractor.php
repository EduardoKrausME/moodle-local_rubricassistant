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

namespace local_rubricassistant\service;

use context_module;

/**
 * Read rubric and marking-guide definitions through the core advanced grading API.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grading_form_extractor {
    /**
     * Return the active method and a normalized definition when supported.
     *
     * @param context_module $context Module context.
     * @return array
     */
    public static function extract(context_module $context): array {
        global $CFG;

        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $manager = get_grading_manager($context, 'mod_assign', 'submissions');
        $method = $manager->get_active_method();
        $supported = in_array($method, ['rubric', 'guide'], true);

        if (!$supported) {
            return [
                'active' => $method,
                'supported' => false,
                'method' => null,
                'name' => '',
                'criteria' => [],
                'hasactiveinstances' => false,
            ];
        }

        $controller = $manager->get_controller($method);
        $definition = $controller->get_definition();
        if (!$definition) {
            return [
                'active' => $method,
                'supported' => true,
                'method' => $method,
                'name' => '',
                'criteria' => [],
                'hasactiveinstances' => false,
            ];
        }

        $criteria = $method === 'rubric'
            ? self::rubric_criteria($definition->rubric_criteria ?? [])
            : self::guide_criteria($definition->guide_criteria ?? []);

        self::add_effective_weights($criteria, $method);

        return [
            'active' => $method,
            'supported' => true,
            'method' => $method,
            'name' => sanitizer::text($definition->name ?? '', 255),
            'criteria' => $criteria,
            'hasactiveinstances' => $controller->has_active_instances(),
        ];
    }

    /**
     * Normalize rubric criteria.
     *
     * @param array $criteria Raw rubric criteria.
     * @return array
     */
    private static function rubric_criteria(array $criteria): array {
        $result = [];
        foreach ($criteria as $id => $criterion) {
            $levels = [];
            foreach (($criterion['levels'] ?? []) as $levelid => $level) {
                $levels[] = [
                    'id' => (int)$levelid,
                    'score' => (float)($level['score'] ?? 0),
                    'definition' => sanitizer::text($level['definition'] ?? '', 4000),
                ];
            }
            usort($levels, static fn(array $a, array $b): int => $a['score'] <=> $b['score']);
            $maxscore = $levels ? max(array_column($levels, 'score')) : 0.0;
            $result[] = [
                'id' => (int)$id,
                'description' => sanitizer::text($criterion['description'] ?? '', 6000),
                'levels' => $levels,
                'maxscore' => (float)$maxscore,
            ];
        }
        return $result;
    }

    /**
     * Normalize marking-guide criteria.
     *
     * @param array $criteria Raw guide criteria.
     * @return array
     */
    private static function guide_criteria(array $criteria): array {
        $result = [];
        foreach ($criteria as $id => $criterion) {
            $result[] = [
                'id' => (int)$id,
                'name' => sanitizer::text($criterion['shortname'] ?? '', 255),
                'description' => sanitizer::text($criterion['description'] ?? '', 6000),
                'markers' => sanitizer::text($criterion['descriptionmarkers'] ?? '', 6000),
                'maxscore' => (float)($criterion['maxscore'] ?? 0),
            ];
        }
        return $result;
    }

    /**
     * Add the practical contribution of each criterion to the total score.
     *
     * @param array $criteria Criteria by reference.
     * @param string $method Method.
     * @return void
     */
    private static function add_effective_weights(array &$criteria, string $method): void {
        $total = 0.0;
        foreach ($criteria as $criterion) {
            $total += max(0.0, (float)($criterion['maxscore'] ?? 0));
        }

        foreach ($criteria as &$criterion) {
            $criterion['effectiveweight'] = $total > 0
                ? round(((float)$criterion['maxscore'] / $total) * 100, 2)
                : 0.0;
            $criterion['method'] = $method;
        }
        unset($criterion);
    }
}
