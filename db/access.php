<?php

/**
 * Capability definitions.
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

/**
 * Capability list for local_rewards.
 */
$capabilities = [
    "local/rewards:manage" => [
        "captype" => "write",
        "contextlevel" => CONTEXT_SYSTEM,
        "archetypes" => [
            "manager" => CAP_ALLOW,
            "editingteacher" => CAP_ALLOW,
        ],
    ],
    "local/rewards:viewown" => [
        "captype" => "read",
        "contextlevel" => CONTEXT_SYSTEM,
        "archetypes" => [
            "student" => CAP_ALLOW,
            "teacher" => CAP_ALLOW,
            "editingteacher" => CAP_ALLOW,
            "manager" => CAP_ALLOW,
        ],
    ],
    "local/rewards:viewpublic" => [
        "captype" => "read",
        "contextlevel" => CONTEXT_SYSTEM,
        "archetypes" => [
            "guest" => CAP_ALLOW,
            "user" => CAP_ALLOW,
        ],
    ],
];
