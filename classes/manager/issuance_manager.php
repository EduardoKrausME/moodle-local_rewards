<?php

namespace local_rewards\manager;

defined("MOODLE_INTERNAL") || die();

/**
 * Issues, exports, and lists reward achievements.
 */
class issuance_manager {
    /**
     * Issues a reward when the activity completion requirement is satisfied.
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

        if (!self::user_completed_module($cmid, $userid)) {
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
     * Verifies whether the user completed a course module.
     *
     * @param int $cmid The course module id.
     * @param int $userid The user id.
     * @return bool
     */
    public static function user_completed_module($cmid, $userid) {
        global $DB;

        $completion = $DB->get_record("course_modules_completion", [
            "coursemoduleid" => $cmid,
            "userid" => $userid,
        ]);

        if (!$completion) {
            return false;
        }

        return !empty($completion->completionstate);
    }

    /**
     * Returns the next pending popup issue for a user.
     *
     * @param int $userid The user id.
     * @return \stdClass|null
     * @throws \dml_exception
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
            "timeissued ASC"
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
     * @return array
     */
    public static function get_user_issues($userid) {
        global $DB;

        return $DB->get_records("local_rewards_issues", ["userid" => $userid], "timeissued DESC");
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

        return has_capability("local/rewards:manage", \context_system::instance());
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
        global $DB;

        $config = config_manager::get_by_cmid($issue->cmid);
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
            "allbadgesurl" => (new \moodle_url("/local/rewards/my.php"))->out(false),
            "publicenabled" => self::is_public_enabled($issue),
            "issueimagealt" => get_string("rewardissueimagealt", "local_rewards"),
        ];
    }

    /**
     * Exports issue cards for the current user page.
     *
     * @param int $userid The user id.
     * @return array
     */
    public static function export_issue_cards($userid) {
        $cards = [];

        foreach (self::get_user_issues($userid) as $issue) {
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
            ];
        }

        return $cards;
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
     * Issues rewards for all users who completed a configured activity and still miss the record.
     *
     * @return void
     */
    public static function repair_missing_issues() {
        global $DB;

        $sql = "SELECT cfg.cmid, cmc.userid
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
