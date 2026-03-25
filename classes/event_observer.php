<?php

namespace local_rewards;

use local_rewards\manager\config_manager;
use local_rewards\manager\issuance_manager;

defined("MOODLE_INTERNAL") || die();

/**
 * Observes Moodle events relevant to rewards.
 */
class event_observer {
    /**
     * Issues rewards when a module completion changes.
     *
     * @param \core\event\course_module_completion_updated $event The completion event.
     * @return void
     */
    public static function course_module_completion_updated(\core\event\course_module_completion_updated $event) {
        $cmid = $event->contextinstanceid;
        $userid = $event->userid;

        if (!empty($event->other["relateduserid"])) {
            $userid = $event->other["relateduserid"];
        }

        issuance_manager::issue_from_completion($cmid, $userid);
    }

    /**
     * Removes module config and issues after activity deletion.
     *
     * @param \core\event\course_module_deleted $event The delete event.
     * @return void
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        config_manager::delete_by_cmid($event->objectid);
    }

    /**
     * Deletes user issue rows after user deletion.
     *
     * @param \core\event\user_deleted $event The user delete event.
     * @return void
     */
    public static function user_deleted(\core\event\user_deleted $event) {
        global $DB;

        $DB->delete_records("local_rewards_issues", ["userid" => $event->objectid]);
    }
}
