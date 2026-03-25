<?php

/**
 * Scheduled tasks.
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

/**
 * Scheduled task list.
 */
$tasks = [
    [
        "classname" => "\\local_rewards\\task\\repair_awards_task",
        "blocking" => 0,
        "minute" => "17",
        "hour" => "*/6",
        "day" => "*",
        "dayofweek" => "*",
        "month" => "*",
    ],
];
