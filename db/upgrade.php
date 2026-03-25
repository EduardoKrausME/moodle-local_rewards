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
 * upgrade.php
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps for local_rewards.
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Executes local_rewards upgrades.
 *
 * @param int $oldversion The currently installed version.
 * @return bool
 */
function xmldb_local_rewards_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026032501) {
        $table = new xmldb_table("local_rewards_configs");

        $fields = [
            new xmldb_field("requirecompletion", XMLDB_TYPE_INTEGER, "1", null, XMLDB_NOTNULL, null, "1", "publicenabled"),
            new xmldb_field("requiremingrade", XMLDB_TYPE_INTEGER, "1", null, XMLDB_NOTNULL, null, "0", "requirecompletion"),
            new xmldb_field("mingrade", XMLDB_TYPE_NUMBER, "10, 2", null, null, null, null, "requiremingrade"),
            new xmldb_field("requiresubmission", XMLDB_TYPE_INTEGER, "1", null, XMLDB_NOTNULL, null, "0", "mingrade"),
            new xmldb_field("requireattemptcompleted", XMLDB_TYPE_INTEGER, "1", null, XMLDB_NOTNULL, null, "0", "requiresubmission"),
            new xmldb_field("requirequizpass", XMLDB_TYPE_INTEGER, "1", null, XMLDB_NOTNULL, null, "0", "requireattemptcompleted"),
            new xmldb_field("requireresourceview", XMLDB_TYPE_INTEGER, "1", null, XMLDB_NOTNULL, null, "0", "requirequizpass"),
            new xmldb_field("requirewithinduedate", XMLDB_TYPE_INTEGER, "1", null, XMLDB_NOTNULL, null, "0", "requireresourceview"),
        ];

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_plugin_savepoint(true, 2026032501, "local", "rewards");
    }

    return true;
}
