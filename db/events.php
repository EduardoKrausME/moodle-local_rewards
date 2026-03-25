<?php

/**
 * Event observers.
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

/**
 * Event observer definitions.
 */
$observers = [
    [
        "eventname" => "\\core\\event\\course_module_completion_updated",
        "callback" => "\\local_rewards\\event_observer::course_module_completion_updated",
        "priority" => 9999,
        "internal" => false,
    ],
    [
        "eventname" => "\\core\\event\\course_module_deleted",
        "callback" => "\\local_rewards\\event_observer::course_module_deleted",
        "priority" => 9999,
        "internal" => false,
    ],
    [
        "eventname" => "\\core\\event\\user_deleted",
        "callback" => "\\local_rewards\\event_observer::user_deleted",
        "priority" => 9999,
        "internal" => false,
    ],
];
