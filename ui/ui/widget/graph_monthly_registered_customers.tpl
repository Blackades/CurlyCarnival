<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bar-chart"></i> {Lang::T('Monthly Registered Customers')}</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
            <a href="{Text::url('dashboard&refresh')}" class="btn btn-box-tool" title="Refresh">
                <i class="fa fa-refresh"></i>
            </a>
        </div>
    </div>
    <div class="box-body">
        <canvas class="chart" id="chart" style="height: 250px;"></canvas>
    </div>
</div>


<script type="text/javascript">
    {literal}
        document.addEventListener("DOMContentLoaded", function() {
            var counts = JSON.parse('{/literal}{$monthlyRegistered|json_encode}{literal}');

            var monthNames = [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ];

            var labels = [];
            var data = [];

            for (var i = 1; i <= 12; i++) {
                var month = counts.find(count => count.date === i);
                labels.push(month ? monthNames[i - 1] : monthNames[i - 1].substring(0, 3));
                data.push(month ? month.count : 0);
            }

            var ctx = document.getElementById('chart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Registered Members',
                        data: data,
                        backgroundColor: 'rgba(24, 144, 255, 0.1)',
                        borderColor: '#1890ff',
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: 'rgba(24, 144, 255, 0.2)'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });
        });
    {/literal}
</script>