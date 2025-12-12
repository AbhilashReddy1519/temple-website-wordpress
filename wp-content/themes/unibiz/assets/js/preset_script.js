"use strict";

document.addEventListener("DOMContentLoaded", function () {
    function playAnimation(element, frame = false) {
        if (frame) {
            element.style.visibility = 'visible';
            element.style.opacity = '1';
            element.style.transform = 'none';
        }
        if (element.getBoundingClientRect().top > 0 && element.getBoundingClientRect().top <= (window.innerHeight * 0.75)) {
            element.classList.add('unibiz-animate-init');            
        }
    }

    function prepareAnimation(doc, frame = false) {
        var elements = doc.getElementsByClassName('unibiz-animate');

        for (let element of elements) {
            if ( frame ) {
                playAnimation(element, true);
            } else {
                window.addEventListener('load', function () {
                    playAnimation(element);
                });
                window.addEventListener('scroll', function () {
                    playAnimation(element);
                });
            }
        }
    }

    prepareAnimation(document);

    setTimeout(function() {
        var iframe = document.getElementsByClassName('edit-site-visual-editor__editor-canvas');
        var innerDoc = iframe.length > 0 ? iframe[0].contentDocument || iframe[0].contentWindow.document : null;
        innerDoc ? prepareAnimation(innerDoc, true) : null;
    }, 3000);

    function submenuDynamicPosition(item, submenu) {
        if (item.classList.contains('left')) {
            item.classList.remove('left');
        }

        const screenWidth = window.innerWidth;

        const updatedRect = submenu.getBoundingClientRect();
        if (updatedRect.right > screenWidth) {
            item.classList.add('left');
        } else if (updatedRect.right < 0) {
            if (item.classList.contains('left')) {
                item.classList.remove('left');
            }
            item.classList.add('right');
        }

        submenu.querySelectorAll('.wp-block-navigation-item.has-child').forEach(itemsub => {
            const sub_submenu = itemsub.querySelector('.wp-block-navigation__submenu-container');
            if (!sub_submenu) return;
            const rect = itemsub.getBoundingClientRect();

            if (rect.right > screenWidth) {
                itemsub.classList.add('left');
            } else if (rect.right < 0) {
                if (itemsub.classList.contains('left')) {
                    itemsub.classList.remove('left');
                }
                itemsub.classList.add('right');
            }
        });
    }

    document.querySelectorAll('.wp-block-navigation .wp-block-navigation__container .wp-block-navigation-item.has-child').forEach(item => {
        const submenu = item.querySelector('.wp-block-navigation__submenu-container');
        if (!submenu) return;

        submenuDynamicPosition(item, submenu);

        item.addEventListener('mouseenter', () => {
            submenuDynamicPosition(item, submenu);
        });

        window.addEventListener('resize', () => {
            submenuDynamicPosition(item, submenu);
        });
    });
});