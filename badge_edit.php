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
 * badge_edit.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_rewards\form\bank_badge_form;
use local_rewards\manager\badge_bank_manager;
use local_rewards\manager\file_manager;

require(__DIR__ . "/../../config.php");
require_once($CFG->dirroot . "/local/rewards/classes/form/bank_badge_form.php");

$systemcontext = context_system::instance();
require_login();
require_capability("local/rewards:manage", $systemcontext);

$id = optional_param("id", 0, PARAM_INT);
$badge = $id ? badge_bank_manager::get_badge($id) : null;
$draftimage = $badge ? file_manager::prepare_draft_files("badgeimage", $badge->id) : file_get_unused_draft_itemid();

$PAGE->set_url("/local/rewards/badge_edit.php", ["id" => $id]);
$PAGE->set_context($systemcontext);
$PAGE->set_title($badge ? get_string("rewardeditbadge", "local_rewards") : get_string("rewardcreatebadge", "local_rewards"));
$PAGE->set_heading(get_string("rewardbanktitle", "local_rewards"));

$form = new bank_badge_form(null, [
    "filemanageroptions" => file_manager::get_filemanager_options(),
]);

if ($form->is_cancelled()) {
    redirect(new moodle_url("/local/rewards/bank.php"));
}

if ($data = $form->get_data()) {
    $savedata = (object) [
        "id" => $data->id,
        "name" => $data->name,
        "description" => $data->description,
    ];

    badge_bank_manager::save_badge($savedata, $data->image);
    redirect(new moodle_url("/local/rewards/bank.php"));
}

$defaults = (object) [
    "id" => $badge ? $badge->id : 0,
    "name" => $badge ? $badge->name : "",
    "description" => $badge ? $badge->description : "",
    "image" => $draftimage,
];
$form->set_data($defaults);

ob_start();
$form->display();
$formhtml = ob_get_clean();

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/badge_form_page", [
    "title" => $badge ? get_string("rewardeditbadge", "local_rewards") : get_string("rewardcreatebadge", "local_rewards"),
    "backurl" => (new moodle_url("/local/rewards/bank.php"))->out(false),
    "formhtml" => $formhtml,
    "previewurl" => $badge ? file_manager::get_image_url("badgeimage", $badge->id) : "",
    "defaultpreviewurl" => (new moodle_url("/local/rewards/pix/defaultbadge.svg"))->out(false),
]);

echo $OUTPUT->footer();
