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
 * file_manager.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards\manager;

/**
 * Handles plugin file areas and image URLs.
 */
class file_manager {
    /**
     * Returns supported file areas.
     *
     * @return array
     */
    public static function get_supported_fileareas() {
        return [
            "badgeimage",
            "configimage",
        ];
    }

    /**
     * Serves plugin files.
     *
     * @param stdClass $course The course object.
     * @param stdClass|null $cm The course module.
     * @param \context $context The file context.
     * @param string $filearea The file area.
     * @param array $args The path arguments.
     * @param bool $forcedownload Whether to force download.
     * @param array $options Additional options.
     * @return void
     */
    public static function pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
        if (!in_array($filearea, self::get_supported_fileareas())) {
            send_file_not_found();
        }

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            send_file_not_found();
        }


        $itemid = array_shift($args);
        $filepath = "/" . implode("/", array_slice($args, 0, -1)) . "/";
        $filename = end($args);

        $fs = get_file_storage();
        $file = $fs->get_file($context->id, "local_rewards", $filearea, $itemid, $filepath, $filename);

        if (!$file || $file->is_directory()) {
            send_file_not_found();
        }

        send_stored_file($file, 60 * 60 * 24, 0, $forcedownload, $options);
    }

    /**
     * Returns the first image URL from a file area.
     *
     * @param string $filearea The file area name.
     * @param int $itemid The item id.
     * @param bool $absolute Whether the URL should be absolute.
     * @return string
     */
    public static function get_image_url($filearea, $itemid, $absolute = false) {
        $context = \context_system::instance();
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, "local_rewards", $filearea, $itemid, "itemid, filepath, filename", false);

        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }

            $url = \moodle_url::make_pluginfile_url(
                $context->id,
                "local_rewards",
                $filearea,
                $itemid,
                $file->get_filepath(),
                $file->get_filename()
            );

            return $absolute ? $url->out(false) : $url->out(false);
        }

        return (new \moodle_url("/local/rewards/pix/defaultbadge.svg"))->out(false);
    }

    /**
     * Saves draft files to a plugin file area.
     *
     * @param int $draftitemid The draft item id.
     * @param string $filearea The plugin file area.
     * @param int $itemid The target item id.
     * @return void
     */
    public static function save_draft_files($draftitemid, $filearea, $itemid) {
        $context = \context_system::instance();
        file_save_draft_area_files(
            $draftitemid,
            $context->id,
            "local_rewards",
            $filearea,
            $itemid,
            self::get_filemanager_options()
        );
    }

    /**
     * Deletes all files from a plugin file area item.
     *
     * @param string $filearea The file area.
     * @param int $itemid The item id.
     * @return void
     */
    public static function delete_area_files($filearea, $itemid) {
        $context = \context_system::instance();
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, "local_rewards", $filearea, $itemid);
    }

    /**
     * Returns file manager options for badge images.
     *
     * @return array
     */
    public static function get_filemanager_options() {
        return [
            "subdirs" => 0,
            "maxfiles" => 1,
            "accepted_types" => ["web_image"],
            "return_types" => FILE_INTERNAL,
        ];
    }

    /**
     * Creates a draft item from an existing file area.
     *
     * @param string $filearea The file area.
     * @param int $itemid The item id.
     * @return int
     */
    public static function prepare_draft_files($filearea, $itemid) {
        $draftitemid = file_get_unused_draft_itemid();
        file_prepare_draft_area(
            $draftitemid,
            \context_system::instance()->id,
            "local_rewards",
            $filearea,
            $itemid,
            self::get_filemanager_options()
        );

        return $draftitemid;
    }
}
