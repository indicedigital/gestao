(function () {
    'use strict';

    var page = document.querySelector('.tutorial-page');
    if (!page) return;

    var tocLinks = page.querySelectorAll('[data-tutorial-link]');
    var sections = page.querySelectorAll('[data-tutorial-section]');
    var progressBar = page.querySelector('[data-tutorial-progress]');
    var progressLabel = page.querySelector('[data-tutorial-progress-label]');
    var sidebar = page.querySelector('.tutorial-sidebar');
    var activeLink = null;

    if (!sections.length) return;

    function setActive(id) {
        tocLinks.forEach(function (link) {
            var isActive = link.getAttribute('data-section-id') === id;
            link.classList.toggle('active', isActive);
            if (isActive) {
                activeLink = link;
            }
        });

        if (activeLink && sidebar) {
            var toc = sidebar.querySelector('.tutorial-toc');
            if (toc && toc.scrollHeight > toc.clientHeight) {
                var linkTop = activeLink.offsetTop;
                var linkHeight = activeLink.offsetHeight;
                var tocScroll = toc.scrollTop;
                var tocHeight = toc.clientHeight;

                if (linkTop < tocScroll + 40) {
                    toc.scrollTo({ top: linkTop - 40, behavior: 'smooth' });
                } else if (linkTop + linkHeight > tocScroll + tocHeight - 20) {
                    toc.scrollTo({ top: linkTop - tocHeight + linkHeight + 20, behavior: 'smooth' });
                }
            }
        }
    }

    function updateProgress() {
        if (!progressBar) return;

        var scrollTop = window.scrollY || document.documentElement.scrollTop;
        var docHeight = document.documentElement.scrollHeight - window.innerHeight;
        var pct = docHeight > 0 ? Math.min(100, Math.round((scrollTop / docHeight) * 100)) : 0;

        progressBar.style.width = pct + '%';
        if (progressLabel) {
            progressLabel.textContent = pct + '% concluído';
        }
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        setActive(entry.target.id);
                    }
                });
            },
            { rootMargin: '-25% 0px -55% 0px', threshold: 0 }
        );

        sections.forEach(function (section) {
            observer.observe(section);
        });
    }

    tocLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            var targetId = link.getAttribute('href').slice(1);
            var target = document.getElementById(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setActive(targetId);
            }
        });
    });

    window.addEventListener('scroll', updateProgress, { passive: true });
    updateProgress();

    if (sections.length && tocLinks.length) {
        setActive(sections[0].id);
    }
})();
