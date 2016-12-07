$(document).ready(function() {
  var stack = true, bars = true, lines = false, steps = false;
  
  var options = {
    xaxis: {
      mode: "categories",
      tickLength: 0
    },
    series: {
      stack: stack,
      lines: { show: lines, fill: true, steps: steps },
      bars: { show: bars, barWidth: 0.8 }
    },
		grid: { 
			borderWidth: 0, hoverable: true, color: "#777" 
		},
		colors: ["#ff2424", "#ff6c24"],
    bars: {
      show: true,
      lineWidth: 0,
      fill: true,
      align: "center",
      fillColor: { colors: [ { opacity: 0.9 }, { opacity: 0.8 } ] }
    }
	};

	// Initiate a recurring data update
	$(document).ready(function () {
    var waitTime = '';
    $.getJSON("settings.json", function(json) {
        waitTime = json.update_frequency;
    });

		bar_data = [];
		alreadyFetched = {};

		$.plot("#agent_v_sales", bar_data, options);

		var iteration = 0;

		function fetchData() {

			++iteration;

			function onDataReceived(series) {

				// Load all the data in one pass; if we only got partial
				// data we could merge it with what we already have.
				bar_data = series;
				$.plot("#agent_v_sales", bar_data, options);
			}
      
			$.ajax({
				url: "rolling_agents_v_sales.php",
				type: "GET",
				dataType: "json",
				success: onDataReceived
			});

			if (iteration >= 0) {
				setTimeout(fetchData, parseInt(waitTime));
			} else {
				bar_data = [];
				alreadyFetched = {};
			}
		}

		// Wait one half second, then execute to populate initial data
    setTimeout(fetchData, parseInt('500'));
	});
});
