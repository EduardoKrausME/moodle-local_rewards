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
 * issuance_manager.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards\manager;

use dml_exception;
use stdClass;

/**
 * Issues, exports, and lists reward achievements.
 */
class issuance_manager {
    /**
     * Issues a reward when all configured criteria are satisfied.
     *
     * @param int $cmid The course module id.
     * @param int $userid The user id.
     * @return int
     */
    public static function issue_from_completion($cmid, $userid) {
        global $DB;

        $config = config_manager::get_by_cmid($cmid);
        if (!$config || !config_manager::is_ready($config)) {
            return 0;
        }

        if (!criteria_manager::user_matches_config($config, $userid)) {
            return 0;
        }

        $existing = $DB->get_record("local_rewards_issues", ["cmid" => $cmid, "userid" => $userid]);
        if ($existing) {
            return $existing->id;
        }

        $issue = (object) [
            "configid" => $config->id,
            "badgeid" => $config->badgeid,
            "cmid" => $cmid,
            "courseid" => $config->courseid,
            "userid" => $userid,
            "name" => config_manager::get_effective_name($config),
            "description" => config_manager::get_effective_description($config),
            "publictoken" => hash("sha256", $config->id . ":" . $cmid . ":" . $userid . ":" . microtime(true) . ":" . random_string(20)),
            "popupshown" => 0,
            "timeissued" => time(),
            "timemodified" => time(),
        ];

        return $DB->insert_record("local_rewards_issues", $issue);
    }

    /**
     * Returns the next pending popup issue for a user.
     *
     * @param int $userid The user id.
     * @return stdClass|null
     * @throws dml_exception
     */
    public static function get_pending_popup($userid) {
        global $DB;

        return $DB->get_record_select(
            "local_rewards_issues",
            "userid = :userid AND popupshown = :popupshown",
            [
                "userid" => $userid,
                "popupshown" => 0,
            ],
            "timeissued"
        );
    }

    /**
     * Marks an issue popup as shown.
     *
     * @param int $issueid The issue id.
     * @return void
     */
    public static function mark_popup_shown($issueid) {
        global $DB;

        if (!$issue = self::get_issue($issueid)) {
            return;
        }

        $issue->popupshown = 1;
        $issue->timemodified = time();
        $DB->update_record("local_rewards_issues", $issue);
    }

    /**
     * Returns a single issue.
     *
     * @param int $id The issue id.
     * @return \stdClass|null
     */
    public static function get_issue($id) {
        global $DB;

        return $DB->get_record("local_rewards_issues", ["id" => $id]);
    }

    /**
     * Returns an issue by public token.
     *
     * @param string $token The public token.
     * @return \stdClass|null
     */
    public static function get_issue_by_token($token) {
        global $DB;

        return $DB->get_record("local_rewards_issues", ["publictoken" => $token]);
    }

    /**
     * Returns all issues for one user.
     *
     * @param int $userid The user id.
     * @param int $courseid Restrict to one course when informed.
     * @return array
     */
    public static function get_user_issues($userid, $courseid = 0) {
        global $DB;

        if ($courseid) {
            return $DB->get_records("local_rewards_issues", ["userid" => $userid, "courseid" => $courseid], "timeissued DESC");
        }

        return $DB->get_records("local_rewards_issues", ["userid" => $userid], "timeissued DESC");
    }

    /**
     * Returns all issues for one course.
     *
     * @param int $courseid The course id.
     * @return array
     */
    public static function get_course_issues($courseid) {
        global $DB;

        return $DB->get_records("local_rewards_issues", ["courseid" => $courseid], "timeissued DESC");
    }

    /**
     * Returns whether a user can view an issue.
     *
     * @param \stdClass $issue The issue record.
     * @param int $viewerid The viewer id.
     * @return bool
     */
    public static function user_can_view(\stdClass $issue, $viewerid) {
        if ($issue->userid == $viewerid) {
            return true;
        }

        if (has_capability("local/rewards:manage", \context_system::instance())) {
            return true;
        }

        return has_capability("local/rewards:viewcourse", \context_course::instance($issue->courseid));
    }

    /**
     * Returns whether an issue can be shown publicly.
     *
     * @param \stdClass $issue The issue record.
     * @return bool
     */
    public static function is_public_enabled(\stdClass $issue) {
        $config = config_manager::get_by_cmid($issue->cmid);
        return !empty($config->publicenabled);
    }

    /**
     * Exports one issue to a template array.
     *
     * @param \stdClass $issue The issue record.
     * @param bool $forpublic Whether this export is for the public page.
     * @return array
     */
    public static function export_issue(\stdClass $issue, $forpublic = false) {
        $user = \core_user::get_user($issue->userid, "id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename, email");
        $course = get_course($issue->courseid);
        $cm = get_coursemodule_from_id(null, $issue->cmid, 0, false, MUST_EXIST);

        $shareurl = self::get_public_url($issue);
        if (!$forpublic && !self::is_public_enabled($issue)) {
            $shareurl = "";
        }

        $sharetext = self::build_share_text($issue, $course);

        return [
            "id" => $issue->id,
            "name" => format_string($issue->name),
            "description" => format_text($issue->description, FORMAT_HTML),
            "studentname" => fullname($user),
            "studentemail" => s($user->email),
            "coursename" => format_string($course->fullname),
            "activityname" => format_string($cm->name),
            "timeissued" => userdate($issue->timeissued),
            "viewurl" => $forpublic ? $shareurl : self::get_view_url($issue),
            "showviewbutton" => !$forpublic,
            "publicurl" => $shareurl,
            "haspublicurl" => $shareurl != "",
            "imageurl" => self::get_issue_image_url($issue),
            "shareimageurl" => self::get_personalized_image_url($issue),
            "linkedinurl" => self::build_linkedin_url($issue),
            "sharetext" => $sharetext,
            "allbadgesurl" => (new \moodle_url("/local/rewards/my.php", ["userid" => $issue->userid, "courseid" => $issue->courseid]))->out(false),
            "publicenabled" => self::is_public_enabled($issue),
            "issueimagealt" => get_string("rewardissueimagealt", "local_rewards"),
        ];
    }

