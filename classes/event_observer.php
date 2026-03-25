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
 * event_observer.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards;

use core\event\course_module_completion_updated;
use core\event\course_module_deleted;
use core\event\user_deleted;
use dml_exception;
use local_rewards\manager\config_manager;
use local_rewards\manager\issuance_manager;

/**
 * Observes Moodle events relevant to rewards.
 */
class event_observer {
    /**
     * Issues rewards when a module completion changes.
     *
     * @param course_module_completion_updated $event The completion event.
     * @return void
     */
    public static function course_module_completion_updated(course_module_completion_updated $event) {
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
     * @param course_module_deleted $event The delete event.
     * @return void
     */
    public static function course_module_deleted(course_module_deleted $event) {
        config_manager::delete_by_cmid($event->objectid);
    }

    /**
     * Deletes user issue rows after user deletion.
     *
     * @param user_deleted $event The user delete event.
     * @return void
     * @throws dml_exception
     */
    public static function user_deleted(user_deleted $event) {
        global $DB;

        $DB->delete_records("local_rewards_issues", ["userid" => $event->objectid]);
    }
}
