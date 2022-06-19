@extends('layouts.app')

@section('module_css')
    <link rel="stylesheet" href="{{ mix('css/dashboard.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-5">
            <div class="row">
                <div class="col-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Top Occupancy</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="topOccupancyChart" width="136" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Bottom Occupancy</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="bottomOccupancyChart" width="136" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Waitlist</h3>
                </div>
                <div class="card-body">
                    <canvas id="waitlistChart" width="104" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Waitlist per Campaign</h3>
                </div>
                <div class="card-body">
                    <canvas id="waitlistPerCampaignChart" width="300" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3">
            <div class="row">
                <div class="col">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Total Staff</h3>
                        </div>
                        <div class="card-body">
                            <h1 class="d-flex justify-content-center"><span id="totalStaff">0</span></h1>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Total Call Terhubung</h3>
                        </div>
                        <div class="card-body">
                            <h1 class="d-flex justify-content-center"><span id="totalCallTerhubung">0</span></h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Total Agent AUX</h3>
                        </div>
                        <div class="card-body">
                            <h1 class="d-flex justify-content-center"><span id="totalAgentAux">0</span></h1>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Total Agent Hold</h3>
                        </div>
                        <div class="card-body">
                            <h1 class="d-flex justify-content-center"><span id="totalAgentHold">0</span></h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Top Agent Status</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="topAgentStatusTable" class="table-striped no-margin" style="width:100%">
                            <thead>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <div class="row">
                <div class="col">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Total RNA</h3>
                        </div>
                        <div class="card-body">
                            <h1 class="d-flex justify-content-center"><span id="totalRna">0</span></h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">AVG Handling Time</h3>
                        </div>
                        <div class="card-body">
                            <h1 class="d-flex justify-content-center"><span id="AvgHandlingTime">0</span></h1>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Occupancy</h3>
                        </div>
                        <div class="card-body">
                            <h1 class="d-flex justify-content-center"><span id="occupancy">0</span></h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('module_js')
    <script src="{{ mix('js/dashboard.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        var topAgentStatusTable;
        const waitlistPerCampaignChart = new Chart(document.getElementById('waitlistPerCampaignChart'), {
            type: 'horizontalBar',
            data: {
                labels: [],
                datasets: [{
                    label: '# of Votes',
                    data: [],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
        const topOccupancyChart = new Chart(document.getElementById('topOccupancyChart'), {
            type: 'horizontalBar',
            data: {
                labels: [],
                datasets: [{
                    label: '# of Votes',
                    data: [],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
        const bottomOccupancyChart = new Chart(document.getElementById('bottomOccupancyChart'), {
            type: 'horizontalBar',
            data: {
                labels: [],
                datasets: [{
                    label: '# of Votes',
                    data: [],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
        const waitlistChart = new Chart(document.getElementById('waitlistChart'), {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
        function updateCharts() {
            waitlistPerCampaignChart.data.datasets[0].data = [12, 19, 3, 5, 2, 3];
            waitlistPerCampaignChart.data.labels = ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'];
            waitlistPerCampaignChart.update();
            topOccupancyChart.data.datasets[0].data = [12, 19, 3, 5, 2, 3];
            topOccupancyChart.data.labels = ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'];
            topOccupancyChart.update();
            bottomOccupancyChart.data.datasets[0].data = [12, 19, 3, 5, 2, 3];
            bottomOccupancyChart.data.labels = ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'];
            bottomOccupancyChart.update();
            waitlistChart.data.datasets[0].data = [12, 19, 3, 5, 2, 3];
            waitlistChart.data.labels = ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'];
            waitlistChart.update();
        }
        function updateTable(id) {
            $.ajax({
                url: "/dynamicticket/getreport",
                data: {
                    category_id: id,
                    mode: 'header'
                },
                type: "POST",
                dataType: 'json',
                success: function (result) {
                    if ($.fn.DataTable.isDataTable('#topAgentStatusTable') ) {
                        topAgentStatusTable.destroy();
                        $('#topAgentStatusTable').empty();
                    }
                    topAgentStatusTable = $('#topAgentStatusTable').DataTable({
                        "fixedHeader": {
                            header: true,
                            footer: true
                        },
                        "scrollX": true,
                        "lengthMenu": [
                            [10, 25, 50, 100, -1],
                            [10, 25, 50, 100, "All"]
                        ],
                        "stateSave": true,
                        "ajax": {
                            "url": "/dynamicticket/getreport",
                            "data": {
                                category_id: id,
                                mode: 'data'
                            },
                            "type": "POST",
                        },
                        "columns": result,
                    });
                }
            });
        }
        updateCharts();
    </script>
@endsection