    /**
     * Exports issue cards for one user page.
     *
     * @param int $userid The user id.
     * @param int $courseid Restrict to one course when informed.
     * @return array
     */
    public static function export_issue_cards($userid, $courseid = 0) {
        $cards = [];

        foreach (self::get_user_issues($userid, $courseid) as $issue) {
            $export = self::export_issue($issue);
            $cards[] = [
                "name" => $export["name"],
                "description" => $export["description"],
                "imageurl" => $export["imageurl"],
                "course" => $export["coursename"],
                "activity" => $export["activityname"],
                "timeissued" => $export["timeissued"],
                "viewurl" => $export["viewurl"],
                "linkedinurl" => $export["linkedinurl"],
                "studentname" => $export["studentname"],
            ];
        }

        return $cards;
    }

    /**
     * Exports a course-wide medal table.
     *
     * @param int $courseid The course id.
     * @return array
     */
    public static function export_course_rows($courseid) {
        $rows = [];

        foreach (self::get_course_issues($courseid) as $issue) {
            $export = self::export_issue($issue);
            $rows[] = [
                "name" => $export["name"],
                "studentname" => $export["studentname"],
                "activity" => $export["activityname"],
                "timeissued" => $export["timeissued"],
                "imageurl" => $export["imageurl"],
                "viewurl" => self::get_view_url($issue),
                "linkedinurl" => $export["linkedinurl"],
            ];
        }

        return $rows;
    }

    /**
     * Returns the most appropriate image URL for an issue.
     *
     * @param \stdClass $issue The issue record.
     * @return string
     */
    public static function get_issue_image_url(\stdClass $issue) {
        $config = config_manager::get_by_cmid($issue->cmid);
        if (!$config) {
            return (new \moodle_url("/local/rewards/pix/defaultbadge.svg"))->out(false);
        }

        return config_manager::get_effective_image_url($config);
    }

    /**
     * Returns a URL to the internal reward page.
     *
     * @param \stdClass $issue The issue record.
     * @return string
     */
    public static function get_view_url(\stdClass $issue) {
        return (new \moodle_url("/local/rewards/view.php", ["id" => $issue->id]))->out(false);
    }

    /**
     * Returns a URL to the public reward page.
     *
     * @param \stdClass $issue The issue record.
     * @return string
     */
    public static function get_public_url(\stdClass $issue) {
        return (new \moodle_url("/local/rewards/public.php", ["token" => $issue->publictoken]))->out(false);
    }

    /**
     * Returns a URL to the personalized share image.
     *
     * @param \stdClass $issue The issue record.
     * @return string
     */
    public static function get_personalized_image_url(\stdClass $issue) {
        return (new \moodle_url("/local/rewards/issue_image.php", ["token" => $issue->publictoken]))->out(false);
    }

    /**
     * Builds the LinkedIn share URL.
     *
     * @param \stdClass $issue The issue record.
     * @return string
     */
    public static function build_linkedin_url(\stdClass $issue) {
        $target = self::is_public_enabled($issue) ? self::get_public_url($issue) : self::get_view_url($issue);
        return "https://www.linkedin.com/sharing/share-offsite/?url=" . rawurlencode($target);
    }

    /**
     * Builds a human friendly share text.
     *
     * @param \stdClass $issue The issue record.
     * @param \stdClass $course The course object.
     * @return string
     */
    public static function build_share_text(\stdClass $issue, \stdClass $course) {
        $payload = (object) [
            "name" => format_string($issue->name),
            "course" => format_string($course->fullname),
        ];

        return get_string("rewardlinkedincopy", "local_rewards", $payload);
    }

    /**
     * Issues rewards for all users who already satisfy the criteria and still miss the record.
     *
     * @return void
     */
    public static function repair_missing_issues() {
        global $DB;

        $sql = "SELECT cfg.cmid, cfg.id AS configid, cmc.userid
                  FROM {local_rewards_configs} cfg
                  JOIN {course_modules_completion} cmc
                    ON cmc.coursemoduleid = cfg.cmid
             LEFT JOIN {local_rewards_issues} iss
                    ON iss.cmid = cfg.cmid
                   AND iss.userid = cmc.userid
                 WHERE cfg.enabled = 1
                   AND cmc.completionstate > 0
                   AND iss.id IS NULL";
        $records = $DB->get_records_sql($sql);

        foreach ($records as $record) {
            self::issue_from_completion($record->cmid, $record->userid);
        }
    }
}
