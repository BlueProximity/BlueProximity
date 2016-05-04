function TimestampParser(container){
	TimestampParser.container = container;
}

TimestampParser.loadData = function(url){
	TimestampParser.url = url;
	TimestampParser.jsonRequest();
};

TimestampParser.jsonRequest = function(){
	LoginSplash.removeErrors();
	$.getJSON( TimestampParser.url, TimestampParser.parseData ).fail( function(){
		LoginSplash.globalError("action_lookup_user", "User not found", 1);
		LoginSplash.insertError("lu_mac", "", "MAC not found.");
		LoginSplash.insertError("lu_fullname", "", "Person not found.");
	});
};

TimestampParser.parseData = function(data){
	setTimeout(TimestampParser.jsonRequest, 5000);
	
	$("#user_container").show();
	$("#user_name").html( $("#lu_fullname").val() );
	
	for(i in data){
		var container = TimestampParser.buildParse(data[i]);
		var header = container.children(".beacon:first");
		var content = container.children(".beacon_content:first");
		
		var append = true;
		TimestampParser.container.children(".beacon").each(function(){
			if($(this).attr("mac") == header.attr("mac")){
				var workingDOM = $(this).next();
				workingDOM.children(".beacon_subcontent.summary_data").html( content.children(".beacon_subcontent.summary_data").html() );
				workingDOM.children(".beacon_subcontent.raw_data").html( content.children(".beacon_subcontent.raw_data").html() );
				workingDOM.children(".beacon_subcontent.parsed_data").html( content.children(".beacon_subcontent.parsed_data").html() );
				return (append = false);
			}
		});
		
		if( append )
			TimestampParser.container.append( container.html() );
	}
	
	$(".expandable").unbind("click");
	$(".expandable").click(function(){
		var sibling = $(this).next();
		
		if(sibling.css("display") == "none")
			sibling.show();
		else
			sibling.hide();
	});
	
	LoginSplash.toggleSplash(true);
};

TimestampParser.formatMac = function(flatMac){
	var mac = "";
	for(i in flatMac){
		if(i != 0 && i != (flatMac.length-1) && i % 2 == 0)
			mac += ':';
		mac += flatMac[i];
	}
	
	return mac;
};

TimestampParser.formatTimestamp = function(flatTS){
	var t = flatTS.split(/[- :]/);
	return moment(new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
};

TimestampParser.buildParse = function(data){
	var container = $("#analytics_template").clone();
	
	// Populate the header for this mac instance
	data.mac = TimestampParser.formatMac(data.mac);
	container.children(".beacon").html(data.nickname + " (" + data.mac + ")").attr("mac", data.mac);
	
	var contentContainer = container.children(".beacon_content");
	
	// Get ready to populate the raw data for this timestamp
	var summaryData = contentContainer.children(".beacon_subcontent.summary_data:first");
	var parsedData 	= contentContainer.children(".beacon_subcontent.parsed_data:first");
	var rawData 	= contentContainer.children(".beacon_subcontent.raw_data:first").hide();
	
	// Convert mysql timestamps to moments (and populate raw data)
	for(i in data.timestamps){
		data.timestamps[i] = TimestampParser.formatTimestamp(data.timestamps[i])
		rawData.append("<div>" + data.timestamps[i].format('llll') + "</div>");
	}
	
	// Populate the parsed data
	var currentDate = moment(new Date());
	var addedDate = moment(new Date());
	var outTime = moment(new Date());
	for(var i = 0; i < data.timestamps.length/2; i++){
		var clockDiv = $("#log_template").children(":eq(0)").clone();
			clockDiv.children(":eq(0)").html(data.timestamps[i*2].format('llll'));
		
		if(i*2 != data.timestamps.length-1){	
			var diff = moment.momentDiff(data.timestamps[i*2], data.timestamps[i*2+1]);
			var timestamp = diff.hours + ":" + diff.minutes + ":" + diff.seconds;
			clockDiv.children(":eq(1)").html(data.timestamps[i*2+1].format('llll'));
			addedDate.add(moment.duration(diff));
			
		} else {
			var diff = moment.momentDiff(data.timestamps[i*2], currentDate);
			var timestamp = (diff.hours < 0) ? "-" : diff.hours + ":" + diff.minutes + ":" + diff.seconds;
			clockDiv.children(":eq(1)").html("-");
		}
		
		clockDiv.children(":eq(2)").html(timestamp);
		parsedData.append(clockDiv);
	}
	
	var totalTime = moment.momentDiff(currentDate, addedDate);
	var diffTime = moment.momentDiff( data.timestamps[0], data.timestamps[data.timestamps.length-1] );
	outTime.add(moment.duration(diffTime));
	outTime = moment.momentDiff(addedDate,outTime);
		
	summaryData.children(".hours_spent:first").children(".inline:first").html(totalTime.hours + ":" + totalTime.minutes + ":" + totalTime.seconds);
	summaryData.children(".hours_out:first").children(".inline:first").html(outTime.hours + ":" + outTime.minutes + ":" + outTime.seconds);

	return container;
};

moment.momentDiff = function(m1, m2){
	var diff = m2.diff(m1);		
	var duration = moment.duration(diff);

	return {"hours": Math.floor(duration.asHours()),
		    "minutes": moment.utc(diff).format("mm"),
			"seconds": moment.utc(diff).format("ss"),
			"raw": (moment.utc(diff))};
};