<?php
// src/templates/PageFooter.php

namespace App\Templates;

class PageFooter
{
	private ?float $timeStart;

	/**
	 * @param float|null $timeStart Microtime captured at the start of the request
	 *                               (e.g. from PageHeader::getLoadStartTime()).
	 *                               Pass null to skip the load-time script.
	 */
	public function __construct(?float $timeStart = null)
	{
		$this->timeStart = $timeStart;
	}

	/**
	 * Builds the inline <script> that reports page load time via the
	 * tool_title tooltip attribute. Returns '' when no start time is set.
	 */
	private function loadTimeScript(): string
	{
		if ($this->timeStart === null) {
			return '';
		}

		$timeEnd  = microtime(true);
		$timeDiff = round($timeEnd - $this->timeStart, 3);

		$line = "Load Time: {$timeDiff} seconds";

		// Escape for JavaScript
		$escapedLine = addslashes($line);
		$script = "$('.tool_title').attr('title', '{$escapedLine}');";

		return "\n<script>\n\t{$script}</script>";
	}

	/**
	 * Renders the closing markup: load-time script, closing tags,
	 * shared JS includes, and page-wide JS initialization.
	 */
	public function render(): void
	{
		echo $this->loadTimeScript();

		echo <<<HTML

        </div>
        </main>

        <script src='/tdc/js/autocomplate.js'></script>
        <script src='/tdc/js/footer.js'></script>

        <!-- Common JavaScript -->
        <script src="/Translation_Dashboard/js/c.js"></script>
        </body>

        </html>
        HTML;
	}
}

// Usage (replaces the old procedural src/templates/footer.php):
// $pageFooter = new PageFooter($pageHeader->getLoadStartTime());
// $pageFooter->render();
