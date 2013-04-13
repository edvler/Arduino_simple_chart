<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Arduino Chart</title>

		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script> 

		<script type="text/javascript">

		
function Config () {
    this.url = 'http://127.0.0.1/arduino_chart/database/db_access.php';
	this.chart_title = 'Arduino Chart';
	this.chart_subtitle = 'Display charts with more than 10 million values!';
	this.loading_title = 'Loading data from MySQL'
	
	
    this.getURL = function() {
        return this.url;
    };

    this.getTitle = function() {
        return this.chart_title;
    };

    this.getSubTitle = function() {
        return this.chart_subtitle;
    };

    this.getLoadingTitle = function() {
        return this.loading_title;
    };
};


		
$(function() {
	var cfg = new Config();

	
	// See source code from the JSONP handler at https://github.com/highslide-software/highcharts.com/blob/master/samples/data/from-sql.php
	$.getJSON(cfg.getURL() + '?callback=?', function(data) {
	
	Highcharts.setOptions({
		global : {
			useUTC : false
		}
	
	});
				
		// create the chart
		window.chart = new Highcharts.StockChart({
			chart : {
				renderTo : 'container',
				type: 'area',
				zoomType: 'x'
            
			},

			navigator : {
				adaptToUpdatedData: false,
				//baseSeries : data[0],
				series : { 
					data : data[0].data
					}
			},

			scrollbar: {
				liveRedraw: false
			},
			
			title: {
				text: cfg.getTitle()
			},
			
			subtitle: {
				text: cfg.getSubTitle()
			},
			
			rangeSelector : {
				buttons: [{
					type: 'hour',
					count: 1,
					text: '1h'
				}, {
					type: 'day',
					count: 1,
					text: '1d'
				}, {
					type: 'month',
					count: 1,
					text: '1m'
				}, {
					type: 'year',
					count: 1,
					text: '1y'
				}, {
					type: 'all',
					text: 'All'
				}],
				inputEnabled: false, // it supports only days
				selected : 5 // all
			},
			
			xAxis : {
				
				events : {
					afterSetExtremes : afterSetExtremes
				},
				minRange: 3600 * 1000 // one hour
			},
            legend: {
                align: "right",
                layout: "vertical",
                enabled: true,
                verticalAlign: "middle"

            },
			
			series : data
		});
	});
});


/**
 * Load new data depending on the selected min and max
 */
function afterSetExtremes(e) {
	var cfg = new Config();
	
	
	var chart = $('#container').highcharts();
	chart.showLoading(cfg.getLoadingTitle());
	
	var currentExtremes = this.getExtremes(),
		range = e.max - e.min;

	$.getJSON(cfg.getURL() + '?start='+ Math.round(e.min) +	'&end=' + Math.round(e.max) + '&callback=?', function(data) {
		//chart.navigator.series.setData(data[0].data);	
		for (var i = 0; i < data.length; i++) {
			chart.series[i].setData(data[i].data);
		}
		//chart.series=data;
		
		chart.redraw();
		chart.hideLoading();
	});
	
}

		</script>
	</head>
	<body>
		<script src="./js/highstock.js"></script>
		<script src="./js/modules/exporting.js"></script>


		<div id="container" style="height: 650px; min-width: 600px"></div>
	</body>
</html>
