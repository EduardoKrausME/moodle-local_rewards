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
 * bank_badge_form.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_rewards\form;

use coding_exception;
use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/formslib.php");

/**
 * Form used to create and edit bank badges.
 */
class bank_badge_form extends moodleform {
    /**
     * Defines form fields.
     *
     * @return void
     * @throws coding_exception
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
