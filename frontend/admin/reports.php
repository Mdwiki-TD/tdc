<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wiki Project Med Translation Dashboard - Reports</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs5/2.2.2/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="../css/Responsive_Table.css">
</head>
<body>
    <header class="mb-3 border-bottom">
        <nav class="navbar navbar-expand-lg bg-body-tertiary shadow" id="mainnav">
            <div class="container-fluid" id="navbardiv">
                <a class="navbar-brand mb-0 h1" href="/#" style="color:#0d6efd;">
                    <span>WikiProjectMed Translation Dashboard</span>
                </a>
            </div>
        </nav>
    </header>

    <main id="body">
        <div id="maindiv" class="container-fluid">
            <div class='card'>
                <div class='card-header'>
                    <h4 class="card-title mb-4">Publish Reports Viewer (<span id="count_result">0</span>):</h4>
                    <form id="filterForm" class="row g-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex justify-content-betweenx justify-content-center">

                                    <select class="form-select" name="year" id="year"></select>
                                    <select class="form-select" name="month" id="month"></select>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <label class="input-group-text" for="lang">Lang.</label>
                                            <select class="form-select1 selectpicker w-50" name="lang" id="lang" data-live-search="true" data-style='btn active' data-bs-theme="auto"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <label class="input-group-text" for="user">User</label>
                                            <select class="form-select1 selectpicker w-50" name="user" id="user" data-live-search="true" data-style='btn active' data-bs-theme="auto"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <label class="input-group-text" for="result">Result</label>
                                            <select class="form-select1 selectpicker w-50" name="result" id="result" data-live-search="true" data-style='btn active' data-bs-theme="auto"></select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex justify-content-between justify-content-center">
                                    <button type="submit" class="btn btn-outline-primary">
                                        Search
                                    </button>
                                    <div><i id="loadingIndicator" class="fa fa-spinner fa-spin" style="display:none;"></i></div>
                                    <button type="button" class="btn btn-outline-secondary" id="resetBtn">Reset</button>
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
                        <table class="table table-sm table-striped table-mobile-responsive table-mobile-sided table_text_left" id="resultsTable" style="width:100%">
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
                </div>
            </div>
        </div>
    </main>

    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net/2.2.2/dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs5/2.2.2/dataTables.bootstrap5.min.js"></script>
    <script src="../js/reports-script.js"></script>
    <script>
        $(document).ready(async function() {
            // Load filters once only
            await load_form();

            let table = await newDataTable();

            $('#count_result').text(allResults.length);

            // حدث إرسال الفورم
            setupEventHandlers(table);

        });
    </script>
</body>
</html>
