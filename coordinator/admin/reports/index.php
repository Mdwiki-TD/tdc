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
    <form id="filterForm" class="row g-3">
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <label class="input-group-text" for="year">Year</label>
                            <select class="form-select" name="year" id="year"></select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <label class="input-group-text" for="month">Month</label>
                            <select class="form-select" name="month" id="month"></select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <label class="input-group-text" for="lang">Language</label>
                            <select class="form-select1 selectpicker w-50" name="lang" id="lang" data-live-search="true" data-style='btn active' data-bs-theme="auto"></select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <label class="input-group-text" for="user">User</label>
                    <select class="form-select1 selectpicker w-75" name="user" id="user" data-live-search="true" data-style='btn active' data-bs-theme="auto"></select>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-4">
                <div class="input-group">
                    <label class="input-group-text" for="result">Result</label>
                    <select class="form-select1 selectpicker" name="result" id="result" data-live-search="true" data-style='btn active' data-bs-theme="auto"></select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="w-50 mx-auto">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        Search <i id="loadingIndicator" class="fa fa-spinner fa-spin" style="display:none;"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="w-50 mx-auto">
                    <button type="button" class="btn btn-outline-secondary w-100" id="resetBtn">Reset</button>
                </div>
            </div>
        </div>
    </form>
</div>
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
        $(document).ready(function() {
            load_results();
        });
    </script>
