$(document).ready(function() {
	var options = {
		lines: {
			show: true, fill: true
		},
		points: {
			show: true
		},
		grid: { 
			hoverable: true, clickable: false, borderWidth:0 
		},
		xaxis: {
			tickDecimals: 0,
			tickSize: 1
		},
		colors: ["#1eafed", "#1eafed"]
	};

	// Initiate a recurring data update
	$(document).ready(function () { 
    var waitTime = '';
    $.getJSON("settings.json", function(json) {
        waitTime = json.update_frequency;
    });

		data = [];
		alreadyFetched = {};

		$.plot("#calls-rolling-chart", data, options);

		var iteration = 0;

		function fetchData() {

			++iteration;

			function onDataReceived(series) {

				// Load all the data in one pass; if we only got partial
				// data we could merge it with what we already have.
				data = [ series ];
				$.plot("#calls-rolling-chart", data, options);
			}

			// Normally we call the same URL - a script connected to a
			// database - but in this case we only have static example
			// files, so we need to modify the URL.
			$.ajax({
				url: "rolling_calls_update.php",
				type: "GET",
				dataType: "json",
				success: onDataReceived
			});

			if (iteration >= 0) {
				setTimeout(fetchData, parseInt(waitTime));
			} else {
				data = [];
				alreadyFetched = {};
			}
		}

		setTimeout(fetchData, parseInt(waitTime));
	});

	// Add the Flot version string to the footer
	$("#footer").prepend("Flot " + $.plot.version + " &ndash; ");
});
