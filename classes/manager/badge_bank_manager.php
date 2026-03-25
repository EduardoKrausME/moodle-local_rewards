<?php

namespace local_rewards\manager;

defined("MOODLE_INTERNAL") || die();

/**
 * Manages reusable badge bank items.
 */
class badge_bank_manager {
    /**
     * Returns all bank badges ordered by name.
     *
     * @return array
     */
    public static function get_all_badges() {
        global $DB;

        return $DB->get_records("local_rewards_badges", [], "name ASC");
    }

    /**
     * Returns a single bank badge.
     *
     * @param int $id The badge id.
     * @return \stdClass|null
     */
    public static function get_badge($id) {
        global $DB;

        return $DB->get_record("local_rewards_badges", ["id" => $id]);
    }

    /**
     * Creates or updates a bank badge.
     *
     * @param \stdClass $record The badge data.
     * @param int $draftimageitemid The draft image id.
     * @return int
     */
    public static function save_badge(\stdClass $record, $draftimageitemid = 0) {
        global $DB, $USER;

        $now = time();

        if (!empty($record->id)) {
            $existing = self::get_badge($record->id);
            if (!$existing) {
                throw new \moodle_exception("invaliddata");
            }

            $existing->name = trim($record->name);
            $existing->description = trim($record->description);
            $existing->timemodified = $now;
            $DB->update_record("local_rewards_badges", $existing);
            $badgeid = $existing->id;
        } else {
            $newrecord = (object) [
                "name" => trim($record->name),
                "description" => trim($record->description),
                "createdby" => $USER->id,
                "timecreated" => $now,
                "timemodified" => $now,
            ];
            $badgeid = $DB->insert_record("local_rewards_badges", $newrecord);
        }

        if ($draftimageitemid) {
            file_manager::save_draft_files($draftimageitemid, "badgeimage", $badgeid);
        }

        return $badgeid;
    }

    /**
     * Deletes a bank badge and its files.
     *
     * @param int $id The badge id.
     * @return void
     */
    public static function delete_badge($id) {
        global $DB;

        $DB->set_field("local_rewards_configs", "badgeid", null, ["badgeid" => $id]);
        $DB->delete_records("local_rewards_badges", ["id" => $id]);
        file_manager::delete_area_files("badgeimage", $id);
    }

    /**
     * Returns select options for the activity form.
     *
     * @return array
     */
    public static function get_select_options() {
        $options = [0 => get_string("choose")];

        foreach (self::get_all_badges() as $badge) {
            $options[$badge->id] = format_string($badge->name);
        }

        return $options;
    }

    /**
     * Exports bank badge cards for a template.
     *
     * @return array
     */
    public static function export_bank_cards() {
        $cards = [];

        foreach (self::get_all_badges() as $badge) {
            $cards[] = [
                "id" => $badge->id,
                "name" => format_string($badge->name),
                "description" => format_text($badge->description, FORMAT_HTML),
                "imageurl" => file_manager::get_image_url("badgeimage", $badge->id),
                "editurl" => (new \moodle_url("/local/rewards/badge_edit.php", ["id" => $badge->id]))->out(false),
                "deleteurl" => (new \moodle_url("/local/rewards/bank.php", ["delete" => $badge->id, "sesskey" => sesskey()]))->out(false),
                "deletelabel" => get_string("rewarddeletebadgeconfirm", "local_rewards", format_string($badge->name)),
            ];
        }

        return $cards;
    }
}
