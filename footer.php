<?php

if (isset($GLOBALS['time_start'])) {
	$time_start = $GLOBALS['time_start'];
	$time_end = microtime(true);
	$time_diff = $time_end - $time_start;
	$time_diff = round($time_diff, 3);
	//---
	$line = "Load Time: " . $time_diff . " seconds";
	//---
	$script = "$('.tool_title').attr('title', '$line');";
	//---
	if (isset($_REQUEST['test']) || isset($_COOKIE['test']) || $_SERVER["SERVER_NAME"] == "localhost") {
		$script .= "\n\t$('#load_time').html('$line');";
	}
	//---
	echo "\n<script>\n\t $script</script>";
}
?>

</div>
</main>
<script src="/Translation_Dashboard/js/c.js"></script>
<script>
	function pup_window_email(element) {
		var target = $(element).attr("pup-target");
		if (!target) {
			console.error("Missing pup-target attribute");
			return;
		}
		window.open(target, 'popupWindow', 'width=850,height=550,scrollbars=yes');
	}

	function pup_window_new(element) {
		var target = $(element).attr("pup-target");
		if (!target) {
			console.error("Missing pup-target attribute");
			return;
		}
		window.open(target, '', 'width=600,height=400, left=100, top=100, location=no');
	}

	$(".Dropdown_menu_toggle").on("click", function() {
		$(".div_menu").toggleClass("mactive");
		// ---
		$(".Dropdown_menu_toggle").text($(".div_menu").hasClass("mactive") ? "✖ Close list" : "☰ Open list");
	});

	$('.sortable').DataTable({
		stateSave: true,
		paging: false,
		info: false,
		searching: false
	});
	$('.sortable2').DataTable({
		stateSave: true,
		lengthMenu: [
			[25, 50, 100, 200],
			[25, 50, 100, 200]
		],
	});
	$(document).ready(function() {
		// $('[data-toggle="tooltip"]').tooltip();
		const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
		const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

		// $('.card').CardWidget('toggle')

		setTimeout(function() {
			$('.soro').DataTable({
				stateSave: true,
				lengthMenu: [
					[25, 50, 100, 200],
					[25, 50, 100, 200]
				],
			});
		}, 200);
	});
</script>
</body>

</html>
