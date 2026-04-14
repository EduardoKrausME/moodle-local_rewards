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
use moodle_exception;
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
     */
    public static function save_badge(stdClass $record, $draftimageitemid = 0) {
        global $DB, $USER;

        $now = time();

        if (!empty($record->id)) {
            $existing = self::get_badge($record->id);
            if (!$existing) {
                throw new moodle_exception("invaliddata");
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
                "templatekey" => "",
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
     * Returns the best preview image URL for one bank badge.
     *
     * @param stdClass $badge The badge record.
     * @param bool $absolute Whether the URL should be absolute.
     * @return string
     */
    public static function get_badge_image_url(stdClass $badge, $absolute = false) {
        if (template_manager::badge_has_template($badge)) {
            return template_manager::get_badge_preview_url($badge, $absolute);
        }

        return file_manager::get_image_url("badgeimage", $badge->id, $absolute);
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
     * Renders the visual badge grid selector used in activity settings.
     *
     * @param string $fieldname The hidden field name.
     * @param int $selectedid The selected badge id.
     * @return string
     */
    public static function render_grid_select($fieldname, $selectedid = 0) {
        $cards = [];

        $cards[] = [
            "id" => 0,
            "name" => get_string("rewardgridcustom", "local_rewards"),
            "description" => get_string("rewardgridcustom_desc", "local_rewards"),
            "imageurl" => new moodle_url("/local/rewards/pix/defaultbadge.svg"),
            "selected" => $selectedid == 0,
        ];

        foreach (self::get_all_badges() as $badge) {
            $cards[] = [
                "id" => $badge->id,
                "name" => format_string($badge->name),
                "description" => format_text($badge->description, FORMAT_HTML),
                "imageurl" => self::get_badge_image_url($badge),
                "selected" => $selectedid == $badge->id,
            ];
        }

        $content = [];
        $content[] = '<div class="local-rewards-gridselect" data-local-rewards-grid="true">';
        $content[] = '<div class="local-rewards-gridselect__options">';

        foreach ($cards as $card) {
            $selectedclass = $card["selected"] ? " is-selected" : "";
            $content[] = '<button type="button" class="local-rewards-gridselect__option' . $selectedclass . '" data-badge-option="' . $card["id"] . '">';
            $content[] = '<span class="local-rewards-gridselect__visual">';
            $content[] = '<object data="' . $card["imageurl"] . '" type="image/svg+xml" ></object>';
            //$content[] = '<img src="' . $card["imageurl"] . '" alt="">';
            $content[] = '</span>';
            $content[] = '<span class="local-rewards-gridselect__title">' . $card["name"] . '</span>';
            $content[] = '<span class="local-rewards-gridselect__description">' . strip_tags($card["description"]) . '</span>';
            $content[] = '</button>';
        }

        $content[] = '</div>';
        $content[] = '</div>';

        return implode("\n", $content);
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
                "imageurl" => self::get_badge_image_url($badge),
                "editurl" => new moodle_url("/local/rewards/badge_edit.php", ["id" => $badge->id]),
                "deleteurl" => new moodle_url("/local/rewards/bank.php", ["delete" => $badge->id, "sesskey" => sesskey()]),
                "deletelabel" => get_string("rewarddeletebadgeconfirm", "local_rewards", format_string($badge->name)),
            ];
        }

        return $cards;
    }
}
