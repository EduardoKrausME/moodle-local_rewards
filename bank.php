<?php

require(__DIR__ . "/../../config.php");

require_login();

$systemcontext = context_system::instance();
require_capability("local/rewards:manage", $systemcontext);

$deleteid = optional_param("delete", 0, PARAM_INT);

$PAGE->set_url("/local/rewards/bank.php");
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string("rewardbanktitle", "local_rewards"));
$PAGE->set_heading(get_string("rewardbanktitle", "local_rewards"));

if ($deleteid && confirm_sesskey()) {
    \local_rewards\manager\badge_bank_manager::delete_badge($deleteid);
    redirect(new moodle_url("/local/rewards/bank.php"));
}

echo $OUTPUT->header();

echo $OUTPUT->render_from_template("local_rewards/bank", [
    "createurl" => (new moodle_url("/local/rewards/badge_edit.php"))->out(false),
    "cards" => \local_rewards\manager\badge_bank_manager::export_bank_cards(),
]);

echo $OUTPUT->footer();
