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
use moodle_exception;

/**
 * Apply explicitly accepted suggestions through Moodle advanced grading APIs.
 *
 * @package    local_rubricassistant
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grading_form_writer {
    /**
     * Apply selected criteria after explicit teacher confirmation.
     *
     * @param context_module $context Module context.
     * @param array $draft Session draft.
     * @param array $selected Edited and accepted criteria.
     * @param bool $confirmregrade Whether the teacher accepted the regrade warning.
     * @return int Core change level returned by the grading-form controller.
     */
    public static function apply(context_module $context, array $draft, array $selected, bool $confirmregrade): int {
        global $CFG;

        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $method = (string)$draft['targetmethod'];
        if (!in_array($method, ['rubric', 'guide'], true)) {
            throw new moodle_exception('cannotapply', 'local_rubricassistant');
        }

        $currentassignment = assignment_extractor::extract($context->instanceid);
        $currentgradingform = grading_form_extractor::extract($context);
        $currentfingerprint = basis_fingerprint::make($currentassignment, $currentgradingform);
        if (!hash_equals((string)($draft['basisfingerprint'] ?? ''), $currentfingerprint)) {
            throw new moodle_exception('basischanged', 'local_rubricassistant');
        }

        $manager = get_grading_manager($context, 'mod_assign', 'submissions');
        $active = $manager->get_active_method();
        $draftactive = $draft['activeatgeneration'] ?? null;
        if ($active !== $draftactive) {
            throw new moodle_exception('methodchanged', 'local_rubricassistant');
        }
        if ($active !== null && $active !== '' && $active !== $method) {
            throw new moodle_exception('cannotapply', 'local_rubricassistant');
        }
        $activateafter = empty($active);
        $controller = $manager->get_controller($method);
        self::assert_source_ids_exist($draft, $selected);

        if ($method === 'rubric') {
            $changelevel = self::apply_rubric($controller, $draft, $selected, $confirmregrade);
        } else {
            $changelevel = self::apply_guide($controller, $draft, $selected, $confirmregrade);
        }

        // Only activate a new method after its definition was successfully written.
        if ($activateafter) {
            $manager->set_active_method($method);
        }
        return $changelevel;
    }

    /**
     * Apply rubric changes.
     *
     * @param \gradingform_rubric_controller $controller Core controller.
     * @param array $draft Draft.
     * @param array $selected Selected criteria.
     * @param bool $confirmregrade Confirmation.
     * @return int
     */
    private static function apply_rubric($controller, array $draft, array $selected, bool $confirmregrade): int {
        $definition = $controller->get_definition_for_editing(false);
        if (!$controller->is_form_defined()) {
            $definition = self::new_definition($draft, 'rubric');
            $definition->rubric = [
                'criteria' => [],
                'options' => \gradingform_rubric_controller::get_default_options(),
            ];
        }

        $criteria = $definition->rubric['criteria'] ?? [];
        $nextsort = self::next_sortorder($criteria);
        $newcriterion = 1;
        $newlevel = 1;

        foreach ($selected as $proposal) {
            $sourceid = $proposal['sourceid'] ?? null;
            if ($sourceid && isset($criteria[$sourceid])) {
                $criterion = $criteria[$sourceid];
                $criterion['description'] = sanitizer::text($proposal['description'] ?? '', 6000);
                $criterion['descriptionformat'] = FORMAT_PLAIN;
                $criterion['levels'] = self::merge_rubric_levels(
                    $criterion['levels'] ?? [],
                    $proposal['levels'] ?? [],
                    $newlevel
                );
                $criteria[$sourceid] = $criterion;
                continue;
            }

            $levels = [];
            foreach (($proposal['levels'] ?? []) as $level) {
                $levels['NEWID' . $newlevel++] = [
                    'score' => (float)$level['score'],
                    'definition' => sanitizer::text($level['definition'] ?? '', 4000),
                    'definitionformat' => FORMAT_PLAIN,
                ];
            }
            $criteria['NEWID' . $newcriterion++] = [
                'sortorder' => $nextsort++,
                'description' => sanitizer::text($proposal['description'] ?? '', 6000),
                'descriptionformat' => FORMAT_PLAIN,
                'levels' => $levels,
            ];
        }

        $definition->rubric['criteria'] = $criteria;
        $definition->status = \gradingform_controller::DEFINITION_STATUS_READY;

        $changelevel = $controller->update_or_check_rubric($definition, null, false);
        if ($controller->has_active_instances() && $changelevel >= 3 && !$confirmregrade) {
            throw new moodle_exception('regradeconfirmationrequired', 'local_rubricassistant');
        }
        $definition->rubric['regrade'] = $controller->has_active_instances() && $changelevel >= 3 ? 1 : 0;
        $controller->update_definition($definition);
        return $changelevel;
    }

    /**
     * Apply marking guide changes.
     *
     * @param \gradingform_guide_controller $controller Core controller.
     * @param array $draft Draft.
     * @param array $selected Selected criteria.
     * @param bool $confirmregrade Confirmation.
     * @return int
     */
    private static function apply_guide($controller, array $draft, array $selected, bool $confirmregrade): int {
        $definition = $controller->get_definition_for_editing(false);
        if (!$controller->is_form_defined()) {
            $definition = self::new_definition($draft, 'guide');
            $definition->guide = [
                'criteria' => [],
                'comments' => [],
                'options' => \gradingform_guide_controller::get_default_options(),
            ];
        }

        $criteria = $definition->guide['criteria'] ?? [];
        $nextsort = self::next_sortorder($criteria);
        $newcriterion = 1;

        foreach ($selected as $proposal) {
            $sourceid = $proposal['sourceid'] ?? null;
            $values = [
                'shortname' => sanitizer::text($proposal['name'] ?? '', 255),
                'description' => sanitizer::text($proposal['description'] ?? '', 6000),
                'descriptionformat' => FORMAT_PLAIN,
                'descriptionmarkers' => sanitizer::text($proposal['markers'] ?? '', 6000),
                'descriptionmarkersformat' => FORMAT_PLAIN,
                'maxscore' => (float)($proposal['maxscore'] ?? 0),
            ];

            if ($sourceid && isset($criteria[$sourceid])) {
                $criteria[$sourceid] = array_merge($criteria[$sourceid], $values);
                continue;
            }

            $values['sortorder'] = $nextsort++;
            $criteria['NEWID' . $newcriterion++] = $values;
        }

        $definition->guide['criteria'] = $criteria;
        $definition->guide['comments'] = $definition->guide['comments'] ?? [];
        $definition->status = \gradingform_controller::DEFINITION_STATUS_READY;

        $changelevel = $controller->update_or_check_guide($definition, null, false);
        if ($controller->has_active_instances() && $changelevel >= 3 && !$confirmregrade) {
            throw new moodle_exception('regradeconfirmationrequired', 'local_rubricassistant');
        }
        $definition->guide['regrade'] = $controller->has_active_instances() && $changelevel >= 3 ? 1 : 0;
        $controller->update_definition($definition);
        return $changelevel;
    }

    /**
     * Merge proposed rubric levels while preserving existing level IDs by position where possible.
     *
     * @param array $existing Existing levels.
     * @param array $proposed Proposed levels.
     * @param int $newlevel Next NEWID counter, by reference.
     * @return array
     */
    private static function merge_rubric_levels(array $existing, array $proposed, int &$newlevel): array {
        uasort($existing, static fn(array $a, array $b): int => ((float)$a['score']) <=> ((float)$b['score']));
        $existingids = array_keys($existing);
        $result = [];

        foreach (array_values($proposed) as $index => $level) {
            $key = $existingids[$index] ?? ('NEWID' . $newlevel++);
            $result[$key] = [
                'score' => (float)$level['score'],
                'definition' => sanitizer::text($level['definition'] ?? '', 4000),
                'definitionformat' => FORMAT_PLAIN,
            ];
        }
        return $result;
    }

    /**
     * Create common fields for a new grading definition.
     *
     * @param array $draft Draft.
     * @param string $method Method.
     * @return \stdClass
     */
    private static function new_definition(array $draft, string $method): \stdClass {
        $definition = new \stdClass();
        $assignmentname = sanitizer::text($draft['assignment']['name'] ?? '', 180);
        $definition->name = $assignmentname . ' - ' . ($method === 'rubric' ? 'Rubric' : 'Marking guide');
        $definition->description_editor = [
            'text' => '',
            'format' => FORMAT_PLAIN,
            'itemid' => 0,
        ];
        $definition->description = '';
        $definition->descriptionformat = FORMAT_PLAIN;
        $definition->status = \gradingform_controller::DEFINITION_STATUS_READY;
        return $definition;
    }

    /**
     * Determine the next criterion sort order.
     *
     * @param array $criteria Criteria.
     * @return int
     */
    private static function next_sortorder(array $criteria): int {
        $max = -1;
        foreach ($criteria as $criterion) {
            $max = max($max, (int)($criterion['sortorder'] ?? -1));
        }
        return $max + 1;
    }

    /**
     * Ensure model-provided source IDs still belong to the grading form used to create the draft.
     *
     * @param array $draft Draft.
     * @param array $selected Selected proposals.
     * @return void
     */
    private static function assert_source_ids_exist(array $draft, array $selected): void {
        $existingids = [];
        foreach (($draft['existing']['criteria'] ?? []) as $criterion) {
            $existingids[(int)$criterion['id']] = true;
        }

        foreach ($selected as $proposal) {
            $sourceid = (int)($proposal['sourceid'] ?? 0);
            if ($sourceid > 0 && !isset($existingids[$sourceid])) {
                throw new moodle_exception('sourcecriterionmissing', 'local_rubricassistant');
            }
        }
    }
}
