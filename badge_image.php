<?php

use local_rewards\manager\badge_bank_manager;
use local_rewards\manager\config_manager;
use local_rewards\manager\issuance_manager;
use local_rewards\manager\template_manager;

require(__DIR__ . "/../../config.php");

$badgeid = optional_param("badgeid", 0, PARAM_INT);
$configid = optional_param("configid", 0, PARAM_INT);
$issueid = optional_param("issueid", 0, PARAM_INT);

$badge = null;
$name = "";
$description = "";

if ($issueid) {
    $issue = issuance_manager::get_issue($issueid);
    if ($issue) {
        $badge = badge_bank_manager::get_badge($issue->badgeid);
        $name = $issue->name;
        $description = $issue->description;
    }
} else if ($configid) {
    $config = config_manager::get_by_id($configid);
    if ($config && !empty($config->badgeid)) {
        $badge = badge_bank_manager::get_badge($config->badgeid);
        $name = config_manager::get_effective_name($config);
        $description = config_manager::get_effective_description($config);
    }
} else if ($badgeid) {
    $badge = badge_bank_manager::get_badge($badgeid);
    if ($badge) {
        $name = $badge->name;
        $description = $badge->description;
    }
}

if (!$badge || !template_manager::badge_has_template($badge)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

header("Content-Type: image/svg+xml; charset=utf-8");
echo template_manager::render_template_svg($badge->templatekey, $name, $description);
