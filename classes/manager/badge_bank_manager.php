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
 * badge_bank_manager.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards\manager;

use coding_exception;
use dml_exception;
use moodle_url;
use stdClass;

/**
 * Manages reusable badge bank items.
 */
class badge_bank_manager {
    /**
     * Returns all bank badges ordered by name.
     *
     * @return array
     * @throws dml_exception
     */
    public static function get_all_badges() {
        global $DB;

        return $DB->get_records("local_rewards_badges", [], "name ASC");
    }

    /**
     * Returns a single bank badge.
     *
     * @param int $id The badge id.
     * @return stdClass|null
     */
    public static function get_badge($id) {
        global $DB;

        return $DB->get_record("local_rewards_badges", ["id" => $id]);
    }

    /**
     * Creates or updates a bank badge.
     *
     * @param stdClass $record The badge data.
     * @param int $draftimageitemid The draft image id.
     * @return int
     * @throws dml_exception
     * @throws \moodle_exception
     */
    public static function save_badge(stdClass $record, $draftimageitemid = 0) {
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
     * @throws coding_exception
     */
    public static function export_bank_cards() {
        $cards = [];

        foreach (self::get_all_badges() as $badge) {
            $cards[] = [
                "id" => $badge->id,
                "name" => format_string($badge->name),
                "description" => format_text($badge->description, FORMAT_HTML),
                "imageurl" => file_manager::get_image_url("badgeimage", $badge->id),
                "editurl" => new moodle_url("/local/rewards/badge_edit.php", ["id" => $badge->id]),
                "deleteurl" => new moodle_url("/local/rewards/bank.php", ["delete" => $badge->id, "sesskey" => sesskey()]),
                "deletelabel" => get_string("rewarddeletebadgeconfirm", "local_rewards", format_string($badge->name)),
            ];
        }

        return $cards;
    }
}
