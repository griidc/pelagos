package edu.tamucc.hri.griidc.utils;

import java.text.SimpleDateFormat;

public class GriidcElapsedTimer {

	public static final long NotSet = -1;
	long startTime = NotSet;
	long lastNowTime = NotSet;
	
	public GriidcElapsedTimer() {
		this.start();
	}
	
	public long start() {
		return this.lastNowTime = this.startTime = System.currentTimeMillis();
	}

	public long getElapsedTime() {
		return Math.abs(this.now() - this.startTime);
	}
	
	public long now() {
		return this.lastNowTime = System.currentTimeMillis();
	}
	
	public  String getFormatedElapsedTime()  {
		return GriidcElapsedTimer.formatTimeMinSecMilSec(getElapsedTime());
	}
		
	public static String formatTimeMinSecMilSec(long t) {
		return new SimpleDateFormat("mm:ss:SSS").format(t);
	}
}
