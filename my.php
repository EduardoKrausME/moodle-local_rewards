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
 * my.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewards\manager\issuance_manager;

require(__DIR__ . "/../../config.php");

require_login();

$userid = optional_param("userid", $USER->id, PARAM_INT);
$courseid = optional_param("courseid", 0, PARAM_INT);

$targetuser = core_user::get_user($userid, "id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename", MUST_EXIST);
$isownpage = $userid == $USER->id;
$systemcontext = context_system::instance();

if (!$isownpage) {
    $canview = has_capability("local/rewards:manage", $systemcontext);

    if (!$canview && $courseid) {
        $canview = has_capability("local/rewards:viewcourse", context_course::instance($courseid));
    }

    if (!$canview) {
        throw new required_capability_exception(context_system::instance(), "local/rewards:viewcourse", "nopermissions", "");
    }
}

$title = $isownpage ? get_string("rewardmybadges", "local_rewards") : get_string("rewardstudentbadgespage", "local_rewards", fullname($targetuser));

$PAGE->set_url("/local/rewards/my.php", ["userid" => $userid, "courseid" => $courseid]);
$PAGE->set_context($courseid ? context_course::instance($courseid) : $systemcontext);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$cards = issuance_manager::export_issue_cards($userid, $courseid);

foreach ($cards as $index => $card) {
    if (!$isownpage) {
        $cards[$index]["showstudentname"] = false;
    }
}

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/my_badges", [
    "title" => $title,
    "cards" => $cards,
]);

echo $OUTPUT->footer();
