<?php

namespace local_rewards\form;

use local_rewards\manager\badge_bank_manager;
use local_rewards\manager\config_manager;
use local_rewards\manager\file_manager;

defined("MOODLE_INTERNAL") || die();

/**
 * Adds and saves reward controls inside activity settings.
 */
class coursemodule_rewards_manager {
    /**
     * Adds reward fields to a module form.
     *
     * @param mixed $formwrapper The module form wrapper.
     * @param \MoodleQuickForm $mform The quick form instance.
     * @return void
     */
    public static function add_form_elements($formwrapper, \MoodleQuickForm $mform) {
        $cmid = optional_param("update", 0, PARAM_INT);
        $config = $cmid ? config_manager::get_by_cmid($cmid) : null;
        $draftimage = $config ? file_manager::prepare_draft_files("configimage", $config->id) : file_get_unused_draft_itemid();

        $mform->addElement("header", "local_rewards_header", get_string("rewardsheader", "local_rewards"));

        $mform->addElement("advcheckbox", "rewards_enabled", get_string("rewardsenabled", "local_rewards"));
        $mform->addHelpButton("rewards_enabled", "rewardsenabled_desc", "local_rewards");

        $mform->addElement("select", "rewards_badgeid", get_string("rewardbadgeid", "local_rewards"), badge_bank_manager::get_select_options());
        $mform->addHelpButton("rewards_badgeid", "rewardbadgeid_desc", "local_rewards");

        $mform->addElement("text", "rewards_name", get_string("rewardname", "local_rewards"), ["size" => 64]);
        $mform->setType("rewards_name", PARAM_TEXT);

        $mform->addElement("textarea", "rewards_description", get_string("rewarddescription", "local_rewards"), [
            "rows" => 4,
            "cols" => 60,
        ]);
        $mform->setType("rewards_description", PARAM_TEXT);

        $mform->addElement(
            "filemanager",
            "rewards_image",
            get_string("rewardimage", "local_rewards"),
            null,
            file_manager::get_filemanager_options()
        );
        $mform->addHelpButton("rewards_image", "rewardbadgeimagehelp", "local_rewards");

        $mform->addElement("advcheckbox", "rewards_publicenabled", get_string("rewardpublicenabled", "local_rewards"));

        $mform->addElement("static", "rewards_criteria", get_string("rewardcriteriafixed", "local_rewards"), get_string("rewardcriteriafixed_desc", "local_rewards"));
        $mform->addElement("static", "rewards_note", "", get_string("rewardactivitysettingsnote", "local_rewards"));

        $mform->setDefault("rewards_enabled", $config ? $config->enabled : 0);
        $mform->setDefault("rewards_badgeid", $config ? $config->badgeid : 0);
        $mform->setDefault("rewards_name", $config ? $config->customname : "");
        $mform->setDefault("rewards_description", $config ? $config->customdescription : "");
        $mform->setDefault("rewards_image", $draftimage);
        $mform->setDefault("rewards_publicenabled", $config ? $config->publicenabled : 1);

        foreach (["rewards_badgeid", "rewards_name", "rewards_description", "rewards_image", "rewards_publicenabled", "rewards_criteria", "rewards_note"] as $fieldname) {
            $mform->hideIf($fieldname, "rewards_enabled", "notchecked");
        }
    }

    /**
     * Saves reward fields after module save.
     *
     * @param \stdClass $data Submitted module data.
     * @param \stdClass $course Current course.
     * @return void
     */
    public static function save_from_coursemodule_data(\stdClass $data, \stdClass $course) {
        if (empty($data->coursemodule)) {
            return;
        }

        if (empty($data->rewards_enabled) && empty($data->rewards_badgeid) && empty($data->rewards_name) && empty($data->rewards_description)) {
            $existing = config_manager::get_by_cmid($data->coursemodule);
            if ($existing) {
                config_manager::delete_by_cmid($data->coursemodule);
            }

            return;
        }

        config_manager::save_for_cmid($data->coursemodule, $course->id, $data);
    }

    /**
     * Validates activity reward fields.
     *
     * @param array $data Submitted form data.
     * @param array $files Uploaded files.
     * @return array
     */
    public static function validate_form_data(array $data, array $files) {
        $errors = [];

        if (empty($data["rewards_enabled"])) {
            return $errors;
        }

        $hasbankbadge = !empty($data["rewards_badgeid"]);
        $hasname = !empty(trim($data["rewards_name"] ?? ""));
        $hasimage = !empty($data["rewards_image"]) && self::draft_has_files($data["rewards_image"]);

        if (!$hasbankbadge && !$hasname) {
            $errors["rewards_name"] = get_string("rewardmissingname", "local_rewards");
        }

        if (!$hasbankbadge && !$hasimage) {
            $errors["rewards_image"] = get_string("rewardrequiredimage", "local_rewards");
        }

        return $errors;
    }

    /**
     * Returns whether a draft area contains at least one file.
     *
     * @param int $draftitemid The draft item id.
     * @return bool
     */
    protected static function draft_has_files($draftitemid) {
        global $USER;

        $usercontext = \context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, "user", "draft", $draftitemid, "id", false);

        return !empty($files);
    }
}
