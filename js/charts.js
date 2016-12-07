/*Sparklines */

// Initiate a recurring data update
$(document).ready(function () {
  var waitTime = '';
  $.getJSON("settings.json", function(json) {
      waitTime = json.update_frequency;
  });
  
  series = [];
  var iteration = 0;
  var old_overview_total_calls = 0;
  var old_overview_login_agents = 0;
  var old_overview_total_sales = 0;
  
	function fetchData() {
		++iteration;

		function onDataReceived(data, target) {
      // Switch to handle charts
      if (target == '#average_drops') {
        $("#average_drops").sparkline(data, {
            type: 'line',
            width: '250',
            height: '30',
            lineColor: '#0ca5e7',
            fillColor: '#e5f3f9'});
      } else if (target == "#overview_total_calls") {
        if (old_overview_total_calls != data[data.length - 1]) {
          $(target).prop('number', data[data.length - 2])
            .animateNumber(
              {
                number: data[data.length - 1]
              },
              5000
            );
          old_overview_total_calls = data[data.length - 1];
        }
      } else if (target == "#overview_login_agents") {
        if (old_overview_login_agents != data[data.length - 1]) {
          $(target).prop('number', data[data.length - 2])
            .animateNumber(
              {
                number: data[data.length - 1]
              },
              5000
            );
          old_overview_login_agents = data[data.length - 1];
        }
      } else if (target == "#overview_total_sales") {
        if (old_overview_total_sales != data[data.length - 1]) {
          $(target).prop('number', data[data.length - 2])
            .animateNumber(
              {
                number: data[data.length - 1]
              },
              5000
            );
          old_overview_total_sales = data[data.length - 1];
        }
      } else if (target == '#hourly_inbound_sales') {
        $(target).sparkline(data, {
          type: 'bar',
          height: '30',
          barWidth: 5,
          barColor: '#f04040',
          tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}}: {{value}}',
          tooltipValueLookups: {
            names: {
              0: 'Eighteen hours ago',
              1: 'Seventeen hours ago',
              2: 'Sixteen hours ago',
              3: 'Fifteen hours ago',
              4: 'Fourteen hours ago',
              5: 'Thirteen hours ago',
              6: 'Twelve hours ago',
              7: 'Eleven hours ago',
              8: 'Ten hours ago',
              9: 'Nine hours ago',
              10: 'Eight hours ago',
              11: 'Seven hours ago',
              12: 'Six hours ago',
              13: 'Five hours ago',
              14: 'Four hours ago',
              15: 'Three hours ago',
              16: 'Two hours ago',
              17: 'Last hour (TBD)'
            }
          }
        });
      } else if (target == '#hourly_outbound_sales') {
        $(target).sparkline(data, {
          type: 'bar',
          height: '30',
          barWidth: 5,
          barColor: '#1eafed',
          tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}}: {{value}}',
          tooltipValueLookups: {
            names: {
              0: 'Eighteen hours ago',
              1: 'Seventeen hours ago',
              2: 'Sixteen hours ago',
              3: 'Fifteen hours ago',
              4: 'Fourteen hours ago',
              5: 'Thirteen hours ago',
              6: 'Twelve hours ago',
              7: 'Eleven hours ago',
              8: 'Ten hours ago',
              9: 'Nine hours ago',
              10: 'Eight hours ago',
              11: 'Seven hours ago',
              12: 'Six hours ago',
              13: 'Five hours ago',
              14: 'Four hours ago',
              15: 'Three hours ago',
              16: 'Two hours ago',
              17: 'Last hour (TBD)'
            }
          }
        });
      } else if (target == '#hourly_close_rate') {
        $(target).sparkline(data, {
          type: 'bar',
          height: '30',
          barWidth: 5,
          barColor: '#fb7d45',
          tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}}: {{value}}%',
          tooltipValueLookups: {
            names: {
              0: 'Eighteen hours ago',
              1: 'Seventeen hours ago',
              2: 'Sixteen hours ago',
              3: 'Fifteen hours ago',
              4: 'Fourteen hours ago',
              5: 'Thirteen hours ago',
              6: 'Twelve hours ago',
              7: 'Eleven hours ago',
              8: 'Ten hours ago',
              9: 'Nine hours ago',
              10: 'Eight hours ago',
              11: 'Seven hours ago',
              12: 'Six hours ago',
              13: 'Five hours ago',
              14: 'Four hours ago',
              15: 'Three hours ago',
              16: 'Two hours ago',
              17: 'Last hour (TBD)'
            }
          }
        });
      } else if (target == '#hourly_sale_revenue') {
        $(target).sparkline(data, {
          type: 'bar',
          height: '30',
          barWidth: 5,
          barColor: '#fb7d45',
          tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:names}}: ${{value}}',
          tooltipValueLookups: {
            names: {
              0: 'Eighteen hours ago',
              1: 'Seventeen hours ago',
              2: 'Sixteen hours ago',
              3: 'Fifteen hours ago',
              4: 'Fourteen hours ago',
              5: 'Thirteen hours ago',
              6: 'Twelve hours ago',
              7: 'Eleven hours ago',
              8: 'Ten hours ago',
              9: 'Nine hours ago',
              10: 'Eight hours ago',
              11: 'Seven hours ago',
              12: 'Six hours ago',
              13: 'Five hours ago',
              14: 'Four hours ago',
              15: 'Three hours ago',
              16: 'Two hours ago',
              17: 'Last hour (TBD)'
            }
          }
        });
      } else {
        $(target).sparkline(data, {
            type: 'line',
            width: '80',
            height: '20',
            lineColor: '#0ca5e7',
            fillColor: '#e5f3f9'
        });
      }
    
      // Switch to handle chart text
      if (target == '#total_talk') {
        var date = new Date(null);
        date.setSeconds(data[data.length - 1]); // specify value for SECONDS here
        date = date.toISOString().substr(11, 8);
        $(target.concat("_text")).html(date);
      } else if (target == '#hourly_inbound_sales') {
        inbound_sales_text = data[data.length - 1];
        $(target.concat("_text")).html(inbound_sales_text);
      } else if (target == '#hourly_outbound_sales') {
        outbound_sales_text = data[data.length - 1];
        $(target.concat("_text")).html(outbound_sales_text);
      } else if (target == '#hourly_close_rate') {
        hourly_close_rate_text = data[data.length - 1];
        $(target.concat("_text")).html(hourly_close_rate_text);
      } else if (target == '#hourly_sale_revenue') {
        hourly_sale_revenue_text = data[data.length - 1];
        $(target.concat("_text")).html(hourly_sale_revenue_text); 
      } else {
        $(target.concat("_text")).html(data[data.length - 1]);
      }
		}

		$.ajax({
			url: 'rolling_agent_stats.php',
			type: 'GET',
			dataType: 'json',
			success: function(data) {
        onDataReceived(data['agents_in_call'], "#agents_on_call");
        onDataReceived(data['agents_on_pause'], "#agents_on_pause");
        onDataReceived(data['agents_ready'], "#agents_ready");
        onDataReceived(data['total_talk'], "#total_talk");
        onDataReceived(data['average_talk'], "#average_talk");
        onDataReceived(data['average_pause'], "#average_pause");
        onDataReceived(data['average_wrap'], "#average_wrap");
        onDataReceived(data['average_drops'], "#average_drops");
        onDataReceived(data['overview_total_calls'], "#overview_total_calls");
        onDataReceived(data['overview_login_agents'], "#overview_login_agents");
        onDataReceived(data['overview_total_sales'], "#overview_total_sales");
        onDataReceived(data['hourly_inbound_sales'], "#hourly_inbound_sales");
        onDataReceived(data['hourly_outbound_sales'], "#hourly_outbound_sales");
        onDataReceived(data['hourly_close_rate'], "#hourly_close_rate");
        onDataReceived(data['hourly_sale_revenue'], "#hourly_sale_revenue");
      }
		});

		if (iteration >= 0) {
			setTimeout(fetchData, parseInt(waitTime));
		} else {
			series = [];
			alreadyFetched = {};
		}
	}

	setTimeout(fetchData, parseInt(waitTime));
  
});
