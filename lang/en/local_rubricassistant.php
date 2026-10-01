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
 * local_rubricassistant.php
 *
 * @package   local_rubricassistant
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['accept'] = 'Accept';
$string['acceptchange'] = 'Accept proposed change';
$string['aierror'] = 'The AI request could not be completed: {$a}';
$string['alignment'] = 'Alignment';
$string['applied'] = 'The selected suggestions were applied through the Moodle advanced grading API.';
$string['applyselected'] = 'Apply selected suggestions';
$string['assignment'] = 'Assignment';
$string['back'] = 'Back to assistant';
$string['basischanged'] = 'The assignment or grading form changed after this draft was generated. Generate a new analysis before applying suggestions.';
$string['bridgeunavailable'] = 'The required local_ai_bridge API is not available.';
$string['cannotapply'] = 'This draft can be reviewed but cannot be applied because the current advanced grading method is incompatible.';
$string['competencies'] = 'Competencies';
$string['criterion'] = 'Criterion';
$string['criteriondescription'] = 'Criterion description';
$string['criterionname'] = 'Criterion name';
$string['current'] = 'Current criterion';
$string['currentmethod'] = 'Current advanced grading method';
$string['draftmissing'] = 'This draft is missing, expired or belongs to another session.';
$string['duplicatecriterion'] = 'The AI returned duplicate criteria.';
$string['expires'] = 'Draft expires after one hour or when the session ends.';
$string['findings'] = 'Findings';
$string['generate'] = 'Generate analysis';
$string['heading'] = 'Rubric assistant';
$string['intro'] = 'Create or review an advanced grading form using AI suggestions while keeping every final decision with the teacher.';
$string['invalidairesponse'] = 'The AI response is not valid for Rubric assistant: {$a}';
$string['invalidcriterion'] = 'A criterion returned by the AI is invalid.';
$string['invalidguide'] = 'A marking guide criterion requires a non-empty name, description and positive maximum score.';
$string['invalidjson'] = 'The response must be a JSON object containing criteria, findings and alignment.';
$string['invalidlevels'] = 'A rubric criterion must contain at least two valid, non-duplicate score levels.';
$string['leveldefinition'] = 'Level description';
$string['markers'] = 'Guidance for markers';
$string['maxscore'] = 'Maximum score';
$string['message'] = 'Message';
$string['method:guide'] = 'Marking guide';
$string['method:rubric'] = 'Rubric';
$string['methodchanged'] = 'The advanced grading method changed after this draft was created. Reload the assistant before applying suggestions.';
$string['newcriterion'] = 'New criterion';
$string['nochangesproposed'] = 'The analysis returned no criterion changes to apply. Findings and alignment notes can still be reviewed above.';
$string['nocompetencies'] = 'No linked competencies were found or are visible to this user.';
$string['nocriteria'] = 'No criteria are currently defined.';
$string['none'] = 'None';
$string['nosubmissions'] = 'Student submissions are not read or sent to AI in this version.';
$string['nothingselected'] = 'No criterion was selected.';
$string['objectives'] = 'Additional learning objectives';
$string['objectives_help'] = 'Optional plain-text learning objectives that are not already represented by Moodle competencies.';
$string['operation'] = 'Operation';
$string['operation:compare'] = 'Compare grading form with assignment';
$string['operation:create'] = 'Create a draft';
$string['operation:review'] = 'Review the existing grading form';
$string['pluginname'] = 'Rubric assistant';
$string['privacy:metadata'] = 'The Rubric assistant does not store personal data in its own persistent tables. Drafts are kept only in the current session.';
$string['proposal'] = 'Proposed criterion';
$string['rationale'] = 'Reason for the suggestion';
$string['regradeconfirm'] = 'I understand that structural changes may require existing assessments to be reviewed or regraded.';
$string['regradeconfirmationrequired'] = 'These changes affect the grading structure and there are existing grading instances. Confirm the regrading warning before applying them.';
$string['reject'] = 'Reject';
$string['reviewnotice'] = 'Nothing is written to Moodle until you explicitly select suggestions and apply them.';
$string['reviewtitle'] = 'Review AI suggestions';
$string['rubricassistant:use'] = 'Use the rubric assistant';
$string['score'] = 'Score';
$string['severity'] = 'Severity';
$string['sourcecriterionmissing'] = 'The AI referenced a criterion that is not present in the current grading form.';
$string['statement'] = 'Assignment statement';
$string['summary'] = 'Current grading form summary';
$string['targetmethod'] = 'Grading method';
$string['teachercriteria'] = 'Criteria supplied by the teacher';
$string['teachercriteria_help'] = 'Optional plain-text notes describing criteria, expected evidence, priorities or constraints.';
$string['type'] = 'Type';
$string['unsupportedmethod'] = 'The active advanced grading method is not supported by this version of Rubric assistant.';
$string['weight'] = 'Effective proportion';
