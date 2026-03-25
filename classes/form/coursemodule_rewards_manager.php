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
 * coursemodule_rewards_manager.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards\form;

use coding_exception;
use context_user;
use local_rewards\manager\badge_bank_manager;
use local_rewards\manager\config_manager;
use local_rewards\manager\criteria_manager;
use local_rewards\manager\file_manager;
use moodle_exception;
use MoodleQuickForm;
use stdClass;

/**
 * Adds and saves reward controls inside activity settings.
 */
class coursemodule_rewards_manager {
    /**
     * Adds reward fields to a module form.
     *
     * @param mixed $formwrapper The module form wrapper.
     * @param MoodleQuickForm $mform The quick form instance.
     * @return void
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function add_form_elements($formwrapper, MoodleQuickForm $mform) {
        $cmid = optional_param("update", 0, PARAM_INT);
        $config = $cmid ? config_manager::get_by_cmid($cmid) : null;
        $draftimage = $config ? file_manager::prepare_draft_files("configimage", $config->id) : file_get_unused_draft_itemid();

        $mform->addElement("header", "local_rewards_header", get_string("rewardsheader", "local_rewards"));

        $mform->addElement("advcheckbox", "rewards_enabled", get_string("rewardsenabled", "local_rewards"));
        $mform->addHelpButton("rewards_enabled", "rewardsenabled_desc", "local_rewards");

        $mform->addElement(
            "select", "rewards_badgeid",
            get_string("rewardbadgeid", "local_rewards"), badge_bank_manager::get_select_options()
        );
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

        $mform->addElement("header", "local_rewards_criteria_header", get_string("rewardcriteriaheader", "local_rewards"));
        $mform->addElement("advcheckbox", "rewards_requirecompletion", get_string("rewardcriterioncompletion", "local_rewards"));
        $mform->setDefault("rewards_requirecompletion", 1);
        $mform->freeze("rewards_requirecompletion");
        $mform->hideIf("local_rewards_criteria_header", "rewards_enabled");

        $mform->addElement("advcheckbox", "rewards_requiremingrade", get_string("rewardcriterionmingrade", "local_rewards"));
        $mform->addElement("text", "rewards_mingrade", get_string("rewardcriterionmingradevalue", "local_rewards"), ["size" => 10]);
        $mform->setType("rewards_mingrade", PARAM_FLOAT);
        $mform->hideIf("rewards_mingrade", "rewards_requiremingrade");

        $mform->addElement("advcheckbox", "rewards_requiresubmission", get_string("rewardcriterionsubmission", "local_rewards"));
        $mform->addElement("advcheckbox", "rewards_requireattemptcompleted", get_string("rewardcriterionattempt", "local_rewards"));
        $mform->addElement("advcheckbox", "rewards_requirequizpass", get_string("rewardcriterionquizpass", "local_rewards"));
        $mform->addElement(
            "advcheckbox", "rewards_requireresourceview",
            get_string("rewardcriterionresourceview", "local_rewards")
        );
        $mform->addElement("advcheckbox", "rewards_requirewithinduedate", get_string("rewardcriterionwithindue", "local_rewards"));

        $details = "<ul class=\"mb-0\">";
        foreach (criteria_manager::get_supported_criteria_descriptions() as $row) {
            $details .= "<li>" . s($row) . "</li>";
        }
        $details .= "</ul>";
        $mform->addElement("static", "rewards_criteria_note", get_string("rewardcriteriahelp", "local_rewards"), $details);
        $mform->addElement("static", "rewards_note", "", get_string("rewardactivitysettingsnote", "local_rewards"));

        $mform->setDefault("rewards_enabled", $config ? $config->enabled : 0);
        $mform->setDefault("rewards_badgeid", $config ? $config->badgeid : 0);
        $mform->setDefault("rewards_name", $config ? $config->customname : "");
        $mform->setDefault("rewards_description", $config ? $config->customdescription : "");
        $mform->setDefault("rewards_image", $draftimage);
        $mform->setDefault("rewards_publicenabled", $config ? $config->publicenabled : 1);
        $mform->setDefault("rewards_requiremingrade", $config ? $config->requiremingrade : 0);
        $mform->setDefault("rewards_mingrade", $config ? $config->mingrade : "");
        $mform->setDefault("rewards_requiresubmission", $config ? $config->requiresubmission : 0);
        $mform->setDefault("rewards_requireattemptcompleted", $config ? $config->requireattemptcompleted : 0);
        $mform->setDefault("rewards_requirequizpass", $config ? $config->requirequizpass : 0);
        $mform->setDefault("rewards_requireresourceview", $config ? $config->requireresourceview : 0);
        $mform->setDefault("rewards_requirewithinduedate", $config ? $config->requirewithinduedate : 0);

        $fieldnames = [
            "rewards_badgeid",
            "rewards_name",
            "rewards_description",
            "rewards_image",
            "rewards_publicenabled",
            "local_rewards_criteria_header",
            "rewards_requirecompletion",
            "rewards_requiremingrade",
            "rewards_mingrade",
            "rewards_requiresubmission",
            "rewards_requireattemptcompleted",
            "rewards_requirequizpass",
            "rewards_requireresourceview",
            "rewards_requirewithinduedate",
            "rewards_criteria_note",
            "rewards_note",
        ];
        foreach ($fieldnames as $fieldname) {
            $mform->hideIf($fieldname, "rewards_enabled");
        }
    }

    /**
     * Saves reward fields after module save.
     *
     * @param stdClass $data Submitted module data.
     * @param stdClass $course Current course.
     * @return void
     */
    public static function save_from_coursemodule_data(stdClass $data, stdClass $course) {
        if (empty($data->coursemodule)) {
            return;
        }

        if (empty($data->rewards_enabled) && empty($data->rewards_badgeid) && empty($data->rewards_name) &&
            empty($data->rewards_description)) {
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
     * @throws coding_exception
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

        if (!empty($data["rewards_requiremingrade"]) && trim($data["rewards_mingrade"] ?? "") == "") {
            $errors["rewards_mingrade"] = get_string("rewardmissingmingrade", "local_rewards");
        }

        return $errors;
    }

    /**
     * Returns whether a draft area contains at least one file.
     *
     * @param int $draftitemid The draft item id.
     * @return bool
     * @throws coding_exception
     */
    protected static function draft_has_files($draftitemid) {
        global $USER;

        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, "user", "draft", $draftitemid, "id", false);

        return !empty($files);
    }
}
