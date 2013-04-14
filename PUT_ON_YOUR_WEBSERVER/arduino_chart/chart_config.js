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