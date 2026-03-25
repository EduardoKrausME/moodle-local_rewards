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
 * events.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Event observers.
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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
