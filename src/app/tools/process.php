<?php
// src/app/tools/process.php

namespace App\Tools;

use App\Coordinator\Admin\Common\AbstractControllerNoPost;

/**
 * Class ProcessControllerDataTable
 * Renders in-process translations dashboard using DataTables AJAX.
 */
class ProcessControllerDataTable extends AbstractControllerNoPost
{
	/**
	 * Handles request execution.
	 */
	public function handleRequest(): void
	{
		$header = <<<HTML
			<h4>Translations in process:</h4>
		HTML;
		$body = <<<HTML
			<table id="process_table" class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" style="font-size:90%;">
				<thead>
					<tr>
						<th>#</th>
						<th>User</th>
						<th><span data-bs-toggle="tooltip" title="Language">Lang.</span></th>
						<th>Title</th>
						<th>Campaign</th>
						<th>Draft</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		HTML;

		$this->echoBs5Card($header, $body, '');

		echo <<<HTML
			<script>
				$(document).ready(function() {
					// Determine endpoint based on environment
					const host = window.location.hostname === 'localhost' ? 'http://localhost:9001' : 'https://mdwiki.toolforge.org';
					const apiUrl = `/api.php?get=in_process&limit=200&order=add_date`;

					$('#process_table').DataTable({
						ajax: {
							url: apiUrl,
							dataSrc: 'results' // Tells DataTables to look for the array inside the 'results' key
						},
						stateSave: true,
						lengthMenu: [
							[25, 50, 100, 200],
							[25, 50, 100, 200]
						],
						columns: [{
								data: null,
								render: (data, type, row, meta) => meta.row + 1
							},
							{
								data: 'user',
								render: function(data, type, row) {
									let talkUrl = `//\${row.lang}.wikipedia.org/w/index.php?title=User_talk:\${data}&action=edit&section=new`;
									return `<a href='/Translation_Dashboard/leaderboard.php?user=\${data}'>\${data}</a> (<a target="_blank" href="\${talkUrl}">talk</a>)`;
								}
							},
							{
								data: 'lang',
								render: function(data, type, row) {
									return `<a href='/Translation_Dashboard/leaderboard.php?langcode=\${data}'>(\${data}) \${row.autonym}</a>`;
								}
							},
							{
								data: 'title',
								render: function(data, type, row) {
									let encoded = encodeURIComponent(data);
									return `<a href="//mdwiki.org/wiki/\${encoded}" target="_blank">\${data}</a>`;
								}
							},
							{
								data: 'campaign'
							},
							{
								data: 'add_date',
								render: function(data, type, row) {
									// Extract YYYY-MM-DD from the timestamp
									let dateOnly = data.includes(' ') ? data.split(' ')[0] : data;
									let encodedTitle = encodeURIComponent(row.title);
									return `<a href="//mdwikicx.toolforge.org/wiki/\${row.lang}/\${encodedTitle}" target="_blank">\${dateOnly}</a>`;
								}
							}
						]
					});
				});
			</script>
		HTML;
	}
}
