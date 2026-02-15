<?php

declare(strict_types=1);

/**
 * Application Footer Module
 * 
 * Provides the closing HTML structure and JavaScript initialization
 * for the Translation Dashboard application. Includes:
 * - Page load time calculation and display
 * - Popup window utilities
 * - DataTable initialization
 * - Bootstrap tooltip initialization
 * 
 * @package    UI
 * @subpackage Footer
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

// Calculate and display page load time
if (isset($GLOBALS['time_start'])) {
    $time_start = (float)$GLOBALS['time_start'];
    $time_end = microtime(true);
    $time_diff = round($time_end - $time_start, 3);
    
    $line = "Load Time: {$time_diff} seconds";
    
    // Escape for JavaScript
    $escaped_line = addslashes($line);
    $script = "$('.tool_title').attr('title', '{$escaped_line}');";
    
    echo "\n<script>\n\t{$script}</script>";
}
?>

</div>
</main>

<!-- Common JavaScript -->
<script src="/Translation_Dashboard/js/c.js"></script>

<script>
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

    $(document).ready(function() {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(
            tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl)
        );

        // Initialize responsive tables with slight delay for DOM stability
        setTimeout(function() {
            $('.soro').DataTable({
                stateSave: true,
                lengthMenu: [
                    [25, 50, 100, 200],
                    [25, 50, 100, 200]
                ]
            });
        }, 200);
    });
</script>

</body>
</html>
