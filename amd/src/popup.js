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
 * popup.js
 *
 * @package   local_rewards
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function($) {
    /**
     * Opens and controls the reward popup.
     *
     * @return void
     */
    function init() {
        var popup = $("[data-local-rewards-popup='true']");
        if (!popup.length) {
            return;
        }

        window.setTimeout(function() {
            popup.addClass("is-visible");
        }, 150);

        popup.on("click", "[data-local-rewards-close='true']", function() {
            popup.removeClass("is-visible");
        });

        popup.on("click", function(e) {
            if ($(e.target).is("[data-local-rewards-popup='true']")) {
                popup.removeClass("is-visible");
            }
        });

        $(document).on("keyup.localRewardsPopup", function(e) {
            if (e.key == "Escape") {
                popup.removeClass("is-visible");
            }
        });
    }

    return {
        init: init
    };
});
