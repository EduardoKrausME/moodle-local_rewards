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
 * local_rewards.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
$string['privacy:metadata:local_rewards_issues:cmid'] = 'The course module that triggered the reward.';
$string['privacy:metadata:local_rewards_issues:courseid'] = 'The course that owns the activity.';
$string['privacy:metadata:local_rewards_issues:description'] = 'The issue description snapshot.';
$string['privacy:metadata:local_rewards_issues:name'] = 'The issue title snapshot.';
$string['privacy:metadata:local_rewards_issues:popupshown'] = 'Whether the reward popup was already shown.';
$string['privacy:metadata:local_rewards_issues:publictoken'] = 'The public token used in the shared page.';
$string['privacy:metadata:local_rewards_issues:timeissued'] = 'The time when the reward was issued.';
$string['privacy:metadata:local_rewards_issues:userid'] = 'The user who earned the reward.';
$string['privacy:path:issues'] = 'Issued rewards';
$string['rewardactivity'] = 'Activity';
$string['rewardactivitysettingsnote'] = 'If a badge bank item is selected, custom fields override bank values only when filled. Criteria that do not apply to the selected activity type will not issue the medal until the activity can satisfy them.';
$string['rewardallbadges'] = 'All my badges';
$string['rewardbacktobank'] = 'Back to badge bank';
$string['rewardbadge'] = 'Badge';
$string['rewardbadgeid'] = 'Badge from bank';
$string['rewardbadgeid_desc'] = 'Choose an existing badge from the bank or leave it empty to define a custom badge for this activity.';
$string['rewardbadgeid_desc_help'] = '';
$string['rewardbadgeimagehelp'] = 'Upload a square image with transparent background for the best result.';
$string['rewardbadgeimagehelp_help'] = '';
$string['rewardbankactions'] = 'Actions';
$string['rewardbankdescription'] = 'Description';
$string['rewardbankempty'] = 'No badge available in the bank yet.';
$string['rewardbankimage'] = 'Image';
$string['rewardbankname'] = 'Name';
$string['rewardbanksubtitle'] = 'Create reusable visual badges and connect them to activities.';
$string['rewardbanktitle'] = 'Badge bank';
$string['rewardclose'] = 'Close';
$string['rewardcompletiontask'] = 'Repair missing rewards';
$string['rewardcopylink'] = 'Copy link';
$string['rewardcourse'] = 'Course';
$string['rewardcoursebadges'] = 'Course badges';
$string['rewardcreatebadge'] = 'Create badge';
$string['rewardcriteriaheader'] = 'Grant criteria';
$string['rewardcriteriahelp'] = 'How the criteria work';
$string['rewardcriterionattempt'] = 'Attempt completed';
$string['rewardcriterionattempt_desc'] = 'Available for quiz activities. Requires at least one finished attempt.';
$string['rewardcriterioncompletion'] = 'Activity completion';
$string['rewardcriterioncompletion_desc'] = 'Always required. The medal is only evaluated after the activity is completed.';
$string['rewardcriterionmingrade'] = 'Minimum grade';
$string['rewardcriterionmingrade_desc'] = 'Uses the activity grade item. Inform the minimum final grade required for the medal.';
$string['rewardcriterionmingradevalue'] = 'Minimum grade value';
$string['rewardcriterionquizpass'] = 'Quiz passed';
$string['rewardcriterionquizpass_desc'] = 'Available for quiz activities. Uses the quiz passing grade configured in the grade item.';
$string['rewardcriterionresourceview'] = 'Resource fully viewed';
$string['rewardcriterionresourceview_desc'] = 'Available for resource, page, url, folder and book. Uses view tracking when available and falls back to completion data.';
$string['rewardcriterionsubmission'] = 'Submission completed';
$string['rewardcriterionsubmission_desc'] = 'Available for assignment activities. Requires a valid submitted attempt.';
$string['rewardcriterionwithindue'] = 'Completed within due date';
$string['rewardcriterionwithindue_desc'] = 'Available when the activity type has a due or close date supported by the plugin.';
$string['rewarddelete'] = 'Delete';
$string['rewarddeletebadge'] = 'Delete badge';
$string['rewarddeletebadgeconfirm'] = 'Are you sure you want to delete the badge ""?';
$string['rewarddescription'] = 'Badge description';
$string['rewardearnedon'] = 'Earned on';
$string['rewardeditbadge'] = 'Edit badge';
$string['rewardimage'] = 'Custom badge image';
$string['rewardimagepreview'] = 'Image preview';
$string['rewardintro'] = 'Digital rewards connected to activities.';
$string['rewardissueimagealt'] = 'Personalized reward image';
$string['rewardlinkedincopy'] = 'I just earned the badge "" in .';
$string['rewardmanage'] = 'Manage badge bank';
$string['rewardmissingmingrade'] = 'Inform the minimum grade value.';
$string['rewardmissingname'] = 'Provide a badge name or select one from the badge bank.';
$string['rewardmybadges'] = 'My badges';
$string['rewardname'] = 'Badge name';
$string['rewardnobadges'] = 'No badges earned yet.';
$string['rewardnocoursebadges'] = 'No badges have been earned in this course yet.';
$string['rewardnotfound'] = 'Reward not found.';
$string['rewardpluginname'] = 'Activity rewards';
$string['rewardpopupcta'] = 'This achievement is ready to be viewed and shared.';
$string['rewardpopupheading'] = 'You earned a badge!';
$string['rewardpublicdisabled'] = 'Public sharing disabled for this badge.';
$string['rewardpublicenabled'] = 'Allow public page';
$string['rewardpubliclink'] = 'Public link';
$string['rewardpublicpage'] = 'Public reward page';
$string['rewardrequiredimage'] = 'Upload a custom image or select a badge from the bank.';
$string['rewardsave'] = 'Save';
$string['rewardsenabled'] = 'Enable reward badge';
$string['rewardsenabled_desc'] = 'When enabled, this activity can issue a reward after the configured criteria are satisfied.';
$string['rewardsenabled_desc_help'] = '';
$string['rewardsettingsnav'] = 'Rewards';
$string['rewardsharelinkedin'] = 'Share on LinkedIn';
$string['rewardsharetext'] = 'Share text';
$string['rewardsheader'] = 'Reward badge';
$string['rewardstudent'] = 'Student';
$string['rewardstudentbadges'] = 'Student badges';
$string['rewardstudentbadgespage'] = 'Badges of ';
$string['rewardviewallbadges'] = 'View all badges';
$string['rewardviewbadge'] = 'View badge';
$string['rewardviewpublic'] = 'View public page';
