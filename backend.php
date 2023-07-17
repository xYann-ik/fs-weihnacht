<?php
require_once('modules/startup.php');

if ($_GET['action'] == 'logout') {
    session_unset();
}

if ($_SESSION['loggedin'] != true) {
    header('Location: index.php');
    die;
}

require_once('modules/api.php');
$p = new PostAPI;

if ($_GET['id'] && is_numeric($_GET['id'])) {
    if ($_GET['action'] == 'accept') {
        $p->subscriberAccept($_GET['id']);
    }
    elseif ($_GET['action'] == 'deny') {
        $p->subscriberDeny($_GET['id']);
    }
    header('Location: backend.php');
}

$p->deleteUnusedFiles();

?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Backend</title>
    
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.noStyle.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css"/>
    
    <link rel="stylesheet" href="assets/css/style.css"/>
    <link rel="stylesheet" href="assets/css/glightbox.min.css"/>
</head>
<body>
      <?php  
    // Show preview in lightbox
    if ($_GET && $_GET['subscriber'] && is_numeric($_GET['subscriber'])) {
        $data = $p->getSubscribers($_GET['subscriber']);
        include('includes/cardpreview.php');
        exit;
    }
    else {
    ?>
        <header>
            <a href="index.php">
                <img src="assets/img/logo.png" alt="Feldschlösschen" />
            </a>
        </header>
        <section class="container">
            <?php
            ?>
            <div id="fs-grid" class="ag-theme-alpine" style="height: 70vh"></div>

            <a class="btn" href="?action=logout">Logout</a>

            <script type="text/javascript">
            const booleanFilter = {
                filterOptions: [
                    {
                        displayKey: 'true',
                        displayName: 'Ja',
                        predicate: (_, cellValue) => +cellValue === 1,
                        numberOfInputs: 0,
                    },
                    {
                        displayKey: 'false',
                        displayName: 'Nein',
                        predicate: (_, cellValue) => +cellValue === 0,
                        numberOfInputs: 0,
                    }
                ],
                suppressAndOrCondition: true,
            };
            const gridOptions = {
                columnDefs: [
                    { field: 'name' },
                    { field: 'email' },
                    { field: 'address' },
                    { field: 'city' },
                    { field: 'plz' },
                    { field: 'date', sort: 'desc' },
                    {
                        field: 'verified',
                        headerName: 'Bestätigt',
                        
                        filter: 'agNumberColumnFilter',
                        filterParams: booleanFilter,
                        cellRenderer: function(params) {
                            return params.data.verified == 1 ? 'Ja' : 'Nein'
                        }
                    },
                    {
                        field: 'actions',
                        autoHeight: true,
                        cellClass: 'aggrid-buttons',
                        cellRenderer: function(params) {
                            if (params.data.verified != 1) {
                                let keyData = params.data.key;
                                let accept = '<a class="btn green" href="?action=accept&id=' + params.data.id + '" onclick="return confirm(\'<?=$lang['confirmAccept']?>\')">&check;</a>';
                                let deny = '<a class="btn red" href="?action=deny&id=' + params.data.id + '" onclick="return confirm(\'<?=$lang['confirmDeny']?>\')">&cross;</a>';
                                return accept + ' ' + deny;
                            }
                        }
                    }
                ],
                
                defaultColDef: {
                    resizable: true,
                    sortable: true,
                    filter: true
                },
                rowSelection: 'single',
                animateRows: true,
                sizeColumnsToFit: true,
                getRowStyle: function(params) {
                    if (params.data.verified == 1) {
                        return { background: '#acebC6' };
                    }
                },
                
                // example event handler
                onCellClicked: params => {
                    if (params.column.colId !== 'actions') {
                        const cardPreview = GLightbox({
                            loop: true,
                            elements: [
                                {
                                    'href': '?subscriber=' + params.data.id
                                },
                                {
                                    'href': 'cards/' + params.data.file + '.jpg',
                                    'type': 'image'
                                }
                                ]
                            });
                            cardPreview.open();
                        }
                    },
                    onGridReady(params) {
                        params.api.sizeColumnsToFit();
                    }
                }
                
                const eGridDiv = document.getElementById('fs-grid');
                new agGrid.Grid(eGridDiv, gridOptions);
                gridOptions.api.setRowData(<?php $p->getSubscribers(); ?>);
                </script>
    <?php
    }
    ?>
    </section>
</body>
</html>