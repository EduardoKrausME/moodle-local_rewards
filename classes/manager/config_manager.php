<?php

namespace local_rewards\manager;

defined("MOODLE_INTERNAL") || die();

/**
 * Manages reward configuration per course module.
 */
class config_manager {
    /**
     * Returns one config by course module id.
     *
     * @param int $cmid The course module id.
     * @return \stdClass|null
     */
    public static function get_by_cmid($cmid) {
        global $DB;

        return $DB->get_record("local_rewards_configs", ["cmid" => $cmid]);
    }

    /**
     * Creates or updates a course module reward config.
     *
     * @param int $cmid The course module id.
     * @param int $courseid The course id.
     * @param \stdClass $data The submitted data.
     * @return int
     */
    public static function save_for_cmid($cmid, $courseid, \stdClass $data) {
        global $DB;

        $now = time();
        $config = self::get_by_cmid($cmid);

        $payload = (object) [
            "cmid" => $cmid,
            "courseid" => $courseid,
            "enabled" => empty($data->rewards_enabled) ? 0 : 1,
            "badgeid" => !empty($data->rewards_badgeid) ? $data->rewards_badgeid : null,
            "customname" => trim($data->rewards_name ?? ""),
            "customdescription" => trim($data->rewards_description ?? ""),
            "publicenabled" => empty($data->rewards_publicenabled) ? 0 : 1,
            "timemodified" => $now,
        ];

        if ($config) {
            $payload->id = $config->id;
            $DB->update_record("local_rewards_configs", $payload);
            $configid = $config->id;
        } else {
            $payload->timecreated = $now;
            $configid = $DB->insert_record("local_rewards_configs", $payload);
        }

        if (!empty($data->rewards_image)) {
            file_manager::save_draft_files($data->rewards_image, "configimage", $configid);
        }

        if (empty($payload->enabled)) {
            self::clear_issues_for_cmid($cmid);
        }

        return $configid;
    }

    /**
     * Deletes a config and all linked files.
     *
     * @param int $cmid The course module id.
     * @return void
     */
    public static function delete_by_cmid($cmid) {
        global $DB;

        $config = self::get_by_cmid($cmid);
        if (!$config) {
            return;
        }

        $DB->delete_records("local_rewards_issues", ["configid" => $config->id]);
        $DB->delete_records("local_rewards_configs", ["id" => $config->id]);
        file_manager::delete_area_files("configimage", $config->id);
    }

    /**
     * Deletes issued rewards linked to a course module.
     *
     * @param int $cmid The course module id.
     * @return void
     */
    public static function clear_issues_for_cmid($cmid) {
        global $DB;

        $DB->delete_records("local_rewards_issues", ["cmid" => $cmid]);
    }

    /**
     * Returns the effective name for a reward config.
     *
     * @param \stdClass $config The config record.
     * @return string
     */
    public static function get_effective_name(\stdClass $config) {
        if (!empty($config->customname)) {
            return $config->customname;
        }

        if (!empty($config->badgeid)) {
            $badge = badge_bank_manager::get_badge($config->badgeid);
            if ($badge) {
                return $badge->name;
            }
        }

        return "";
    }

    /**
     * Returns the effective description for a reward config.
     *
     * @param \stdClass $config The config record.
     * @return string
     */
    public static function get_effective_description(\stdClass $config) {
        if (!empty($config->customdescription)) {
            return $config->customdescription;
        }

        if (!empty($config->badgeid)) {
            $badge = badge_bank_manager::get_badge($config->badgeid);
            if ($badge) {
                return $badge->description;
            }
        }

        return "";
    }

    /**
     * Returns the effective image URL for a config.
     *
     * @param \stdClass $config The config record.
     * @param bool $absolute Whether the URL should be absolute.
     * @return string
     */
    public static function get_effective_image_url(\stdClass $config, $absolute = false) {
        $configimage = file_manager::get_image_url("configimage", $config->id, $absolute);
        if (!str_contains($configimage, "defaultbadge.svg")) {
            return $configimage;
        }

        if (!empty($config->badgeid)) {
            return file_manager::get_image_url("badgeimage", $config->badgeid, $absolute);
        }

        return $configimage;
    }

    /**
     * Validates whether the config can issue a reward.
     *
     * @param \stdClass $config The config record.
     * @return bool
     */
    public static function is_ready(\stdClass $config) {
        return !empty($config->enabled) && self::get_effective_name($config) != "";
    }
}
