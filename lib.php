<?php

/**
 * Global callbacks for local_rewards.
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once($CFG->dirroot . "/local/rewards/classes/form/coursemodule_rewards_manager.php");

/**
 * Adds reward controls to the activity settings form.
 *
 * @param mixed $formwrapper The module form wrapper.
 * @param MoodleQuickForm $mform The form instance.
 * @return void
 */
function local_rewards_coursemodule_standard_elements($formwrapper, $mform) {
    \local_rewards\form\coursemodule_rewards_manager::add_form_elements($formwrapper, $mform);
}

/**
 * Persists reward settings after the activity settings form is saved.
 *
 * @param stdClass $data Submitted module data.
 * @param stdClass $course The current course.
 * @return stdClass
 */
function local_rewards_coursemodule_edit_post_actions($data, $course) {
    \local_rewards\form\coursemodule_rewards_manager::save_from_coursemodule_data($data, $course);
    return $data;
}

/**
 * Validates reward fields in the activity form.
 *
 * @param array $data Submitted form data.
 * @param array $files Uploaded file data.
 * @return array
 */
function local_rewards_coursemodule_validation($data, $files) {
    return \local_rewards\form\coursemodule_rewards_manager::validate_form_data($data, $files);
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
    \local_rewards\manager\file_manager::pluginfile(
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
