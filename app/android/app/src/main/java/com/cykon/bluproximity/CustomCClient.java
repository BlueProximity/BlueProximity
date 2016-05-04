package com.cykon.bluproximity;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.webkit.ConsoleMessage;
import android.webkit.WebChromeClient;
import android.webkit.WebView;

public class CustomCClient extends WebChromeClient
{
	private Context thisContext;
	
	@Override
	public boolean onConsoleMessage(ConsoleMessage cm) {
		System.out.println(cm.message() + " -- From line "
	                         + cm.lineNumber() + " of "
	                         + cm.sourceId() );
	    return true;
	  }
	
	@Override
	public boolean onJsAlert(WebView view, String url, String message, final android.webkit.JsResult result)   
    {  
        new AlertDialog.Builder(thisContext)  
            .setTitle("JavaScript Alert")  
            .setMessage(message)  
            .setPositiveButton(android.R.string.ok,  
                    new AlertDialog.OnClickListener()   
                    {  
                        public void onClick(DialogInterface dialog, int which)   
                        {  
                            result.confirm();  
                        }  
                    })  
            .setCancelable(false)  
            .create()  
            .show();  
          
        return true;  
    };  
    
    public CustomCClient(Context thisContext)
    {
    	this.thisContext = thisContext;
    }
}
