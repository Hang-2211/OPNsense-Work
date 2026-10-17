<script>
    $(document).ready(function() {
        // Khởi tạo bảng Active Sessions (Tab 2)
        $("#grid-sessions").UIBootgrid({
            search: '/api/firewall/nat_monitoring/querySessions',
            options: {
                sorting: false,
                rowCount: [20, 50, 100, -1]
            }
        });

        // Khởi tạo bảng Traffic Log (Tab 1)
        $("#grid-traffic").UIBootgrid({
            search: '/api/firewall/nat_monitoring/queryTraffic',
            options: {
                sorting: false,
                rowCount: [50, 100, 200, -1]
            }
        });

        // Tự động tải lại bảng khi chuyển Tab
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (e.target.hash === '#tab-sessions') {
                $("#grid-sessions").bootgrid('reload');
            } else if (e.target.hash === '#tab-traffic') {
                $("#grid-traffic").bootgrid('reload');
            }
        });

        $('#btn-refresh-sessions').click(function() {
            $("#grid-sessions").bootgrid('reload');
        });
        $('#btn-refresh-traffic').click(function() {
            $("#grid-traffic").bootgrid('reload');
        });
    });
</script>

<!-- Cấu trúc 2 Tab chuẩn Bootstrap OPNsense -->
<ul class="nav nav-tabs" data-tabs="tabs" id="maintabs">
    <li class="active"><a data-toggle="tab" href="#tab-traffic">{{ lang._("Traffic Log") }}</a></li>
    <li><a data-toggle="tab" href="#tab-sessions">{{ lang._("Active Sessions") }}</a></li>
</ul>

<div class="tab-content content-box">
    <!-- TAB 1: TRAFFIC LOG -->
    <div id="tab-traffic" class="tab-pane fade in active">
        <div class="panel-body">
            <button id="btn-refresh-traffic" class="btn btn-default pull-right" style="margin-bottom: 10px;">
                <i class="fa fa-refresh"></i> {{ lang._("Refresh") }}
            </button>
            <table id="grid-traffic" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="time" data-type="string">{{ lang._("Time") }}</th>
                        <th data-column-id="interface" data-type="string">{{ lang._("Interface") }}</th>
                        <th data-column-id="proto" data-type="string">{{ lang._("Protocol") }}</th>
                        <th data-column-id="src" data-type="string">{{ lang._("Source") }}</th>
                        <th data-column-id="dst" data-type="string">{{ lang._("Destination") }}</th>
                        <th data-column-id="action" data-type="string">{{ lang._("Action") }}</th>
                        <th data-column-id="label" data-type="string">{{ lang._("Rule Description") }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: ACTIVE SESSIONS -->
    <div id="tab-sessions" class="tab-pane fade in">
        <div class="panel-body">
            <button id="btn-refresh-sessions" class="btn btn-default pull-right" style="margin-bottom: 10px;">
                <i class="fa fa-refresh"></i> {{ lang._("Refresh") }}
            </button>
            <table id="grid-sessions" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th data-column-id="proto" data-type="string">{{ lang._("Protocol") }}</th>
                        <th data-column-id="src" data-type="string">{{ lang._("Original Source") }}</th>
                        <th data-column-id="nat" data-type="string">{{ lang._("Translated NAT") }}</th>
                        <th data-column-id="dst" data-type="string">{{ lang._("Destination") }}</th>
                        <th data-column-id="state" data-type="string">{{ lang._("State") }}</th>
                        <th data-column-id="age" data-type="string">{{ lang._("Age") }}</th>
                        <th data-column-id="expires" data-type="string">{{ lang._("Expires") }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>