/**
 * Read More front-end behaviour, with sticky table-of-contents support.
 *
 * Collapses product descriptions behind a toggle, and makes sure
 * table-of-contents (EZ TOC) anchor links still reach headings that
 * sit inside a collapsed description: the description is expanded
 * first, then the page scrolls to the heading.
 */
document.addEventListener('DOMContentLoaded', function() {

    // --- Settings ---
    const STICKY_HEADER_SELECTOR = '#header.is-sticky';
    const SCROLL_PADDING = 20;

    // --- Expand / collapse helpers ---
    // The two state classes are always kept in sync: a wrap is either
    // .collapsed or .active, never both and never neither once set up.
    function expandWrap(wrap) {
        const toggleButton = wrap.nextElementSibling;

        wrap.classList.remove('collapsed');
        wrap.classList.add('active');

        if (toggleButton && toggleButton.classList.contains('read-more-toggle')) {
            toggleButton.textContent = toggleButton.getAttribute('data-collapse-label') || 'Show less ▲';
        }

        // Animate to the full height first, then drop the fixed
        // max-height so the block can keep growing afterwards
        // (late-loading images, web fonts, window resizes).
        wrap.style.maxHeight = wrap.scrollHeight + 'px';
        wrap.addEventListener('transitionend', function onTransitionEnd(event) {
            if (event.propertyName !== 'max-height') {
                return;
            }
            wrap.removeEventListener('transitionend', onTransitionEnd);
            if (wrap.classList.contains('active')) {
                wrap.style.maxHeight = 'none';
            }
        });
    }

    function collapseWrap(wrap, initialHeight) {
        const toggleButton = wrap.nextElementSibling;

        wrap.classList.add('collapsed');
        wrap.classList.remove('active');

        if (toggleButton && toggleButton.classList.contains('read-more-toggle')) {
            toggleButton.textContent = toggleButton.getAttribute('data-expand-label') || 'Read more ▼';
        }

        // If the fixed max-height was dropped while expanded, restore
        // the current height first so the collapse transition can run.
        if (wrap.style.maxHeight === 'none' || wrap.style.maxHeight === '') {
            wrap.style.maxHeight = wrap.scrollHeight + 'px';
            void wrap.offsetHeight; // Force reflow before transitioning down.
        }
        wrap.style.maxHeight = initialHeight + 'px';
    }

    // --- "Read more" / "Show less" toggle (only runs if present on the page) ---
    const readMoreWraps = document.querySelectorAll('.read-more-wrap');

    if (readMoreWraps.length > 0) {
        readMoreWraps.forEach(wrap => {
            const initialHeight = wrap.getAttribute('data-initial-height');
            const toggleButton = wrap.nextElementSibling;

            if (initialHeight && wrap.scrollHeight > parseInt(initialHeight, 10)) {
                wrap.style.maxHeight = `${initialHeight}px`;
                wrap.classList.add('collapsed');
                if (toggleButton) {
                    toggleButton.style.display = 'block';
                }
            } else {
                if (toggleButton) {
                    toggleButton.style.display = 'none';
                }
            }

            if (toggleButton && toggleButton.classList.contains('read-more-toggle')) {
                toggleButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (wrap.classList.contains('collapsed')) {
                        expandWrap(wrap);
                    } else {
                        collapseWrap(wrap, initialHeight);

                        const stickyHeader = document.querySelector(STICKY_HEADER_SELECTOR);
                        const headerHeight = stickyHeader ? stickyHeader.offsetHeight : 0;
                        const wrapPosition = wrap.getBoundingClientRect().top + window.scrollY - headerHeight - SCROLL_PADDING;

                        window.scrollTo({
                            top: wrapPosition,
                            behavior: "smooth"
                        });
                    }
                });
            }
        });
    }

    // --- Scroll to a table-of-contents target, expanding its wrapper first ---
    function expandAndScroll(hash) {
        if (!hash) return;

        // Anchor ids may be percent-encoded or contain characters that
        // are not valid in a CSS selector, so look the element up by id
        // instead of passing the raw hash to querySelector().
        const targetId = decodeURIComponent(hash.substring(1));
        const targetElement = document.getElementById(targetId);
        if (!targetElement) return;

        const parentWrap = targetElement.closest('.read-more-wrap.collapsed');

        if (parentWrap) {
            expandWrap(parentWrap);
        }

        setTimeout(() => {
            const stickyHeader = document.querySelector(STICKY_HEADER_SELECTOR);
            const headerHeight = stickyHeader ? stickyHeader.offsetHeight : 0;

            const elementPosition = targetElement.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.scrollY - headerHeight - SCROLL_PADDING;

            window.scrollTo({
                top: offsetPosition,
                behavior: "smooth"
            });
        }, 100);
    }

    // --- Catch table-of-contents clicks across the whole site (pages, posts, products) ---
    document.addEventListener('click', function(event) {
        const link = event.target.closest('#ez-toc-container a[href^="#"], #ez-toc-sticky-container a[href^="#"], .ez-toc-sidebar a[href^="#"], .ez-toc-list a[href^="#"]');

        if (link) {
            const hash = link.getAttribute('href');
            if (hash && hash.startsWith('#')) {
                event.preventDefault();

                expandAndScroll(hash);

                // Close the sticky TOC bar right away on every page.
                const stickyBar = document.querySelector('.ez-toc-sticky-fixed');
                if (stickyBar) {
                    stickyBar.classList.remove('show');
                    stickyBar.classList.add('hide');
                }

                const closeIcon = document.querySelector('.ez-toc-close-icon');
                if (closeIcon) {
                    closeIcon.click();
                }

                if (history.pushState) {
                    history.pushState(null, null, hash);
                } else {
                    location.hash = hash;
                }
            }
        }
    });

    if (window.location.hash) {
        expandAndScroll(window.location.hash);
    }

    // --- Auto-expand when the "open table of contents" button is pressed ---
    const openTocButton = document.querySelector('.ez-toc-open-icon');
    const longDescriptionWrap = document.querySelector('.read-more-wrap.read-more-long-description');

    if (openTocButton && longDescriptionWrap) {
        openTocButton.addEventListener('click', function() {
            if (longDescriptionWrap.classList.contains('collapsed')) {
                const toggleButton = longDescriptionWrap.nextElementSibling;
                if (toggleButton) {
                    toggleButton.click();
                }
            }
        });
    }
});
