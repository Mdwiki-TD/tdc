<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wiki Project Med Translation Dashboard</title>

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
                    <h4>Recent translations</h4>
                </div>
                <div class='card-body'>
                    <table class="table table-sm table-striped" id="last-edits-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Title</th>
                                <th>Translated</th>
                                <th>Publication date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be inserted here by JavaScript -->
                        </tbody>
                    </table>
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
            fetch('/backend/router.php?action=last')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tableBody = $('#last-edits-table tbody');
                        let counter = 1;
                        data.data.forEach(item => {
                            const row = `
                                <tr>
                                    <td>${counter++}</td>
                                    <td>${item.user}</td>
                                    <td>${item.title}</td>
                                    <td>${item.lang}: ${item.target}</td>
                                    <td>${item.pupdate}</td>
                                </tr>
                            `;
                            tableBody.append(row);
                        });
                        $('#last-edits-table').DataTable();
                    } else {
                        console.error('Failed to fetch last edits:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        });
    </script>
</body>
</html>
