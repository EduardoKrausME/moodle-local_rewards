<?php

require(__DIR__ . "/../../config.php");

$token = required_param("token", PARAM_ALPHANUMEXT);
$issue = \local_rewards\manager\issuance_manager::get_issue_by_token($token);

if (!$issue) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$export = \local_rewards\manager\issuance_manager::export_issue($issue, true);

$width = 1200;
$height = 630;

$name = s($export["name"]);
$student = s($export["studentname"]);
$course = s($export["coursename"]);
$date = s($export["timeissued"]);
$imageurl = s($export["imageurl"]);

header("Content-Type: image/svg+xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<svg xmlns="http://www.w3.org/2000/svg" width="<?php echo $width; ?>" height="<?php echo $height; ?>" viewBox="0 0 1200 630">
    <defs>
        <linearGradient id="bg" x1="0%" x2="100%" y1="0%" y2="100%">
            <stop offset="0%" stop-color="#eff6ff"/>
            <stop offset="100%" stop-color="#ffffff"/>
        </linearGradient>
    </defs>
    <rect width="1200" height="630" fill="url(#bg)"/>
    <rect x="40" y="40" width="1120" height="550" rx="34" fill="#ffffff" stroke="#dbeafe" stroke-width="2"/>
    <rect x="70" y="70" width="330" height="490" rx="26" fill="#f8fbff"/>
    <image href="<?php echo $imageurl; ?>" x="110" y="135" width="250" height="250" preserveAspectRatio="xMidYMid meet"/>
    <text x="450" y="150" font-family="Arial, Helvetica, sans-serif" font-size="28" font-weight="700" fill="#2563eb">Activity reward</text>
    <text x="450" y="230" font-family="Arial, Helvetica, sans-serif" font-size="58" font-weight="700" fill="#0f172a"><?php echo $name; ?></text>
    <text x="450" y="320" font-family="Arial, Helvetica, sans-serif" font-size="36" font-weight="600" fill="#334155">Awarded to <?php echo $student; ?></text>
    <text x="450" y="390" font-family="Arial, Helvetica, sans-serif" font-size="28" fill="#475569"><?php echo $course; ?></text>
    <text x="450" y="450" font-family="Arial, Helvetica, sans-serif" font-size="24" fill="#64748b">Earned on <?php echo $date; ?></text>
    <text x="70" y="530" font-family="Arial, Helvetica, sans-serif" font-size="22" fill="#94a3b8"><?php echo s($CFG->wwwroot); ?>/local/rewards/public.php?token=<?php echo s($token); ?></text>
</svg>
