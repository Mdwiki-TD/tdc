<?php
// src/templates/footer.php

// Calculate and display page load time
if (isset($GLOBALS['timeStart'])) {
	$timeStart = (float)$GLOBALS['timeStart'];
	$timeEnd = microtime(true);
	$timeDiff = round($timeEnd - $timeStart, 3);

	$line = "Load Time: {$timeDiff} seconds";

	// Escape for JavaScript
	$escapedLine = addslashes($line);
	$script = "$('.tool_title').attr('title', '{$escapedLine}');";

	echo "\n<script>\n\t{$script}</script>";
}
?>

</div>
</main>

<script src='/tdc/js/autocomplate.js'></script>

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
