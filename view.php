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
 * view.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewards\manager\issuance_manager;

require(__DIR__ . "/../../config.php");

require_login();

$id = required_param("id", PARAM_INT);
$issue = issuance_manager::get_issue($id);

if (!$issue) {
    throw new moodle_exception("rewardnotfound", "local_rewards");
}

$viewcontext = context_course::instance($issue->courseid);

if (!issuance_manager::user_can_view($issue, $USER->id)) {
    throw new required_capability_exception($viewcontext, "local/rewards:viewcourse", "nopermissions", "");
}

$PAGE->set_url("/local/rewards/view.php", ["id" => $id]);
$PAGE->set_context($viewcontext);
$PAGE->set_title(format_string($issue->name));
$PAGE->set_heading(get_string("rewardviewbadge", "local_rewards"));

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/award_page", issuance_manager::export_issue($issue));

echo $OUTPUT->footer();
