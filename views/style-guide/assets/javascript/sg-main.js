/**
 * Tabs
 */

window.addEventListener("load", function() {

    // store tabs variable
    var tabGroups = document.querySelectorAll(".sg-tabs");

    function setupTabs(group) {
        var tabEls = group.querySelectorAll('ul.sg-tabs-list li');

        for(var i = 0; i < tabEls.length; i++) {
            tabEls[i].addEventListener("click", function(e) {
                for (var i = 0; i < tabEls.length; i++) {
                    tabEls[i].classList.remove("sg-active");
                }

                var clickedTab = e.currentTarget;

                clickedTab.classList.add("sg-active");

                e.preventDefault();

                var myContentPanes = group.querySelectorAll(".sg-tabs-panel");

                for (i = 0; i < myContentPanes.length; i++) {
                    myContentPanes[i].classList.remove("sg-active");
                }

                var anchorReference = e.target;
                var activePaneId = anchorReference.getAttribute("href");
                var activePane = document.querySelector(activePaneId);

                activePane.classList.add("sg-active");
            })
        }
    }

    for (var i = 0; i < tabGroups.length; i++) {
        setupTabs(tabGroups[i]);
    }

});