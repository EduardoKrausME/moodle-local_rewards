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
 * Language strings.
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Activity rewards';
$string['privacy:metadata'] = 'The local_rewards plugin stores earned activity reward data.';
$string['privacy:metadata:local_rewards_issues'] = 'Information about issued rewards.';
$string['privacy:metadata:local_rewards_issues:userid'] = 'The user who earned the reward.';
$string['privacy:metadata:local_rewards_issues:cmid'] = 'The course module that triggered the reward.';
$string['privacy:metadata:local_rewards_issues:courseid'] = 'The course that owns the activity.';
$string['privacy:metadata:local_rewards_issues:name'] = 'The issue title snapshot.';
$string['privacy:metadata:local_rewards_issues:description'] = 'The issue description snapshot.';
$string['privacy:metadata:local_rewards_issues:publictoken'] = 'The public token used in the shared page.';
$string['privacy:metadata:local_rewards_issues:popupshown'] = 'Whether the reward popup was already shown.';
$string['privacy:metadata:local_rewards_issues:timeissued'] = 'The time when the reward was issued.';
$string['privacy:path:issues'] = 'Issued rewards';

$string['rewardsheader'] = 'Reward badge';
$string['rewardsenabled'] = 'Enable reward badge';
$string['rewardsenabled_desc'] = 'When enabled, this activity can issue a reward after the configured criteria are satisfied.';
$string['rewardbadgeid'] = 'Badge from bank';
$string['rewardbadgeid_desc'] = 'Choose an existing badge from the bank or leave it empty to define a custom badge for this activity.';
$string['rewardname'] = 'Badge name';
$string['rewarddescription'] = 'Badge description';
$string['rewardimage'] = 'Custom badge image';
$string['rewardpublicenabled'] = 'Allow public page';
$string['rewardcriteriaheader'] = 'Grant criteria';
$string['rewardcriteriahelp'] = 'How the criteria work';
$string['rewardcriterioncompletion'] = 'Activity completion';
$string['rewardcriterioncompletion_desc'] = 'Always required. The medal is only evaluated after the activity is completed.';
$string['rewardcriterionmingrade'] = 'Minimum grade';
$string['rewardcriterionmingradevalue'] = 'Minimum grade value';
$string['rewardcriterionmingrade_desc'] = 'Uses the activity grade item. Inform the minimum final grade required for the medal.';
$string['rewardcriterionsubmission'] = 'Submission completed';
$string['rewardcriterionsubmission_desc'] = 'Available for assignment activities. Requires a valid submitted attempt.';
$string['rewardcriterionattempt'] = 'Attempt completed';
$string['rewardcriterionattempt_desc'] = 'Available for quiz activities. Requires at least one finished attempt.';
$string['rewardcriterionquizpass'] = 'Quiz passed';
$string['rewardcriterionquizpass_desc'] = 'Available for quiz activities. Uses the quiz passing grade configured in the grade item.';
$string['rewardcriterionresourceview'] = 'Resource fully viewed';
$string['rewardcriterionresourceview_desc'] = 'Available for resource, page, url, folder and book. Uses view tracking when available and falls back to completion data.';
$string['rewardcriterionwithindue'] = 'Completed within due date';
$string['rewardcriterionwithindue_desc'] = 'Available when the activity type has a due or close date supported by the plugin.';
$string['rewardmissingmingrade'] = 'Inform the minimum grade value.';
$string['rewardbankempty'] = 'No badge available in the bank yet.';
$string['rewardbanktitle'] = 'Badge bank';
$string['rewardbanksubtitle'] = 'Create reusable visual badges and connect them to activities.';
$string['rewardcreatebadge'] = 'Create badge';
$string['rewardeditbadge'] = 'Edit badge';
$string['rewarddeletebadge'] = 'Delete badge';
$string['rewarddeletebadgeconfirm'] = 'Are you sure you want to delete the badge "{$a}"?';
$string['rewardbadgeimagehelp'] = 'Upload a square image with transparent background for the best result.';
$string['rewardsave'] = 'Save';
$string['rewardbacktobank'] = 'Back to badge bank';
$string['rewardmybadges'] = 'My badges';
$string['rewardstudentbadges'] = 'Student badges';
$string['rewardstudentbadgespage'] = 'Badges of {$a}';
$string['rewardcoursebadges'] = 'Course badges';
$string['rewardallbadges'] = 'All my badges';
$string['rewardnobadges'] = 'No badges earned yet.';
$string['rewardnocoursebadges'] = 'No badges have been earned in this course yet.';
$string['rewardearnedon'] = 'Earned on';
$string['rewardcourse'] = 'Course';
$string['rewardactivity'] = 'Activity';
$string['rewardbadge'] = 'Badge';
$string['rewardstudent'] = 'Student';
$string['rewardsharelinkedin'] = 'Share on LinkedIn';
$string['rewardpubliclink'] = 'Public link';
$string['rewardcopylink'] = 'Copy link';
$string['rewardviewbadge'] = 'View badge';
$string['rewardviewallbadges'] = 'View all badges';
$string['rewardpopupheading'] = 'You earned a badge!';
$string['rewardpopupcta'] = 'This achievement is ready to be viewed and shared.';
$string['rewardpublicdisabled'] = 'Public sharing disabled for this badge.';
$string['rewardnotfound'] = 'Reward not found.';
$string['rewardbankname'] = 'Name';
$string['rewardbankdescription'] = 'Description';
$string['rewardbankimage'] = 'Image';
$string['rewardbankactions'] = 'Actions';
$string['rewardlinkedincopy'] = 'I just earned the badge "{$a->name}" in {$a->course}.';
$string['rewardissueimagealt'] = 'Personalized reward image';
$string['rewardmanage'] = 'Manage badge bank';
$string['rewardcompletiontask'] = 'Repair missing rewards';
$string['rewardsettingsnav'] = 'Rewards';
$string['rewardintro'] = 'Digital rewards connected to activities.';
$string['rewardactivitysettingsnote'] = 'If a badge bank item is selected, custom fields override bank values only when filled. Criteria that do not apply to the selected activity type will not issue the medal until the activity can satisfy them.';
$string['rewardpluginname'] = 'Activity rewards';
$string['rewardimagepreview'] = 'Image preview';
$string['rewardpublicpage'] = 'Public reward page';
$string['rewardsharetext'] = 'Share text';
$string['rewarddelete'] = 'Delete';
$string['rewardclose'] = 'Close';
$string['rewardviewpublic'] = 'View public page';
$string['rewardmissingname'] = 'Provide a badge name or select one from the badge bank.';
$string['rewardrequiredimage'] = 'Upload a custom image or select a badge from the bank.';
$string['rewardgridcustom'] = 'Use a custom badge for this activity';
$string['rewardgridcustom_desc'] = 'Do not use a badge from the bank. You can upload a custom image below.';

$string['rewardsenabled_desc_help'] = '';
$string['rewardbadgeimagehelp_help'] = '';