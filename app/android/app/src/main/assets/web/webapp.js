
/* DOM object reference for quick parsing */
var DOM = {};

/* Options variables */
var SAMP = 450;
var OSR = 100;
var CSR = 475;
var MAC = "FF:FF:FF:FF:FF:FF";

window.onload = function() {
    // Set up global option button references		
    DOM.optionsButton = document.getElementById("optionsButton");
    DOM.noDevices = document.getElementById("noDevicesFound");

	// Threshold button
    DOM.THR_T = document.getElementById("THR_T");
    DOM.THR_D = document.getElementById("THR_D");
    DOM.THR_I = document.getElementById("THR_I");

	// Sample Button
    // DOM.SAMP_T = document.getElementById("SAMP_T");
    // DOM.SAMP_D = document.getElementById("SAMP_D");
    // DOM.SAMP_I = document.getElementById("SAMP_I");

	// Open Scan rate Button
    DOM.OSR_T = document.getElementById("OSR_T");
    // DOM.OSR_D = document.getElementById("OSR_D");
    // DOM.OSR_I = document.getElementById("OSR_I");

	// Closed scan rate button
    DOM.CSR_T = document.getElementById("CSR_T");
    // DOM.CSR_D = document.getElementById("CSR_D");
    // DOM.CSR_I = document.getElementById("CSR_I");

	// Simple options buttons.
    // DOM.menuADD = document.getElementById("menuADD");
    // DOM.menuREMOVE = document.getElementById("menuREMOVE");
    DOM.container = document.getElementById("content");
    DOM.options = document.getElementById("options");

    // Click hooks
    DOM.optionsButton.onclick = menuToggle;

    DOM.THR_D.onclick = THR_D;
    DOM.THR_I.onclick = THR_I;

    /* DOM.SAMP_D.onclick = SAMP_D;
    DOM.SAMP_I.onclick = SAMP_I;

    DOM.OSR_D.onclick = OSR_D;
    DOM.OSR_I.onclick = OSR_I;

    DOM.CSR_D.onclick = CSR_D;
    DOM.CSR_I.onclick = CSR_I; */

    // DOM.menuADD.onclick = menuADD;
    // DOM.menuREMOVE.onclick = menuREMOVE;

    /* DOM.THR_I.onclick();
    DOM.SAMP_I.onclick();
    DOM.OSR_I.onclick();
    DOM.CSR_I.onclick(); */

	// Tell android our page has been loaded, so we can init the bluetooth stuff
    if (typeof Android != 'undefined'){
        Android.pageLoaded();
		Device.threshold = Android.getRSSIThreshold();
		
		if(Device.threshold == 0)
			Device.threshold = -42;
		
		DOM.THR_T.innerHTML = "Threshold (" + Device.threshold + ")";
	}
};

Device.a_Dev = new Array();

/* Function that toggles the menu */
function menuToggle() {
    var s_options = DOM.options.style;
    var s_obutton = DOM.optionsButton.style;
    var s_content = DOM.container.style;

    if (s_options.display == "" || s_options.display == "none") {
        s_obutton.background = "#1D8EC2";
        s_options.display = "block";
        s_content.display = "none";
    } else {
        s_obutton.background = "inherit";
        s_options.display = "none";
        s_content.display = "block";
    }
}

function THR_I() {
    DOM.THR_T.innerHTML = "Threshold (" + Device.incrementThreshold() + ")";
	if (typeof Android != 'undefined')
		Android.setRSSIThreshold(Device.threshold);
}

function THR_D() {
    DOM.THR_T.innerHTML = "Threshold (" + Device.decrementThreshold() + ")";
	if (typeof Android != 'undefined')
		Android.setRSSIThreshold(Device.threshold);
}

function SAMP_I() {
    DOM.SAMP_T.innerHTML = "Sample Length (" + (SAMP += 50) + "ms)";
}

function SAMP_D() {
    DOM.SAMP_T.innerHTML = "Sample Length (" + (SAMP -= (SAMP == 500) ? 0 : 50) + "ms)";
}

function OSR_I() {
    DOM.OSR_T.innerHTML = "Open Scan Rate (" + (OSR += 25) + "ms)";
}

function OSR_D() {
    DOM.OSR_T.innerHTML = "Open Scan Rate (" + (OSR -= (OSR == 100) ? 0 : 25) + "ms)";
}

