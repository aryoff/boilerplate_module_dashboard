@extends('layouts.app')

@section('module_css')
    <link rel="stylesheet" href="{{ mix('css/dashboard.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-9">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Campaign</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableRealtimeCampaign" class="table-striped no-margin" style="width:100%">
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
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Agent</h3>
                </div>
                <div class="card-body">
                    <ul class="nav flex-column">
                        <li class="nav-item"> Total <span class="float-right badge bg-primary"><h6><span id="staffTotal">0</span></h6></span>
                        </li>
                        <li class="nav-item"> Online <span class="float-right badge bg-info"><h6><span id="staffOnline">0</span></h6></span>
                        </li>
                        <li class="nav-item"> AUX <span class="float-right badge bg-success"><h6><span id="staffAux">0</span></h6></span><br><br>
                            <ul class="nav flex-column" style="padding-left: 10px;">
                                <li class="nav-item"> Toilet <span class="float-right badge bg-primary"><h6><span id="staffAuxToilet">0</span></h6></span>
                                </li>
                                <li class="nav-item"> Brief <span class="float-right badge bg-info"><h6><span id="staffAuxBrief">0</span></h6></span>
                                </li>
                                <li class="nav-item"> Break <span class="float-right badge bg-success"><h6><span id="staffAuxBreak">0</span></h6></span>
                                </li>
                                <li class="nav-item"> Sholat <span class="float-right badge bg-info"><h6><span id="staffAuxSholat">0</span></h6></span>
                                </li>
                                <li class="nav-item"> Others <span class="float-right badge bg-success"><h6><span id="staffAuxOther">0</span></h6></span>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-1">
            <div class="card card-primary" id="nossaLastUpdateCard">
                <div class="card-header">
                    <h3 class="card-title">Nossa LUP</h3>
                </div>
                <div class="card-body">
                    <h6 class="d-flex justify-content-center mx-auto"><span id="nossaLastUpdateValue"></span></h6>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Agent Status</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableRealtimeStaff" class="table-striped no-margin" style="width:100%">
                            <thead>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
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
        var tableRealtimeStaff;
        var tableRealtimeCampaign;
        generateTableRealtimeStaff();
        generateTableRealtimeCampaign();
        getTotalAgentOnline();
        getLastUpdateNossa();
        function generateTableRealtimeCampaign() {
            $.ajax({
                url: "{{ url('/dashboard/getDataCampaignC4') }}",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    if ($.fn.DataTable.isDataTable('#tableRealtimeCampaign')) {
                        tableRealtimeCampaign.destroy();
                        $('#tableRealtimeCampaign').empty();
                    }
                    tableRealtimeCampaign = $('#tableRealtimeCampaign').DataTable({
                        "searching": false,
                        "info": false,
                        "paging": false,
                        "ordering": false,
                        "stateSave": true,
                        "data": data.data,
                        "columns": [{
                                "data": "nama",
                                "title": "Nama Campaign"
                            },
                            {
                                "data": "total",
                                "title": "WO",
                            },
                            {
                                "data": "sisa",
                                "title": "Saldo",
                            },
                            {
                                "data": "assigned",
                                "title": "Assign",
                            },
                            {
                                "data": "pickup",
                                "title": "Picked Up",
                            },
                            {
                                "data": "submit",
                                "title": "Submit",
                            },
                            {
                                "data": "staffed",
                                "title": "Staff",
                            },
                        ],
                    });
                    setTimeout(generateTableRealtimeCampaign, 60000);
                },
                error: function (data) {
                    console.log(data);
                }
            })
        }

        function generateTableRealtimeStaff() {
            $.ajax({
                url: "{{ url('/dashboard/getRealtimeStaffC4') }}",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    if ($.fn.DataTable.isDataTable('#tableRealtimeStaff')) {
                        tableRealtimeStaff.destroy();
                        $('#tableRealtimeStaff').empty();
                    }
                    tableRealtimeStaff = $('#tableRealtimeStaff').DataTable({
                        "fixedHeader": {
                            header: true,
                            footer: true
                        },
                        "initComplete": function (settings, json) {
                            $("#tableRealtimeStaff").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                            this.api().columns('campaign_name:name').every(function () {
                                let column = this;
                                let select = $('<select><option value="" selected>All Campaign</option></select>')
                                    .appendTo($(column.header()).empty())
                                    .on('change', function () {
                                        let val = $.fn.dataTable.util.escapeRegex(
                                            $(this).val()
                                        );
                                        column
                                            .search(val ? '^' + val + '$' : '', true, false)
                                            .draw();
                                    });
                                column.data().unique().sort().each(function (d, j) {
                                    select.append('<option value="' + d + '">' + d + '</option>')
                                });
                            });
                        },
                        "lengthMenu": [
                            [10, 25, 50, 100, -1],
                            [10, 25, 50, 100, "All"]
                        ],
                        "pageLength": -1,
                        "scrollY": "250px",
                        "scrollCollapse": true,
                        "paging": true,
                        "stateSave": true,
                        "data": data.data,
                        "columns": [{
                                "data": "name",
                                "title": "Nama Agent"
                            },
                            {
                                "name": "campaign_name",
                                "data": "campaign_name",
                                "title": "Campaign",
                            },
                            {
                                "data": "status",
                                "title": "Status",
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
                                            return '<h4 class="badge btn-info">No Data</h4>';
                                            break;
                                    }
                                }
                            },
                            {
                                "data": "total_aux",
                                "title": "Total Aux",
                                "render": function (data, type, row, meta) {
                                    let num;
                                    if (data == 0) {
                                        num = 0;
                                    } else {
                                        num = data / 60;
                                    }
                                    return num.toFixed(2);
                                }
                            },
                            {
                                "data": "total_online",
                                "title": "Staffed Time",
                                "render": function (data, type, row, meta) {
                                    let num;
                                    if (data == 0) {
                                        num = 0;
                                    } else {
                                        num = data / 60;
                                    }
                                    return num.toFixed(2);
                                }
                            },
                            {
                                "data": "consume",
                                "title": "Consume",
                                "render": function (data, type, row, meta) {
                                    return row['close'] + row['onprogress'];
                                }
                            },
                            {
                                "data": "close",
                                "title": "Close"
                            },
                            {
                                "data": "onprogress",
                                "title": "Open"
                            },
                            {
                                "data": "handling_time",
                                "title": "AHT",
                                // "width": "5%",
                                "render": function (data, type, row, meta) {
                                    let num;
                                    if (data == 0) {
                                        num = 0;
                                    } else {
                                        num = (data / 60) / row['consume'];
                                    }
                                    return num.toFixed(2);
                                }
                            },
                            {
                                "data": "pickup",
                                "title": "Pick Up",
                            },
                            {
                                "data": "pticket",
                                "title": "Ticket",
                            },
                        ],
                    });
                    setTimeout(generateTableRealtimeStaff, 60000);
                },
                error: function (data) {
                    console.log(data);
                }
            })
        }

        function getTotalAgentOnline() {
            $.ajax({
                url: "{{ url('/dashboard/getTotalAgentOnlineC4') }}",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    document.getElementById('staffOnline').innerHTML = data.online;
                    document.getElementById('staffAux').innerHTML = data.aux;
                    document.getElementById('staffTotal').innerHTML = parseInt(data.aux) + parseInt(data.online);
                    document.getElementById('staffAuxToilet').innerHTML = data.aux_4;
                    document.getElementById('staffAuxSholat').innerHTML = data.aux_6;
                    document.getElementById('staffAuxBreak').innerHTML = data.aux_7;
                    document.getElementById('staffAuxBrief').innerHTML = data.aux_8;
                    document.getElementById('staffAuxOther').innerHTML = parseInt(data.aux_1) + parseInt(data.aux_2) + parseInt(data.aux_3) + parseInt(data.aux_5) + parseInt(data.aux_9);
                    setTimeout(getTotalAgentOnline, 60000);
                },
                error: function (data) {
                    console.log(data);
                }
            })
        }

        function getLastUpdateNossa() {
            $.ajax({
                url: "{{ url('/dashboard/getLastUpdateNossaC4') }}",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    if (data) {
                        document.getElementById('nossaLastUpdateValue').innerHTML = data.hari+'<br>'+data.jam;
                        if (data.delta_last_update > 10) {
                            document.getElementById('nossaLastUpdateCard').classList.remove("bg-warning");
                            document.getElementById('nossaLastUpdateCard').classList.remove("bg-success");
                            document.getElementById('nossaLastUpdateCard').classList.add("bg-danger");
                        } else if (data.delta_last_update > 6) {
                            document.getElementById('nossaLastUpdateCard').classList.add("bg-warning");
                            document.getElementById('nossaLastUpdateCard').classList.remove("bg-success");
                            document.getElementById('nossaLastUpdateCard').classList.remove("bg-danger");
                        } else {
                            document.getElementById('nossaLastUpdateCard').classList.remove("bg-danger");
                            document.getElementById('nossaLastUpdateCard').classList.remove("bg-warning");
                            document.getElementById('nossaLastUpdateCard').classList.add("bg-success");
                        }

                    }
                    setTimeout(getLastUpdateNossa, 60000);
                },
                error: function (data) {
                    console.log(data);
                }
            })
        }
    </script>
@endsection