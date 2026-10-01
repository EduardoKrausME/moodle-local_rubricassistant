# Moodle Rubric Assistant

`local_rubricassistant` is a Moodle local plugin that helps teachers create, review and compare advanced grading
criteria with an assignment. AI is used only to propose and explain changes; the teacher remains responsible for every
criterion that is finally applied.

The plugin is designed around Moodle's official advanced grading APIs. It supports Rubric (`gradingform_rubric`) as the
primary method and Marking guide (`gradingform_guide`) when that method is active or selected for a new grading form. It
does not patch Moodle core and it does not write directly to the grading-form database tables.

## What it does

The assistant can create a draft grading form from the assignment statement, linked Moodle competencies, additional
learning objectives, and criteria entered by the teacher. It can also review an existing rubric or marking guide and
compare it with the assignment.

The review asks the AI to look for overlapping criteria, vague language, subjective wording, unclear distinctions
between rubric levels, levels without real progression, assignment requirements that are not assessed, criteria with no
apparent basis in the assignment, and score proportions that may deserve review.

The current form is normalized before it is sent to the bridge. For each existing criterion the payload contains only
the information necessary for the analysis, including the practical percentage represented by its maximum score relative
to the total maximum score.

## Human-in-the-loop workflow

The AI never calls a Moodle grading API and never saves a rubric. Its output is validated and converted into a temporary
session draft.

The teacher then receives a comparison screen where every proposed criterion is independent. A criterion must be
explicitly checked before it can be applied, and its text, level descriptions, scores, marking-guide name, marking
guidance and maximum score can be edited first.

Unselected existing criteria are preserved. A proposal that updates one criterion does not replace the whole grading
form. When a structural Rubric/Marking guide change reaches Moodle's regrading change levels and existing grading
instances are present, the plugin requires an additional explicit regrading confirmation before saving.

If the advanced grading method changes after the AI draft was generated, the draft cannot be applied. If an unsupported
advanced grading method is already active, the analysis can still be reviewed but this plugin does not switch that
method automatically.

## Data sent to AI

the plugin sends only data needed to design or review the grading form:

- assignment name and statement;
- linked competencies visible to the current teacher;
- optional learning objectives typed into the assistant;
- optional teacher criteria/notes typed into the assistant;
- the normalized existing rubric or marking guide, when present.

Student submissions are neither read nor sent. Student grades, feedback, identities and submission files are not
included.

The assistant has no persistent database tables. Drafts are stored only in the current Moodle session and expire after
one hour.

## AI response contract

The bridge response must be strict JSON, with no Markdown fences and no HTML:

```json
{
  "criteria": [],
  "findings": [],
  "alignment": []
}
```

Rubric criteria contain plain-text `description`, `sourceid`, `operation`, `rationale`, and at least two unique numeric
score levels. Marking-guide criteria contain plain-text `name`, `description`, optional marker guidance, and a
positive `maxscore`.

The validator rejects malformed JSON, malformed criteria, duplicate criteria, duplicate rubric scores, invalid source
IDs and references to criteria that do not exist in the grading form used to create the draft. AI text is stripped of
HTML and control characters before display or application.

## Permissions

The plugin link and pages require a module context and all of the following capabilities:

- `local/rubricassistant:use`
- `moodle/grade:managegradingforms`
- `moodle/course:manageactivities`
- `mod/assign:grade`

The plugin capability is granted by default to the editing teacher and manager archetypes. Moodle's existing
capabilities are still checked independently; the custom capability is not a shortcut around core permissions.

`local_ai_bridge` performs its own `local/ai_bridge:use`, tenant, user, purpose, credits and route checks
when `generate()` is called.

## Architecture

Important classes are intentionally small and separated by responsibility:

- `assignment_extractor`: reads the assignment and linked competencies; it does not touch submissions.
- `grading_form_extractor`: reads Rubric/Marking guide through `grading_manager` and their controllers.
- `prompt_builder`: creates the structured bridge request.
- `ai_client`: the only class allowed to invoke `local_ai_bridge`.
- `response_validator`: enforces the strict JSON contract and sanitizes AI output.
- `draft_store`: keeps the reviewed proposal in the current session only.
- `apply_input`: accepts only teacher-selected proposals and revalidates teacher edits.
- `grading_form_writer`: merges accepted criteria and saves through the official Rubric/Marking guide controller APIs.

There are no direct INSERT/UPDATE/DELETE operations against Moodle grading-form tables.
