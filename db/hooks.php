<?php

/**
 * Hook callback definitions.
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\hook\output\before_footer_html_generation;

defined("MOODLE_INTERNAL") || die();

/**
 * Hook callback list.
 */
$callbacks = [
    [
        "hook" => before_footer_html_generation::class,
        "callback" => "\\local_rewards\\hook_callbacks::before_footer_html_generation",
        "priority" => 1000,
    ],
];
