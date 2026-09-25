
/**
 * Open email popup window
 *
 * @param {HTMLElement} element - Element with pup-target attribute
 * @returns {void}
 */
function pup_window_email(element) {
    var target = $(element).attr("pup-target");
    if (!target) {
        console.error("Missing pup-target attribute");
        return;
    }
    window.open(target, 'popupWindow', 'width=850,height=550,scrollbars=yes');
}

/**
 * Open generic popup window
 *
 * @param {HTMLElement} element - Element with pup-target attribute
 * @returns {void}
 */
function pup_window_new(element) {
    var target = $(element).attr("pup-target");
    if (!target) {
        console.error("Missing pup-target attribute");
        return;
    }
    window.open(target, '', 'width=600,height=500,left=100,top=100,location=no');
}


$(document).ready(function () {
    // Initialize simple sortable tables
    $('.sortable').DataTable({
        stateSave: true,
        paging: false,
        info: false,
        searching: false
    });

    // Initialize paginated sortable tables
    $('.sortable2').DataTable({
        stateSave: true,
        lengthMenu: [
            [25, 50, 100, 200],
            [25, 50, 100, 200]
        ]
    });
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(
        tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl)
    );

    // Initialize responsive tables with slight delay for DOM stability
    setTimeout(function () {
        $('.soro').DataTable({
            stateSave: true,
            lengthMenu: [
                [25, 50, 100, 200],
                [25, 50, 100, 200]
            ]
        });
    }, 200);
});
