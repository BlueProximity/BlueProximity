package com.cykon.bluproximity;

import android.util.Log;
import android.webkit.WebView;
import android.webkit.WebViewClient;

public class CustomWVClient extends WebViewClient
{
	@Override
    public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
            Log.e("WEB_VIEW_TEST", "error code:" + errorCode);
            super.onReceivedError(view, errorCode, description, failingUrl);
    }
}
