/*
 Highcharts JS v6.1.1 (2018-06-27)

 (c) 2009-2017 Torstein Honsi

 License: www.highcharts.com/license
*/
(function(b){"object"===typeof module&&module.exports?module.exports=b:b(Highcharts)})
(function(b){
	(function(a){
		a.createElement("link",{href:"https://fonts.googleapis.com/css?family\x3dSignika:400,700",rel:"stylesheet",type:"text/css"},
		null,document.getElementsByTagName("head")[0]);
		a.wrap(a.Chart.prototype,"getContainer",
		function(a){a.call(this);this.container.style.background="url(https://www.highcharts.com/samples/graphics/sand.png)"});
		a.theme={colors:"#adadad #f81f4b #cbcbcb #8d4654 #7798BF #aaeeee #ff0066 #eeaaee #55BF3B #DF5353 #7798BF #aaeeee".split(" "),

	chart:
	{
		backgroundColor:null,
		
		style:
			{
				fontFamily:"Signika, serif"
			}
		},

		title:
			{
				style:
					{
						color:"black",
						fontSize:"16px",
						fontWeight:"bold"
					}
			},
			subtitle:
				{
					style:
						{
							color:"black"
						}
				},

			tooltip:
				{
					borderWidth:0
				},

			legend:
				{
					itemStyle:
					{
						fontWeight:"bold",
						fontSize:"13px"
					}
				},

			xAxis:
				{
					labels:
						{
							style:
						{
					color:"#6e6e70"
				}
			}
		},
		yAxis:
			{
				labels:
					{
						style:
							{
								color:"#6e6e70"
							}
					}
			},
		plotOptions:
			{
				series:{
					shadow: false
				},
				candlestick:
					{
						lineColor:"#404048"
					},
				map:
					{
						shadow:0
				}
			},
		navigator:
			{
				xAxis:
					{
						gridLineColor:"#D0D0D8"
					}
			},

		rangeSelector:
			{
				buttonTheme:
						{
							fill:"#2bb528",
							stroke:"#C0C0C8",
							"stroke-width":1,
							states:
								{
									select:
										{
											fill:"#D0D0D8"
										}
								}
						}
			},
		scrollbar:
			{
				trackBorderColor:"#C0C0C8"
			},

		background2:"#E0E0E8"
	};
	a.setOptions(a.theme)})(b)});