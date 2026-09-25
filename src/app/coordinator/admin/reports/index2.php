<?php
// src/app/coordinator/admin/reports/index2.php

namespace App\Coordinator\Admin\Reports;

use App\Coordinator\Admin\Common\AbstractControllerNoPost;

/**
 * Class ReportsIndex2Controller
 * Renders an alternate "Publish Reports Viewer" shell, with server-built
 * filter <select> options (currently backed by an empty dataset, kept
 * for parity with the legacy behaviour) instead of client-populated ones.
 */
class ReportsIndex2Controller extends AbstractControllerNoPost
{
    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        $this->validateCoordinator();

        $formOptions = $this->buildFormOptions();

        $this->renderFilterCard($formOptions);
        $this->renderResultsBody();
    }

    /**
     * Builds the <select> markup for each filter key based on the
     * (currently empty) dataset columns.
     */
    private function buildFormOptions(): array
    {
        $formOptions = [];

        $keys = [
            'year'   => false,
            'month'  => false,
            'lang'   => true,
            'user'   => true,
            'result' => true,
        ];

        // $data = get_publish_reports_stats();
        $data = [];

        foreach ($keys as $key => $useSelectPicker) {
            $dataNew = array_unique(array_column($data, $key));

            $formOptions[$key] = $this->makeSelect($key, $dataNew, $useSelectPicker);
        }

        return $formOptions;
    }

    /**
     * Builds a single <select> filter element.
     */
    private function makeSelect(string $id, array $data, bool $useSelectPicker): string
    {
        $options = '<option value="">All</option>';

        foreach ($data as $key => $value) {
            $options .= "<option value='" . $value . "'>" . $value . "</option>\n";
        }

        if (!$useSelectPicker) {
            return <<<HTML
                <select id="$id" name="$id" class="form-select" data-bs-theme="auto">
                    $options
                </select>
            HTML;
        }

        return <<<HTML
            <select class="form-select1 selectpicker w-50"
                name="lang"
                id="lang"
                data-bstheme-="auto"
                data-live-search="true"
                data-style='btn active'
                data-bs-theme="auto"
                data-container="body"
                data-live-search-style="begins"
                >
                $options
            </select>
        HTML;
    }

    /**
     * Renders the styles and filter form card.
     */
    private function renderFilterCard(array $formOptions): void
    {
        $yearOptions   = $formOptions["year"] ?? "";
        $monthOptions  = $formOptions["month"] ?? "";
        $langOptions   = $formOptions["lang"] ?? "";
        $userOptions   = $formOptions["user"] ?? "";
        $resultOptions = $formOptions["result"] ?? "";

        echo <<<HTML
            <style>
                pre.json-data {
                    background-color: #f8f9fa;
                    padding: 10px;
                    border-radius: 5px;
                    max-height: 300px;
                    overflow: auto;
                }
            </style>
            <div class='card-header'>
                <h4 class="card-title mb-4">Publish Reports Viewer (<span id="count_result">0</span>):</h4>
                <form id="filterForm" class="row g-3" action="#" method="get">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex justify-content-betweenx justify-content-center">
                                $yearOptions
                                $monthOptions
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <label class="input-group-text" for="lang">Lang.</label>
                                            $langOptions
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <label class="input-group-text" for="user">User</label>
                                            $userOptions
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <label class="input-group-text" for="result">Result</label>
                                            $resultOptions
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex justify-content-between justify-content-center">
                                <button type="submit" class="btn btn-outline-primary" id="searchBtn">
                                    Search
                                </button>
                                <div><i id="loadingIndicator" class="fa fa-spinner fa-spin" style="display:none;"></i></div>
                                <button type="button" class="btn btn-outline-secondary" id="resetBtn">Reset</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        HTML;
    }

    /**
     * Renders the results table body, modal, and init script.
     */
    private function renderResultsBody(): void
    {
        echo <<<HTML
            <div class='card-body p-1'>
                <div id="loading" class="text-center my-0" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-striped table-mobile-responsive table-mobile-sided" id="resultsTable" style="width:100%">
                        <thead class="">
                            <tr>
                                <th style="display:none">ID</th>
                                <th>Date</th>
                                <th>Language</th>
                                <th>Title</th>
                                <th>User</th>
                                <th>Source Title</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailsModalLabel">Data Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <pre class="json-data" id="modalData"></pre>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="/tdc/js/reports-script.js"></script>
                <script>
                    $(document).ready(async function() {
                        // Load filters once only
                        await load_form();

                        $('.selectpicker').selectpicker('refresh');

                        let table = await newDataTable();

                        $('#count_result').text(allResults.length);

                        // Handle the form submit event
                        setupEventHandlers(table);

                    });
                </script>
        HTML;
    }
}
