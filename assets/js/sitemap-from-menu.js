function sfm_init_collapsible() {
    var toggleButtons = document.querySelectorAll('.sfm-toggle-btn');

    toggleButtons.forEach(function (btn) {
        if (btn.dataset.sfmBound === "true") return;
        btn.dataset.sfmBound = "true";

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var li = this.parentElement;
            var isExpanded = this.getAttribute('aria-expanded') === 'true';

            // Toggle state
            if (isExpanded) {
                this.setAttribute('aria-expanded', 'false');
                this.innerText = '+';
                li.classList.remove('sfm-expanded');
            } else {
                this.setAttribute('aria-expanded', 'true');
                this.innerText = '-';
                li.classList.add('sfm-expanded');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', sfm_init_collapsible);
