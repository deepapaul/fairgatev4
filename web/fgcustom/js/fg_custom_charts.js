var hoverable = true;
var clickable = true;
var plotGraph = {
        init_pie : function(data, containerId, radius, innerRadius){
            var options = {
                series: {
                    pie: {
                        show: true,
                        radius: radius,
                        innerRadius:innerRadius,
                        label: {
                            show: false
                        }
                    }
                },
                grid: {
                    hoverable: hoverable, 
                    clickable: clickable
                },
                legend: {
                    show: false
                }
            };            
            $.plot($(containerId), data, options);
        },
        init_barchart : function(barData, barContainerId, xticks, yticks){
            var options = {
                    series:{
                        bars:{show: true}
                    },
                    bars:{
                        barWidth: 0.8,
                        lineWidth: 0, // in pixels
                        shadowSize: 0,
                        align: 'center'
                    },  
                    xaxis: {
                        minTickSize: 1,
                        tickDecimals:0,
                        ticks: xticks
                    },
                    yaxis: {
                        min:0,
                        minTickSize: 1,
                        tickDecimals:0,
                        ticks: yticks
                    },
                    grid:{
                        hoverable: true, 
                        clickable: true,
                        tickColor: "#eee",
                        borderColor: "#eee",
                        borderWidth: 1                                               
                    }
            };
            $.plot($(barContainerId),
                [{
                    data: barData,
                    lines: {
                        lineWidth: 1,
                    },
                    shadowSize: 0
                }]
            , options);
            
        },
        init_stacked_barchart : function(stacked_container_id, stack_data, xTicks, showLegendFlag){
            var bars = true,
            lines = false,
            steps = false;
            var options = {
                        series: {
                            stack: true,
                            lines: {
                                show: lines,
                                fill: 1,
                                fillOpacity:1.0,
                                steps: steps,
                                lineWidth: 0, // in pixels
                            },
                            bars: {
                                show: bars,
                                barWidth: 0.5,
                                lineWidth: 0, // in pixels
                                shadowSize: 0,
                                align: 'center'
                            }
                        },
                        grid: {
                            hoverable: true,                             
                            tickColor: "#eee",
                            borderColor: "#eee",
                            borderWidth: 1                            
                        },
                        legend: {
                            show: showLegendFlag,
                            position: "ne",
                            noColumns: 1
                        },
                        xaxis: xTicks,
                        yaxis: {                            
                            tickDecimals:0
                        }
                    };

            $.plot(stacked_container_id, stack_data, options);
        }
        
    }