package com.cykon.bluproximity;

import android.app.Activity;
import android.bluetooth.BluetoothAdapter;
import android.content.Context;
import android.graphics.Color;
import android.os.Bundle;
import android.os.Environment;
import android.os.RemoteException;
import android.provider.ContactsContract;
import android.util.DisplayMetrics;
import android.view.Menu;
import android.view.MenuItem;
import android.webkit.JavascriptInterface;
import android.webkit.WebView;
import android.widget.RelativeLayout;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;

import org.altbeacon.beacon.Beacon;
import org.altbeacon.beacon.BeaconConsumer;
import org.altbeacon.beacon.BeaconManager;
import org.altbeacon.beacon.BeaconParser;
import org.altbeacon.beacon.RangeNotifier;
import org.altbeacon.beacon.Region;
import org.altbeacon.beacon.service.RangedBeacon;
import java.io.File;

import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.net.URL;
import java.util.Collection;
import java.io.BufferedReader;
import java.io.InputStreamReader;


public class BluProximity extends Activity implements BeaconConsumer {
    private WebView webView;
    private BeaconManager beaconManager;

    /***
     * These variables will control some bluetooth scanning params
     * These are the times in miliseconds which the phone will look for updated
     * beacon information. The faster it is, the more battery it uses.
     *
     * Foreground = when the app is in the foreground (directly open)
     * Background = when the app is closed (not visible)
     * SCAN = the period of time it will scan for siginals
     * SCAN_PAUSE = an additional amount of pausing time between scans
     */

    private static int BACKGROUND_SCAN_MS = 250;
    private static int BACKGROUND_SCAN_PAUSE_MS = 50;
    private static int FOREGROUND_SCAN_MS = 250;
    private static int FOREGROUND_SCAN_PAUSE_MS = 50;
    private SharedPreferences pref;
    /***
     * Method called by the bluetooth detection library, creates a BDevice for each in range device
     */
    @Override
    public void onBeaconServiceConnect() {
        beaconManager.setRangeNotifier(new RangeNotifier() {
            public void didRangeBeaconsInRegion(Collection<Beacon> beacons, Region region) {
                if (beacons.size() > 0) {
                    for (Beacon beacon : beacons)
                        webView.post(new BDevice(beacon.getBluetoothAddress(), beacon.getRssi(), beacon.getDistance()));
                }
            };
        });

        try {
            beaconManager.startRangingBeaconsInRegion(new Region("", null, null, null));
        } catch (RemoteException e) {}
    }

    @JavascriptInterface
    public void println(String text) {
        System.out.println(text);
    }

    /***
     * Method called from javascript, lets us know that we are ready to start
     * scanning for new bluetooth devices. Sets up the bluetooth scanning library.
     */
    @JavascriptInterface
    public void pageLoaded() {
        System.out.println("Init bluetooth libs...");
        webView.post(new Runnable() {
            public void run() {
                webView.loadUrl("javascript:setDeviceMac('" + BluetoothAdapter.getDefaultAdapter().getAddress() + "')");
            }
        });

        beaconManager = BeaconManager.getInstanceForApplication(this);
        beaconManager.setForegroundBetweenScanPeriod(FOREGROUND_SCAN_PAUSE_MS);
        beaconManager.setForegroundScanPeriod(FOREGROUND_SCAN_MS);
        beaconManager.setBackgroundBetweenScanPeriod(BACKGROUND_SCAN_PAUSE_MS);
        beaconManager.setBackgroundBetweenScanPeriod(BACKGROUND_SCAN_MS);
        beaconManager.getBeaconParsers().add(new BeaconParser().
                setBeaconLayout("m:0-3=4c000215,i:4-19,i:20-21,i:22-23,p:24-24"));
        beaconManager.bind(this);
    }

    @JavascriptInterface
    public int getRSSIThreshold(){
        try {
            File file = new File(getFilesDir(), "bludata.dat");

            if(!file.exists()){
                System.out.println("making file...");
                setRSSIThreshold(42);
                return -42;
            }

            FileInputStream fis = new FileInputStream(file);
            int value = fis.read();
            System.out.println("Value Read: " + value);
            fis.close();
            return -value;
        } catch (Exception err) {
            err.printStackTrace();
        }

        return -1;
    }

    @JavascriptInterface
    public void setRSSIThreshold(int rssi){
        if(rssi < 0)
            rssi *= -1;
        System.out.println("Setting: " + rssi);
        try {
            File file = new File(getFilesDir(), "bludata.dat");
            file.getParentFile().mkdirs();
            FileOutputStream fos = new FileOutputStream(file);
            fos.write(rssi);
            fos.close();
            getRSSIThreshold();
        } catch(Exception err){
            err.printStackTrace();
        }
    }

    @Override
    public void onDestroy() {
        if(beaconManager != null) {
            beaconManager.unbind(this);
        }

        super.onDestroy();
    }
    /***
     * Method called from javascript, used as an alternative to AJAX so we can hit a webserver
     * under any domain, letting it know that a device is in range.
     * @param str_url
     */
    @JavascriptInterface
    public void bluDetected(String str_url){
        try {
            System.out.println("URL: " + str_url);
            InputStreamReader in =  new InputStreamReader( new URL(str_url).openStream() );
            while(in.read() != -1);
            in.close();
        }
        catch(Exception err) {
            System.err.println("Could not post proximity to web page.");
            err.printStackTrace();
        }
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        pref = getApplicationContext().getSharedPreferences("myPrefs",0);
        displayLayout();
    }


    private void displayLayout() {
        // Instantiate new webview and enable javascript
        webView = new WebView(this);
        webView.getSettings().setJavaScriptEnabled(true);

        // Load the webapp
        webView.loadUrl("file:///android_asset/web/index.html");

        // Set the WV and WC clients (these are used largely for debugging)
        webView.setWebChromeClient(new CustomCClient(this));
        webView.setWebViewClient(new CustomWVClient());


        // Hook this class up as a javascript interface, this lets us run java code from javascript
        webView.addJavascriptInterface(this, "Android");

        // Set the background color of the app
        webView.setBackgroundColor(Color.WHITE);

        // Get screen metrics to set our layout size
        DisplayMetrics metrics = this.getResources().getDisplayMetrics();

        // New layout, add the webview and set it's render size to the same of our screen
        RelativeLayout layout = new RelativeLayout(this);
        layout.addView(webView, metrics.widthPixels, metrics.heightPixels);

        // Set the current view to be layout
        setContentView(layout);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        //getMenuInflater().inflate(R.menu.blu_proximity, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();
        if (id == R.id.action_settings) {
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    // This runnable class is in charge of sending bluetooth data to the javascript code
    // It is needed because we need to .post() to the webkit thread, can't directly call .loadUrl()
    public class BDevice implements Runnable {
        private String mac;         // Mac of the device
        private int rssi;           // Rssi of the device
        private double distance;    // Calculated 'distance' of the device

        // Constructor
        public BDevice(String mac, int rssi, double distance) {
            this.mac = mac;
            this.rssi = rssi;
            this.distance = distance;
        }

        public void run(){
            // Send data to the javascript function 'deviceInRange()'
            webView.loadUrl("javascript:deviceInRange('" + mac + "','" + rssi + "','" + distance + "')");
        }
    }
}