/**
 * Tabs
 */

window.addEventListener("load", function () {

    // store tabs variable
    var tabGroups = document.querySelectorAll(".sg-tabs");

    function setupTabs(group) {
        var tabEls = group.querySelectorAll('ul.sg-tabs-list li');

        for (var i = 0; i < tabEls.length; i++) {
            tabEls[i].addEventListener("click", function (e) {
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

/**
 * Pinned Sub Nav
 */
window.addEventListener("load", function () {
    var pageNavWrapper = document.querySelectorAll('.sg-page-nav-wrapper')[0];
    var pageNav = pageNavWrapper.querySelectorAll('.sg-page-nav')[0];
    var pageNavOffset = pageNavWrapper.offsetTop;

    function pinPageNav() {
        var scrollTop = window.pageYOffset;
        if (scrollTop >= pageNavOffset) {
            pageNav.classList.add('pinned')
        } else {
            pageNav.classList.remove('pinned')
        }

        window.requestAnimationFrame(pinPageNav);
    }

    window.requestAnimationFrame(pinPageNav);
});

/**
 * Sub Nav Active States
 */
window.addEventListener("load", function () {
    var subNavItems = Array.prototype.slice.call(document.querySelectorAll('.sg-page-nav a')); // DomList to Array
    // reverse array for cascading conditional from bottom up.
    subNavItems.reverse();

    var subNavTriggerPoints = [];

    for (var i = 0; i < subNavItems.length; i++) {
        var href = subNavItems[i].hash.substring(1);

        subNavTriggerPoints.push(document.getElementById(href).offsetTop)
    }

    function highlightActive() {
        var scrollTop = window.pageYOffset;

        for (var i = 0; i < subNavTriggerPoints.length; i++) {
            if (scrollTop > subNavTriggerPoints[i] - 90) {
                subNavItems[i].parentNode.classList.add('sg-active');

                for (var d = 0; d < subNavItems.length; d++) {
                    if (d !== i) {
                        subNavItems[d].parentNode.classList.remove('sg-active');
                    }
                }
                break;
            }
        }

        window.requestAnimationFrame(highlightActive)
    }

    window.requestAnimationFrame(highlightActive);

});

/**
 * Sub Nav Smooth Scroll
 */
window.addEventListener("load", function() {
    var subNavItems = document.querySelectorAll('.sg-page-nav a, .sg-side-nav ul li ul li a');

    for (var i = 0; i < subNavItems.length; i++) {
        subNavItems[i].addEventListener('click', function(e) {
            e.preventDefault();
            var href = e.target.hash.substring(1);
            var offset = document.getElementById(href).offsetTop;
            window.scrollTo({
                top: offset - 85,
                behavior: "smooth"
            });
        });
    }
});

/**
 * Side Nav Toggle
 */
window.addEventListener("load", function() {
    document.querySelector('.sg-nav-toggle').addEventListener('click', function() {
        document.body.classList.add('sg-show-nav');
    })

    document.querySelector('.sg-nav-toggle-close').addEventListener('click', function() {
        document.body.classList.remove('sg-show-nav');
    })
});