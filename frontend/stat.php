<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wiki Project Med Translation Dashboard - Stats</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs5/2.2.2/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="css/Responsive_Table.css">
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
            <div class="card">
                <div class='card-header'>
                    <form id="filter-form">
                        <div class="row">
                            <div class="col-md-4">
                                <h4>Status:</h4>
                            </div>
                            <div class="col-md-4">
                                <select id="category-select" name="cat" class="form-select">
                                    <!-- Options will be inserted here by JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class='card-body'>
                    <div id="summary-table-container"></div>
                    <hr>
                    <div id="stats-table-container">
                        <table class="table table-sm table-striped" id="stats-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>QID</th>
                                    <th>Lead Word</th>
                                    <th>All Word</th>
                                    <th>Ref</th>
                                    <th>All Ref</th>
                                    <th>Importance</th>
                                    <th>ENWiki Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be inserted here by JavaScript -->
                            </tbody>
                        </table>
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

    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const cat = urlParams.get('cat') || 'RTT';

            function loadStats(category) {
                fetch(`/backend/router.php?action=stat&cat=${category}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Populate category dropdown
                            const categorySelect = $('#category-select');
                            if (categorySelect.children().length === 0) {
                                data.data.categories.forEach(catName => {
                                    const selected = catName === category ? 'selected' : '';
                                    categorySelect.append(`<option value="${catName}" ${selected}>${catName}</option>`);
                                });
                            }

                            // Populate summary table
                            const summaryTableContainer = $('#summary-table-container');
                            let summaryTable = '<table class="table table-bordered"><thead><tr><th>Key</th>';
                            Object.keys(data.data.summary).forEach(key => {
                                summaryTable += `<th>${key}</th>`;
                            });
                            summaryTable += '</tr></thead><tbody><tr><th>With</th>';
                            Object.values(data.data.summary).forEach(val => {
                                summaryTable += `<td>${val.with}</td>`;
                            });
                            summaryTable += '</tr><tr><th>Without</th>';
                             Object.values(data.data.summary).forEach(val => {
                                summaryTable += `<td>${val.without}</td>`;
                            });
                            summaryTable += '</tr></tbody></table>';
                            summaryTableContainer.html(summaryTable);


                            // Populate stats table
                            const tableBody = $('#stats-table tbody');
                            tableBody.empty();
                            let counter = 1;
                            data.data.table_data.forEach(item => {
                                const row = `
                                    <tr>
                                        <td>${counter++}</td>
                                        <td><a href="https://mdwiki.org/wiki/${item.title}">${item.title}</a></td>
                                        <td><a href="https://wikidata.org/wiki/${item.qid}">${item.qid}</a></td>
                                        <td>${item.word}</td>
                                        <td>${item.allword}</td>
                                        <td>${item.ref}</td>
                                        <td>${item.all_ref}</td>
                                        <td>${item.importance}</td>
                                        <td>${item.enwiki_views}</td>
                                    </tr>
                                `;
                                tableBody.append(row);
                            });

                            if ($.fn.DataTable.isDataTable('#stats-table')) {
                                $('#stats-table').DataTable().destroy();
                            }
                            $('#stats-table').DataTable();

                        } else {
                            console.error('Failed to fetch stats:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                const selectedCategory = $('#category-select').val();
                window.history.pushState({}, '', `?cat=${selectedCategory}`);
                loadStats(selectedCategory);
            });

            loadStats(cat);
        });
    </script>
</body>
</html>
