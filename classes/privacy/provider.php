<?php

namespace local_rewards\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

defined("MOODLE_INTERNAL") || die();

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
     * @param \context $context The context.
     * @return void
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
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $DB->delete_records("local_rewards_issues", ["userid" => $contextlist->get_user()->id]);
    }
}
