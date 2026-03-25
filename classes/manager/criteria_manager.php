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
 * criteria_manager.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards\manager;

/**
 * Evaluates configured badge criteria for one user and one activity.
 */
class criteria_manager {
    /**
     * Returns whether all configured criteria are satisfied.
     *
     * @param \stdClass $config The reward config.
     * @param int $userid The user id.
     * @return bool
     */
    public static function user_matches_config(\stdClass $config, $userid) {
        if (empty($config->enabled)) {
            return false;
        }

        if (!self::has_required_completion($config, $userid)) {
            return false;
        }

        $cm = get_coursemodule_from_id(null, $config->cmid, 0, false, MUST_EXIST);

        if (!empty($config->requiremingrade) && !self::has_minimum_grade($cm, $userid, $config->mingrade)) {
            return false;
        }

        if (!empty($config->requiresubmission) && !self::has_submission($cm, $userid)) {
            return false;
        }

        if (!empty($config->requireattemptcompleted) && !self::has_completed_attempt($cm, $userid)) {
            return false;
        }

        if (!empty($config->requirequizpass) && !self::has_quiz_passed($cm, $userid)) {
            return false;
        }

        if (!empty($config->requireresourceview) && !self::has_resource_view($cm, $userid)) {
            return false;
        }

        if (!empty($config->requirewithinduedate) && !self::is_completed_within_due_date($cm, $userid)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the criteria rows for display in the activity form.
     *
     * @return array
     */
    public static function get_supported_criteria_descriptions() {
        return [
            get_string("rewardcriterioncompletion_desc", "local_rewards"),
            get_string("rewardcriterionmingrade_desc", "local_rewards"),
            get_string("rewardcriterionsubmission_desc", "local_rewards"),
            get_string("rewardcriterionattempt_desc", "local_rewards"),
            get_string("rewardcriterionquizpass_desc", "local_rewards"),
            get_string("rewardcriterionresourceview_desc", "local_rewards"),
            get_string("rewardcriterionwithindue_desc", "local_rewards"),
        ];
    }

    /**
     * Returns a configured due timestamp for a module when possible.
     *
     * @param \stdClass $cm The course module.
     * @return int
     */
    public static function get_module_due_timestamp(\stdClass $cm) {
        global $DB;

        $map = [
            "assign" => ["assign", ["duedate"]],
            "quiz" => ["quiz", ["timeclose"]],
            "choice" => ["choice", ["timeclose"]],
            "feedback" => ["feedback", ["timeclose"]],
            "lesson" => ["lesson", ["deadline"]],
            "workshop" => ["workshop", ["submissionend", "assessmentend"]],
            "scorm" => ["scorm", ["timeclose"]],
        ];

        if (empty($map[$cm->modname])) {
            return 0;
        }

        [$table, $fields] = $map[$cm->modname];
        $record = $DB->get_record($table, ["id" => $cm->instance]);
        if (!$record) {
            return 0;
        }

        foreach ($fields as $fieldname) {
            if (!empty($record->{$fieldname})) {
                return $record->{$fieldname};
            }
        }

        return 0;
    }

    /**
     * Returns whether the activity completion is marked.
     *
     * @param \stdClass $config The reward config.
     * @param int $userid The user id.
     * @return bool
     */
    protected static function has_required_completion(\stdClass $config, $userid) {
        global $DB;

        $completion = $DB->get_record("course_modules_completion", [
            "coursemoduleid" => $config->cmid,
            "userid" => $userid,
        ]);

        if (!$completion) {
            return false;
        }

        return !empty($completion->completionstate);
    }

    /**
     * Returns the completion timestamp for a user in one module.
     *
     * @param int $cmid The course module id.
     * @param int $userid The user id.
     * @return int
     */
    protected static function get_completion_time($cmid, $userid) {
        global $DB;

        $completion = $DB->get_record("course_modules_completion", [
            "coursemoduleid" => $cmid,
            "userid" => $userid,
        ]);

        if (!$completion) {
            return 0;
        }

        return !empty($completion->timemodified) ? $completion->timemodified : 0;
    }

    /**
     * Returns whether the user reached the configured minimum grade.
     *
     * @param \stdClass $cm The course module.
     * @param int $userid The user id.
     * @param float $mingrade The configured minimum grade.
     * @return bool
     */
    protected static function has_minimum_grade(\stdClass $cm, $userid, $mingrade) {
        global $DB;

        $gradeitem = $DB->get_record("grade_items", [
            "courseid" => $cm->course,
            "itemtype" => "mod",
            "itemmodule" => $cm->modname,
            "iteminstance" => $cm->instance,
            "itemnumber" => 0,
        ]);

        if (!$gradeitem) {
            return false;
        }

        $gradegrade = $DB->get_record("grade_grades", [
            "itemid" => $gradeitem->id,
            "userid" => $userid,
        ]);

        if (!$gradegrade) {
            return false;
        }

        $finalgrade = $gradegrade->finalgrade;
        if (is_null($finalgrade)) {
            $finalgrade = $gradegrade->rawgrade;
        }

        if (is_null($finalgrade)) {
            return false;
        }

        return $finalgrade >= $mingrade;
    }

    /**
     * Returns whether the user has a valid submission for this activity.
     *
     * @param \stdClass $cm The course module.
     * @param int $userid The user id.
     * @return bool
     */
    protected static function has_submission(\stdClass $cm, $userid) {
        global $DB;

        if ($cm->modname == "assign") {
            return $DB->record_exists_select(
                "assign_submission",
                "assignment = :assignment AND userid = :userid AND latest = :latest AND status <> :status",
                [
                    "assignment" => $cm->instance,
                    "userid" => $userid,
                    "latest" => 1,
                    "status" => "new",
                ]
            );
        }

        return false;
    }

    /**
     * Returns whether the user has a finished attempt.
     *
     * @param \stdClass $cm The course module.
     * @param int $userid The user id.
     * @return bool
     */
    protected static function has_completed_attempt(\stdClass $cm, $userid) {
        global $DB;

        if ($cm->modname == "quiz") {
            return $DB->record_exists("quiz_attempts", [
                "quiz" => $cm->instance,
                "userid" => $userid,
                "state" => "finished",
            ]);
        }

        return false;
    }

    /**
     * Returns whether the user passed a quiz according to the grade pass value.
     *
     * @param \stdClass $cm The course module.
     * @param int $userid The user id.
     * @return bool
     */
    protected static function has_quiz_passed(\stdClass $cm, $userid) {
        global $DB;

        if ($cm->modname != "quiz") {
            return false;
        }

        $gradeitem = $DB->get_record("grade_items", [
            "courseid" => $cm->course,
            "itemtype" => "mod",
            "itemmodule" => "quiz",
            "iteminstance" => $cm->instance,
            "itemnumber" => 0,
        ]);

        if (!$gradeitem || empty($gradeitem->gradepass)) {
            return false;
        }

        return self::has_minimum_grade($cm, $userid, $gradeitem->gradepass);
    }

    /**
     * Returns whether the user viewed the resource activity.
     *
     * @param \stdClass $cm The course module.
     * @param int $userid The user id.
     * @return bool
     */
    protected static function has_resource_view(\stdClass $cm, $userid) {
        global $DB;

        $supportedmodules = ["resource", "page", "url", "folder", "book"];
        if (!in_array($cm->modname, $supportedmodules)) {
            return false;
        }

        if ($DB->get_manager()->table_exists("course_modules_viewed")) {
            if ($DB->record_exists("course_modules_viewed", [
                "coursemoduleid" => $cm->id,
                "userid" => $userid,
            ])) {
                return true;
            }
        }

        return self::get_completion_time($cm->id, $userid) > 0;
    }

    /**
     * Returns whether completion happened on or before the due date.
     *
     * @param \stdClass $cm The course module.
     * @param int $userid The user id.
     * @return bool
     */
    protected static function is_completed_within_due_date(\stdClass $cm, $userid) {
        $duedate = self::get_module_due_timestamp($cm);
        if (!$duedate) {
            return false;
        }

        $completiontime = self::get_completion_time($cm->id, $userid);
        if (!$completiontime) {
            return false;
        }

        return $completiontime <= $duedate;
    }
}
