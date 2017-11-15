/**
 * Created by Muhammad Rehman on 3/4/2016.
 */
function wsa_create_chart(data_array, chart_content, title, colors , legend, is_3d) {

    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {

        var data = google.visualization.arrayToDataTable( data_array );

        var options = {
            title: title,
            colors: colors,
            legend: legend,
            tooltip: {isHtml: true, trigger: 'selection' },
            pieSliceText: 'label',
            is3D: is_3d,
            pieStartAngle: '',
            slices: {
                4: {offset: 0.2},
                12: {offset: 0.3},
                14: {offset: 0.4},
                15: {offset: 0.5}
            },
            'width':500,
            'height':250
        };

        var chart = new google.visualization.PieChart(document.getElementById(chart_content));

        chart.draw(data, options);
    }
}

function wsa_create_line_chart(data_array, chart_content, title) {

    google.charts.setOnLoadCallback(drawChart)

    function drawChart() {
        var chartDiv = document.getElementById('monthly_enrolled');

        var data = new google.visualization.DataTable();
        data.addColumn('date', 'Month');
        data.addColumn('number', "Enrolled Students");
        data.addColumn({'type': 'string', 'role': 'tooltip', 'p': {'html': true}})

        data.addRows( data_array );

        var materialOptions = {
            chart: {
                title: 'Enrolled Students by Month'
            },
            width: 1000,
            height: 300,
            series: {
                // Gives each series an axis name that matches the Y-axis below.
                0: {axis: 'Time'}
            },
            axes: {
                // Adds labels to each axis; they don't have to match the axis names.
                y: {
                    Time: {label: 'Enrolled Students'}
                }
            }
        };

        function drawMaterialChart() {
            var materialChart = new google.charts.Line(chartDiv);
            materialChart.draw(data, materialOptions);
        }

        drawMaterialChart();
    }
}
