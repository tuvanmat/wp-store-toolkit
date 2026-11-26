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

    // --- "Read more" / "Show less" toggle (only runs if present on the page) ---
    const readMoreWraps = document.querySelectorAll('.read-more-wrap');

    if (readMoreWraps.length > 0) {
        readMoreWraps.forEach(wrap => {
            const initialHeight = wrap.getAttribute('data-initial-height');
            const toggleButton = wrap.nextElementSibling;
            const expandLabel = toggleButton ? (toggleButton.getAttribute('data-expand-label') || 'Read more ▼') : 'Read more ▼';
            const collapseLabel = toggleButton ? (toggleButton.getAttribute('data-collapse-label') || 'Show less ▲') : 'Show less ▲';

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
                        wrap.style.maxHeight = wrap.scrollHeight + 'px';
                        this.textContent = collapseLabel;
                        wrap.classList.remove('collapsed');
                    } else {
                        wrap.style.maxHeight = `${initialHeight}px`;
                        this.textContent = expandLabel;
                        wrap.classList.add('collapsed');

                        const stickyHeader = document.querySelector(STICKY_HEADER_SELECTOR);
                        const headerHeight = stickyHeader ? stickyHeader.offsetHeight : 0;
                        const wrapPosition = wrap.getBoundingClientRect().top + window.pageYOffset - headerHeight - SCROLL_PADDING;

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

        try {
            const targetElement = document.querySelector(hash);
            if (!targetElement) return;

            const parentWrap = targetElement.closest('.read-more-wrap.collapsed');

            if (parentWrap) {
                const toggleButton = parentWrap.nextElementSibling;
                if (toggleButton) {
                    parentWrap.style.maxHeight = parentWrap.scrollHeight + 'px';
                    toggleButton.textContent = toggleButton.getAttribute('data-collapse-label') || 'Show less ▲';
                    parentWrap.classList.remove('collapsed');
                }
            }

            setTimeout(() => {
                const stickyHeader = document.querySelector(STICKY_HEADER_SELECTOR);
                const headerHeight = stickyHeader ? stickyHeader.offsetHeight : 0;

                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerHeight - SCROLL_PADDING;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: "smooth"
                });
            }, 100);

        } catch (e) {
            console.warn('Could not select or scroll to element for hash:', hash);
        }
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
