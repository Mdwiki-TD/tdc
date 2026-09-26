<?php
// src/app/tools/process1.php

namespace App\Tools;

use App\Coordinator\Admin\Common\AbstractControllerNoPost;
use function App\APICalls\TDApi\get_td_api;

/**
 * Class Process1Controller
 * Renders server-side in-process translations table.
 */
class Process1Controller extends AbstractControllerNoPost
{
    /**
     * Handles request execution.
     */
    public function handleRequest(): void
    {
        $apiResults = get_td_api(['get' => 'in_process', 'limit' => '100', 'order' => 'add_date']);
        $data = $apiResults['results'] ?? [];

        $tbodyHtml = "";
        $noo = 0;
        foreach ($data as $tat => $tabe) {
            $noo++;
            $tbodyHtml .= $this->createProcessRow($tabe, $noo);
        }

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>Translations in process:</h4>
                </div>
                <div class='card-body'>
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
                            $tbodyHtml
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    $('#process_table').DataTable({
                        stateSave: true,
                        lengthMenu: [
                            [25, 50, 100, 200],
                            [25, 50, 100, 200]
                        ]
                    });
                });
            </script>
        HTML;
    }

    private function createProcessRow(array $tab, int $nnnn): string
    {
        // { "id": 3284, "title": "Triquetral fracture", "user": "SeaTub", "lang": "es", "cat": "RTT", "translate_type": "all", "word": 198, "add_date": "2026-02-17 03:00:00", "campaign": "Main", "autonym": "español" }

        $user      = $tab['user'] ?? "";
        $langCode  = $tab['lang'] ?? "";
        $mdTitle   = $tab['title'] ?? "";
        $autonym   = $tab['autonym'] ?? "";
        $campaign  = $tab['campaign'] ?? "";

        $date      = $tab['add_date'] ?? "";
        // if $date has : then split before first space `2026-02-25 03:00:00` > `2026-02-25`
        if (strpos($date, ':') !== false) {
            $date = explode(' ', $date)[0];
        }
        $langTitle = "($langCode) $autonym";

        $talkUrl = "//$langCode.wikipedia.org/w/index.php?title=User_talk:$user&action=edit&section=new";

        $mdTitleEncoded = rawurlencode($mdTitle);

        return <<<HTML
            <tr>
                <td data-content="#">
                    $nnnn
                </td>
                <td data-content="User">
                    <a target='' href='/Translation_Dashboard/leaderboard.php?user=$user'>$user</a> (<a target="_blank" href="$talkUrl">talk</a>)
                </td>
                <td data-content="Lang.">
                    <a target='' href='/Translation_Dashboard/leaderboard.php?langcode=$langCode'>$langTitle</a>
                </td>
                <td data-content="Title">
                    <a href="//mdwiki.org/wiki/$mdTitleEncoded" target="_blank">$mdTitle</a>
                </td>
                <td data-content="Campaign">
                    $campaign
                </td>
                <td data-content="Draft">
                    <a href="//mdwikicx.toolforge.org/wiki/$langCode/$mdTitleEncoded" target="_blank">$date</a>
                </td>
            </tr>
        HTML;
    }
}

// $controller = new Process1Controller();
// $controller->handleRequest();
