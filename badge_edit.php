<?php

require(__DIR__ . "/../../config.php");
require_once($CFG->dirroot . "/local/rewards/classes/form/bank_badge_form.php");

$systemcontext = context_system::instance();
require_login();
require_capability("local/rewards:manage", $systemcontext);

$id = optional_param("id", 0, PARAM_INT);
$badge = $id ? \local_rewards\manager\badge_bank_manager::get_badge($id) : null;
$draftimage = $badge ? \local_rewards\manager\file_manager::prepare_draft_files("badgeimage", $badge->id) : file_get_unused_draft_itemid();

$PAGE->set_url("/local/rewards/badge_edit.php", ["id" => $id]);
$PAGE->set_context($systemcontext);
$PAGE->set_title($badge ? get_string("rewardeditbadge", "local_rewards") : get_string("rewardcreatebadge", "local_rewards"));
$PAGE->set_heading(get_string("rewardbanktitle", "local_rewards"));

$form = new \local_rewards\form\bank_badge_form(null, [
    "filemanageroptions" => \local_rewards\manager\file_manager::get_filemanager_options(),
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

    \local_rewards\manager\badge_bank_manager::save_badge($savedata, $data->image);
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
    "previewurl" => $badge ? \local_rewards\manager\file_manager::get_image_url("badgeimage", $badge->id) : "",
    "defaultpreviewurl" => (new moodle_url("/local/rewards/pix/defaultbadge.svg"))->out(false),
]);

echo $OUTPUT->footer();
