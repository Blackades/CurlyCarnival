<div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 24px; overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #1890ff 0%, #40a9ff 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fa fa-th" style="color: white; font-size: 18px;"></i>
            </div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #262626;">{Lang::T('Monthly Registered Customers')}</h3>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-default btn-sm" data-widget="collapse" style="border-radius: 6px; border: 1px solid #d9d9d9; background: white; padding: 6px 12px;">
                <i class="fa fa-minus" style="color: #595959;"></i>
            </button>
            <a href="{Text::url('dashboard&refresh')}" class="btn btn-default btn-sm" style="border-radius: 6px; border: 1px solid #d9d9d9; background: white; padding: 6px 12px; text-decoration: none;">
                <i class="fa fa-refresh" style="color: #595959;"></i>
            </a>
        </div>
    </div>
    <div style="padding: 24px;">
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