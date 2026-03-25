<?php

namespace local_rewards\form;

defined("MOODLE_INTERNAL") || die();

require_once($CFG->libdir . "/formslib.php");

/**
 * Form used to create and edit bank badges.
 */
class bank_badge_form extends \moodleform {
    /**
     * Defines form fields.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $options = $this->_customdata["filemanageroptions"];

        $mform->addElement("hidden", "id");
        $mform->setType("id", PARAM_INT);

        $mform->addElement("text", "name", get_string("rewardbankname", "local_rewards"), ["size" => 60]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");

        $mform->addElement("textarea", "description", get_string("rewardbankdescription", "local_rewards"), [
            "rows" => 5,
            "cols" => 60,
        ]);
        $mform->setType("description", PARAM_TEXT);

        $mform->addElement("filemanager", "image", get_string("rewardbankimage", "local_rewards"), null, $options);

        $this->add_action_buttons(true, get_string("rewardsave", "local_rewards"));
    }
}
