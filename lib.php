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
 * lib.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Global callbacks for local_rewards.
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewards\form\coursemodule_rewards_manager;
use local_rewards\manager\file_manager;

/**
 * Adds reward controls to the activity settings form.
 *
 * @param mixed $formwrapper The module form wrapper.
 * @param MoodleQuickForm $mform The form instance.
 * @return void
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_rewards_coursemodule_standard_elements($formwrapper, $mform) {
    coursemodule_rewards_manager::add_form_elements($formwrapper, $mform);
}

/**
 * Persists reward settings after the activity settings form is saved.
 *
 * @param stdClass $data Submitted module data.
 * @param stdClass $course The current course.
 * @return stdClass
 */
function local_rewards_coursemodule_edit_post_actions($data, $course) {
    coursemodule_rewards_manager::save_from_coursemodule_data($data, $course);
    return $data;
}

/**
 * Validates reward fields in the activity form.
 *
 * @param array $data Submitted form data.
 * @param array $files Uploaded file data.
 * @return array
 * @throws coding_exception
 */
function local_rewards_coursemodule_validation($data, $files) {
    return coursemodule_rewards_manager::validate_form_data($data, $files);
}

/**
 * Serves badge images from the file API.
 *
 * @param stdClass $course The course object.
 * @param stdClass|null $cm The course module.
 * @param context $context The file context.
 * @param string $filearea The file area.
 * @param array $args Remaining path arguments.
 * @param bool $forcedownload Whether to force download.
 * @param array $options Additional file options.
 * @return void
 */
function local_rewards_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    file_manager::pluginfile(
        $course,
        $cm,
        $context,
        $filearea,
        $args,
        $forcedownload,
        $options
    );
}

/**
 * Adds quick navigation links for users and managers.
 *
 * @param global_navigation $navigation The global navigation.
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
function local_rewards_extend_navigation(global_navigation $navigation) {
    if (!isloggedin() || isguestuser()) {
        return;
    }

    $systemcontext = context_system::instance();
    $myurl = new moodle_url("/local/rewards/my.php");
    $navigation->add(get_string("rewardmybadges", "local_rewards"), $myurl, navigation_node::TYPE_CUSTOM);

    if (has_capability("local/rewards:manage", $systemcontext)) {
        $bankurl = new moodle_url("/local/rewards/bank.php");
        $navigation->add(get_string("rewardmanage", "local_rewards"), $bankurl, navigation_node::TYPE_CUSTOM);
    }
}

/**
 * Adds a badge link to the user profile navigation.
 *
 * @param core_user\output\myprofile\tree $tree The profile tree.
 * @param stdClass $user The profile user.
 * @param bool $iscurrentuser Whether this is the current user profile.
 * @param stdClass|null $course Optional course object.
 * @return void
 * @throws Exception
 */
function local_rewards_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $USER;

    if (!isloggedin() || isguestuser()) {
        return;
    }

    $canview = false;
    $urlparams = ["userid" => $user->id];

    if ($iscurrentuser || $user->id == $USER->id) {
        $canview = true;
    } else if (has_capability("local/rewards:manage", context_system::instance())) {
        $canview = true;
    } else if (!empty($course->id) && has_capability("local/rewards:viewcourse", context_course::instance($course->id))) {
        $canview = true;
        $urlparams["courseid"] = $course->id;
    }

    if (!$canview) {
        return;
    }

    $category = new core_user\output\myprofile\category("local_rewards", get_string("pluginname", "local_rewards"));
    $tree->add_category($category);

    $label = $iscurrentuser ? get_string("rewardmybadges", "local_rewards") : get_string("rewardstudentbadges", "local_rewards");
    $node = new core_user\output\myprofile\node(
        "local_rewards",
        "local_rewards_badges",
        $label,
        null,
        new moodle_url("/local/rewards/my.php", $urlparams)
    );
    $tree->add_node($node);
}

/**
 * Adds a course settings link for the course-wide medal page.
 *
 * @param settings_navigation $settingsnav
 * @param context $context The current context.
 * @return void
 * @throws Exception
 */
function local_rewards_extend_settings_navigation(settings_navigation $settingsnav, $context) {
    if (empty($context) || $context->contextlevel != CONTEXT_COURSE) {
        return;
    }

    if (!has_capability("local/rewards:viewcourse", $context)) {
        return;
    }

    $coursesettingsnode = $settingsnav->find("courseadmin", null);
    if (!$coursesettingsnode) {
        return;
    }

    $url = new moodle_url("/local/rewards/course.php", ["courseid" => $context->instanceid]);
    $node = navigation_node::create(
        get_string("rewardcoursebadges", "local_rewards"),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        "local_rewards_coursebadges",
        new pix_icon("i/award", "")
    );
    $coursesettingsnode->add_node($node);
}
