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
 * provider.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards\privacy;

use coding_exception;
use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use dml_exception;

/**
 * Privacy provider for local_rewards.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Describes the stored personal data.
     *
     * @param collection $collection The metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection) {
        $collection->add_database_table("local_rewards_issues", [
            "userid" => "privacy:metadata:local_rewards_issues:userid",
            "cmid" => "privacy:metadata:local_rewards_issues:cmid",
            "courseid" => "privacy:metadata:local_rewards_issues:courseid",
            "name" => "privacy:metadata:local_rewards_issues:name",
            "description" => "privacy:metadata:local_rewards_issues:description",
            "publictoken" => "privacy:metadata:local_rewards_issues:publictoken",
            "popupshown" => "privacy:metadata:local_rewards_issues:popupshown",
            "timeissued" => "privacy:metadata:local_rewards_issues:timeissued",
        ], "privacy:metadata");

        return $collection;
    }

    /**
     * Returns contexts that contain data for a user.
     *
     * @param int $userid The user id.
     * @return contextlist
     * @throws dml_exception
     */
    public static function get_contexts_for_userid($userid) {
        global $DB;

        $contextlist = new contextlist();

        if ($DB->record_exists("local_rewards_issues", ["userid" => $userid])) {
            $contextlist->add_context(\context_system::instance());
        }

        return $contextlist;
    }

    /**
     * Exports user data.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $issues = $DB->get_records("local_rewards_issues", ["userid" => $userid], "timeissued DESC");

        if (!$issues) {
            return;
        }

        $records = [];
        foreach ($issues as $issue) {
            $records[] = (object) [
                "name" => $issue->name,
                "description" => $issue->description,
                "cmid" => $issue->cmid,
                "courseid" => $issue->courseid,
                "timeissued" => transform::datetime($issue->timeissued),
            ];
        }

        writer::with_context(\context_system::instance())->export_data(
            [get_string("privacy:path:issues", "local_rewards")],
            (object) ["issues" => $records]
        );
    }

    /**
     * Deletes all data for all users in the provided context.
     *
     * @param context $context The context.
     * @return void
     * @throws dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $DB->delete_records("local_rewards_issues");
    }

    /**
     * Deletes all data for a user within approved contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     * @return void
     * @throws dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $DB->delete_records("local_rewards_issues", ["userid" => $contextlist->get_user()->id]);
    }
}
