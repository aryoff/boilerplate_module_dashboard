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
                    label: 'Waitlist Campaign',
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
                },
                events: false,
                animation: {
                    duration: 500,
                    easing: "easeOutQuart",
                    onComplete: function () {
                        barLabel(this);
                    }
                }
            }
        });
        const topOccupancyChart = new Chart(document.getElementById('topOccupancyChart'), {
            type: 'horizontalBar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Top Agent',
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
                },
                events: false,
                animation: {
                    duration: 500,
                    easing: "easeOutQuart",
                    onComplete: function () {
                        barLabel(this);
                    }
                }
            }
        });
        const bottomOccupancyChart = new Chart(document.getElementById('bottomOccupancyChart'), {
            type: 'horizontalBar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Bottom Agent',
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
                },
                events: false,
                animation: {
                    duration: 500,
                    easing: "easeOutQuart",
                    onComplete: function () {
                        barLabel(this);
                    }
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
                maintainAspectRatio: true,
                events: false,
                animation: {
                    duration: 500,
                    easing: "easeOutQuart",
                    onComplete: function () {
                        let ctx = this.chart.ctx;
                        ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';

                        this.data.datasets.forEach(function (dataset) {
                            for (let i = 0; i < dataset.data.length; i++) {
                                let model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,
                                    total = dataset._meta[Object.keys(dataset._meta)[0]].total,
                                    mid_radius = model.innerRadius + (model.outerRadius - model.innerRadius) / 2,
                                    start_angle = model.startAngle,
                                    end_angle = model.endAngle,
                                    mid_angle = start_angle + (end_angle - start_angle) / 2;

                                let x = mid_radius * Math.cos(mid_angle);
                                let y = mid_radius * Math.sin(mid_angle);

                                ctx.fillStyle = dataset.borderColor[i];

                                let val = dataset.data[i];
                                let percent = String(Math.round(val / total * 100)) + "%";

                                if (val != 0) {
                                    ctx.fillText(dataset.data[i], model.x + x, model.y + y);
                                    // Display percent in another line, line break doesn't work for fillText
                                    ctx.fillText(percent, model.x + x, model.y + y + 15);
                                }
                            }
                        });
                    }
                }
            }
        });
        function barLabel(barChart) {
            let ctx = barChart.chart.ctx;
            ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
            ctx.textAlign = "center";
            ctx.textBaseline = "bottom";
            barChart.data.datasets.forEach(function (dataset) {
                for (let i = 0; i < dataset.data.length; i++) {
                    let model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,
                        val = dataset.data[i];
                    if (val != 0) {
                        ctx.fillStyle = dataset.borderColor[i];
                        ctx.fillText(dataset.data[i], model.x * 0.9, model.y);
                    }
                }
            });
        }
        function getTotalAgentOnlineT2() {
            $.ajax({
                url: "{{ url('/dashboard/getTotalAgentOnlineT2') }}",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    if (data) {
                        let jParse = JSON.parse(data);
                        console.log(jParse);
                        topOccupancyChart.data.datasets[0].data = jParse.top_value;
                        topOccupancyChart.data.labels = jParse.top_label;
                        topOccupancyChart.update();
                        bottomOccupancyChart.data.datasets[0].data = jParse.bottom_value;
                        bottomOccupancyChart.data.labels = jParse.bottom_label;
                        bottomOccupancyChart.update();
                        if ($.fn.DataTable.isDataTable('#topAgentStatusTable')) {
                            topAgentStatusTable.destroy();
                            $('#topAgentStatusTable').empty();
                        }
                        topAgentStatusTable = $('#topAgentStatusTable').DataTable({
                            "fixedHeader": {
                                header: true,
                                footer: true
                            },
                            "initComplete": function (settings, json) {
                                $("#topAgentStatusTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                            //     this.api().columns('campaign_name:name').every(function () {
                            //         let column = this;
                            //         let select = $('<select><option value="" selected>All Campaign</option></select>')
                            //             .appendTo($(column.header()).empty())
                            //             .on('change', function () {
                            //                 let val = $.fn.dataTable.util.escapeRegex(
                            //                     $(this).val()
                            //                 );
                            //                 column
                            //                     .search(val ? '^' + val + '$' : '', true, false)
                            //                     .draw();
                            //             });
                            //         column.data().unique().sort().each(function (d, j) {
                            //             select.append('<option value="' + d + '">' + d + '</option>')
                            //         });
                            //     });
                            },
                            "lengthMenu": [
                                [10, 25, 50, 100, -1],
                                [10, 25, 50, 100, "All"]
                            ],
                            "pageLength": 10,
                            "scrollY": "250px",
                            "scrollCollapse": true,
                            "paging": true,
                            "stateSave": true,
                            "data": jParse.data,
                            "columns": [{
                                    "data": "agent_name",
                                    "title": "Nama Agent"
                                },
                                {
                                    "data": "distribution_status",
                                    "title": "Distribution Status",
                                    "render": function (data, type, row, meta) {
                                        switch (data) {
                                            case 'online':
                                                return '<h4 class="badge btn-success">Online</h4>';
                                                break;
                                            case 'offline':
                                                return '<h4 class="badge btn-danger">Offline</h4>';
                                                break;
                                            case 'aux_1':
                                                return '<h4 class="badge btn-warning">Konsultasi</h4>';
                                                break;
                                            case 'aux_2':
                                                return '<h4 class="badge btn-warning">Supporting</h4>';
                                                break;
                                            case 'aux_3':
                                                return '<h4 class="badge btn-warning">Gangguan</h4>';
                                                break;
                                            case 'aux_4':
                                                return '<h4 class="badge btn-warning">Toilet</h4>';
                                                break;
                                            case 'aux_5':
                                                return '<h4 class="badge btn-warning">Air Minum</h4>';
                                                break;
                                            case 'aux_6':
                                                return '<h4 class="badge btn-warning">Sholat</h4>';
                                                break;
                                            case 'aux_7':
                                                return '<h4 class="badge btn-warning">Lunch Break</h4>';
                                                break;
                                            case 'aux_8':
                                                return '<h4 class="badge btn-warning">Briefing</h4>';
                                                break;
                                            case 'aux_9':
                                                return '<h4 class="badge btn-warning">Update System</h4>';
                                                break;
                                            default:
                                                return '<h4 class="badge btn-info">'+data+'</h4>';
                                                break;
                                        }
                                    }
                                },
                                {
                                    "data": "pbx_status",
                                    "title": "PBX Status"
                                },
                                {
                                    "data": "connected_number",
                                    "title": "Connected Number"
                                },
                                {
                                    "data": "status_duration",
                                    "title": "Durasi",
                                    "render": function (data, type, row, meta) {
                                        let num;
                                        let ret;
                                        if (data == 0) {
                                            ret = '00:00:00';
                                        } else {
                                            num = data;
                                            let sec = num % 60;
                                            if (sec<10) {
                                                ret = '0'+sec;
                                            } else {
                                                ret = sec;
                                            }
                                            num = (num-sec) / 60;
                                            let min = num % 60;
                                            if (min<10) {
                                                ret = '0'+min+':'+ret;
                                            } else {
                                                ret = min+':'+ret;
                                            }
                                            let hour = (num-min) / 60;
                                            ret = hour+':'+ret;
                                        }
                                        return ret;
                                    }
                                },
                                {
                                    "data": "aht",
                                    "title": "AHT",
                                    "render": function (data, type, row, meta) {
                                        if (parseInt(row['total_call'])!=0) {
                                            return Math.round(parseInt(row['handlingtime']) / parseInt(row['total_call']));
                                        } else {
                                            return 0;
                                        }
                                    }
                                },
                            ],
                        });

                    }
                    setTimeout(getTotalAgentOnlineT2, 60000);
                },
                error: function (data) {
                    console.log(data);
                }
            })
        }
        function getWaitlistT2() {
            $.ajax({
                url: "{{ url('/dashboard/getWaitlistT2') }}",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    if (data) {
                        let jParse = JSON.parse(data);
                        waitlistPerCampaignChart.data.datasets[0].data = jParse.count;
                        waitlistPerCampaignChart.data.labels = jParse.name;
                        waitlistPerCampaignChart.update();
                        waitlistChart.data.datasets[0].data = jParse.total_count;
                        waitlistChart.data.labels = jParse.total_label;
                        waitlistChart.update();
                    }
                    setTimeout(getWaitlistT2, 60000);
                },
                error: function (data) {
                    console.log(data);
                }
            })
        }
        getTotalAgentOnlineT2();
        getWaitlistT2();
    </script>
@endsection