function CSR_I() {
    DOM.CSR_T.innerHTML = "Closed Scan Rate (" + (CSR += 25) + "ms)";
}

function CSR_D() {
    DOM.CSR_T.innerHTML = "Closed Scan Rate (" + (CSR -= (CSR == 100) ? 0 : 25) + "ms)";
}

function menuADD() {
    deviceInRange("FF:FF:FF:FF:FF:FF", -999, -1);
    setTimeout(DOM.optionsButton.onclick, 50);

    setTimeout(function() {
        deviceInRange("FF:FF:FF:FF:FF:FF", -999, 999);
    }, 100);
}

function menuREMOVE() {
    Device.removeAllDevices();
    setTimeout(DOM.optionsButton.onclick, 50);
}

// To be called from JAVA
function deviceInRange(mac, rssi, distance) {
    var device = new Device(mac, rssi, distance);
    device.register();
}

// To be called from JAVA
function setDeviceMac(mac) {
    MAC = mac;
    document.getElementById("DEVICE_MAC").innerHTML = "MAC: " + MAC;
}


function Device(mac, rssi, distance) {
    this.mac = mac;
    this.rssi = rssi;
    this.distance = distance;
    this.cooldown = false;
    this.inRange = false;
    this.triggered = 0;
    this.htmlNode;
}

Device.incrementThreshold = function() {
    if (Device.threshold < -1)
		Device.threshold += 1;
    return Device.threshold;
}

Device.decrementThreshold = function() {
    Device.threshold -= 1;
    return Device.threshold;
}

Device.cooldown = 5000;


Device.removeAllDevices = function() {
    while (Device.a_Dev.length > 0) {
        Device.a_Dev[0].unregister();
    }
}

Device.prototype.register = function() {
    for (var i = 0; i < Device.a_Dev.length; i++) {
        if (Device.a_Dev[i].mac === this.mac) {
            Device.a_Dev[i].distanceUpdate(this.rssi, this.distance);
            return false;
        }
    }

    Device.a_Dev.push(this);

    this.htmlNode = document.createElement("div");
    this.htmlNode.className = "beaconItem";
    DOM.container.appendChild(this.htmlNode);
    this.distanceUpdate();

    DOM.noDevices.style.display = "none";

    return true;
};

Device.prototype.unregister = function() {
    for (var i = 0; i < Device.a_Dev.length; i++) {
        if (Device.a_Dev[i].mac === this.mac) {
            DOM.container.removeChild(this.htmlNode);
            Device.a_Dev.splice(i, 1);

            if (Device.a_Dev.length == 0)
                DOM.noDevices.style.display = "block";

            return true;
        }
    }

    return false;
};

Device.prototype.updateHTML = function() {
    this.htmlNode.innerHTML = '<div class="beaconMac">Mac: ' + this.mac + ' (' + this.triggered + ')</div><div class="beaconMac">RSSI: ' + this.rssi + '</div>';
};

Device.prototype.distanceUpdate = function(rssi, dist) {
    dist = parseFloat(dist);
    rssi = parseFloat(rssi);
	this.distance = dist;
    this.rssi = rssi;
    this.updateHTML();

    if (this.cooldown == false) {
        // In range, previously out of range					
        if (this.inRange == false && rssi >= Device.threshold) {
            this.triggered += 1;
            this.inRange = true;
            this.htmlNode.className = "beaconItem inRange";
            this.startCooldown();
        }

        // Out of range, previously in range
        else if (this.inRange == true && rssi < Device.threshold) {
            this.inRange = false;
            this.htmlNode.className = "beaconItem";
        }
    }
};

Device.prototype.startCooldown = function() {
    this.cooldown = true;

    //if (typeof Android != "undefined") {
		//console.log("http://cefns.nau.edu/~mja266/prox/server/interact.php?source=" + MAC.split(":").join("") + "&beacon=" + this.mac.split(":").join(""));
        //Android.bluDetected("http://cefns.nau.edu/~mja266/prox/server/interact.php?source=" + MAC.split(":").join("") + "&beacon=" + this.mac.split(":").join(""));
        Android.bluDetected("https://www.cefns.nau.edu/Projects/blueproximity/interact.php?source=" + MAC.split(":").join("") + "&beacon=" + this.mac.split(":").join(""));

    //}

    var thisRef = this;
    setTimeout(function() {
        thisRef.finishCooldown();
    }, Device.cooldown);
};

Device.prototype.finishCooldown = function() {
    this.cooldown = false;
};