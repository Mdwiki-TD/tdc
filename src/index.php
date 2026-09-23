<?php

include_once __DIR__ . '/app/include_all.php';
include_once __DIR__ . '/templates/header.php';

echo <<<HTML
	<!-- </div> -->
	<script>$("#coord").addClass("active");</script>
	<!-- <div id="maindiv" class="container-fluid"> -->
HTML;

include_once __DIR__ . '/app/index.php';

echo <<<HTML
			</div>
		</div>
	</div>
</div>
HTML;

echo "<script src='/tdc/js/autocomplate.js'></script>";

include_once __DIR__ . '/templates/footer.php';
