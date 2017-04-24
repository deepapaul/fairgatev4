var FgDashboard = function() {

    var chartResultOptimized;
    
	var stackedBarchartPlot = function(templateSelector,chartResult,nodataMessage) {
					chartResult = JSON.parse(chartResult);
					chartResultOptimized = chartResult;
					var datamax = chartResult.stackedData[0].data.length;
					var barmax = $(templateSelector).outerWidth()/50;
					if(datamax > barmax){
						var barexclude = datamax - barmax;
					}else{
						var barexclude = 0;
					}
					chartResultOptimized.stackedData[0].data = _.filter(chartResult.stackedData[0].data, function(num,key){ return key >= barexclude;});
                    chartResultOptimized.stackedData[1].data = _.filter(chartResult.stackedData[1].data, function(num,key){ return key >= barexclude;});
                    chartResultOptimized.xTicks.ticks = _.filter(chartResult.xTicks.ticks, function(num,key){ return key >= barexclude;});
                    if(chartResultOptimized.xTicks.ticks.length > 0) {
			plotGraph.init_stacked_barchart(templateSelector, chartResultOptimized.stackedData, chartResultOptimized.xTicks, true);                              
                    } else {
                        $(templateSelector).children().remove();
                        $(templateSelector).html('<div class="dashboard-nodata" style="color:#D8D8D8;">'+nodataMessage+'</div>');
                    }
                    bindHoverForStackedBarchart(templateSelector);
    }
	var piechartPlot = function(templateSelector,chartResult,nodataMessage,innerRadiusEnable) {
					chartResult = JSON.parse(chartResult);
					chartResultOptimized = chartResult;
					if(!chartResultOptimized.length){
                                             $(templateSelector).children().remove();
						$(templateSelector).append('<div class="dashboard-nodata" style="color:#D8D8D8;">'+nodataMessage+'</div>');
					} 
					else{
						var width = $(templateSelector).outerWidth();
						var height = $(templateSelector).outerHeight();
						var radius = (width>height)?height*45/100:width*45/100;//70; //in pixel
						var innerRadius = (innerRadiusEnable)?radius/2:0;//30; 					
						plotGraph.init_pie(chartResultOptimized, templateSelector, radius, innerRadius);
						bindHoverForPieChart(templateSelector);
					}
    }
	var barchartPlot = function(templateSelector,chartResult) {
				chartResult = JSON.parse(chartResult);
				var yearGroup = 1;
				var width = $(templateSelector).outerWidth();
				var barCount = parseInt(width/30);
				var yearLength = _.size(chartResult.barData);
				if(barCount >= yearLength){
					yearGroup = 1;
				}else{
					yearGroup = parseInt(1.75*yearLength/barCount);
				}
				var chartResultOptimizedData = [];
				var chartResultOptimizedXticks = [];
				_.map(chartResult.barData, function(num, key){ 
					var keyStart = key - key%yearGroup;
					var keyStop = keyStart + yearGroup - 1;
					var xvalue = (yearGroup == 1)?keyStart : keyStart+'<br/>-<br/>'+keyStop;
					if(chartResultOptimizedData[keyStart]){
						chartResultOptimizedData[keyStart][1] = parseInt(num) + parseInt(chartResultOptimizedData[keyStart][1]);
					}else{
						chartResultOptimizedData[keyStart] = [];
						chartResultOptimizedData[keyStart].push(keyStart/yearGroup);
						chartResultOptimizedData[keyStart].push(num);
						chartResultOptimizedXticks[keyStart] = [];
						chartResultOptimizedXticks[keyStart].push(keyStart/yearGroup);
						chartResultOptimizedXticks[keyStart].push(xvalue);
					}
				});
					
				plotGraph.init_barchart(chartResultOptimizedData, templateSelector,chartResultOptimizedXticks);
                bindHoverForBarChart(templateSelector);
               
	}
	var renderHtml = function(templateSelector, templateScriptSelector, jsonResult) {
		jsonResult = JSON.parse(jsonResult);
        var template = $(templateScriptSelector).html();
        var result_data = _.template(template, {data: jsonResult});
        $(templateSelector).append(result_data);
    }
	var bindHoverForPieChart = function(templateSelector) {        
        var previousPoint = [0,0,0];
        $(templateSelector).bind("plothover", function (event, pos, item) {                    
            if (item) {
                var label = item.series.label;
                var x = item.datapoint[0].toFixed(0);
                $("#tooltip").html(label+": "+ item.series.data[0][1] +" ("+ x+"%"+")")
                        .css({top: pos.pageY+10, left: pos.pageX+10,"background-color":"#000", "opacity":0.5,"color":"white"})
                        .fadeIn(200);
            } else {
                $("#tooltip").hide();
                previousPoint = [0,0,0];
            }
        });                
    }
	var bindHoverForStackedBarchart = function(templateSelector) {
        var previousPoint = [0,0,0];
        $(templateSelector).bind("plothover", function (event, pos, item) {                    
            if (item) {                
                if (previousPoint[0] != item.datapoint[0]
                    || previousPoint[1] != item.datapoint[1]
                    || previousPoint[2] != item.datapoint[2]
                ) {
                    previousPoint = item.datapoint;
                    var x = item.datapoint[0],
                        y = item.datapoint[1] - item.datapoint[2];
                    $("#tooltip").html(item.series.label+": " + y)
                        .css({top: item.pageY+5, left: item.pageX+5,"background-color":"#000", "opacity":0.5,"color":"white"})
                        .fadeIn(200);
                }
            } else {
                $("#tooltip").hide();
                previousPoint = [0,0,0];
            }
        });                
    } 
	var bindHoverForBarChart = function(templateSelector) {
        var previousPoint = [0,0,0];
        $(templateSelector).bind("plothover", function (event, pos, item) { 
            if (item) {                
                if (previousPoint[0] != item.datapoint[0]
                    || previousPoint[1] != item.datapoint[1]
                ) {
                    previousPoint = item.datapoint;
                    var x = item.datapoint[0],
                        y = item.datapoint[1];
                       if(y>1){
                    $("#tooltip").html(y+" "+persons)
                        .css({top: item.pageY+5, left: item.pageX+5,"background-color":"#000", "opacity":0.5,"color":"white"})
                        .fadeIn(200);
                       }else{
                            $("#tooltip").html(y+" "+person)
                        .css({top: item.pageY+5, left: item.pageX+5,"background-color":"#000", "opacity":0.5,"color":"white"})
                        .fadeIn(200);
                       }
                }
            } else {
                $("#tooltip").hide();
                previousPoint = [0,0,0];
            }
        });                
    } 
    
    var successCallBack = function() {
            $('.fg-plus-click').on('click', function(){
                $(this).parent().find('.fg-bithday-contact').removeClass("hide");
                $(this).parent().find('.fg-bithday-contact1').addClass("hide");
                $(this).parent().find('.fg-minus-click').removeClass("hide");
                $(this).parent().find('.fg-plus-click').addClass("hide");
            });
            $('.fg-minus-click').on('click', function(){
                $(this).parent().find('.fg-bithday-contact').addClass("hide");
                $(this).parent().find('.fg-bithday-contact1').removeClass("hide");
                $(this).parent().find('.fg-minus-click').addClass("hide");
                $(this).parent().find('.fg-plus-click').removeClass("hide");
            });
        }  
    return {
        initStackedBarchart: function(templateSelector,jsonPath, nodataMessage) {
            $(function() {
                $.getJSON(jsonPath, function (result) {
					stackedBarchartPlot(templateSelector,JSON.stringify(result), nodataMessage); 
					$(window).resize(function() {
						stackedBarchartPlot(templateSelector,JSON.stringify(result), nodataMessage);
					});
					$(window).load(function() {
						stackedBarchartPlot(templateSelector,JSON.stringify(result), nodataMessage);
					});
				});	
            });
        },
		initPiechart: function(templateSelector,jsonPath,nodataMessage,innerRadiusEnable) {
            $(function() {
                $.getJSON(jsonPath, function (result) {
					piechartPlot(templateSelector,JSON.stringify(result),nodataMessage,innerRadiusEnable); 
					$(window).resize(function() {
						piechartPlot(templateSelector,JSON.stringify(result),nodataMessage,innerRadiusEnable);
					});
					$(window).load(function() {
						piechartPlot(templateSelector,JSON.stringify(result),nodataMessage,innerRadiusEnable);
					});
				});	
            });
        },
		initBarchart: function(templateSelector,jsonPath,nodataMessage,innerRadiusEnable) {
            $(function() {
                $.getJSON(jsonPath, function (result) {
					barchartPlot(templateSelector,JSON.stringify(result)); 
					$(window).resize(function() {
						barchartPlot(templateSelector,JSON.stringify(result));
					});
					$(window).load(function() {
						barchartPlot(templateSelector,JSON.stringify(result));
					});
				});	
            });
        },
		initRenderHtml: function(templateSelector,jsonPath,templateScriptSelector,callBackFn) {
            $(function() {
                $.getJSON(jsonPath, function (result) {
					renderHtml(templateSelector,templateScriptSelector, JSON.stringify(result));
                                        if (callBackFn) {
                                            successCallBack();
                                        }
				});	
            });
        }
    };
}();

