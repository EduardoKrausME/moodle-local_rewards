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
