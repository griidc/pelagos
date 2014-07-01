package edu.tamucc.hri.griidc.utils;

public class ProgressSpinner {

	private static int DefaultIntervalInMillSeconds = 500;
	private int interval = DefaultIntervalInMillSeconds;
	private long nextTime = System.currentTimeMillis() + interval;

	public ProgressSpinner() {
      this(DefaultIntervalInMillSeconds);
	}

	public ProgressSpinner(int intervalInSeconds) {
		this.interval = intervalInSeconds;
		nextTime = System.currentTimeMillis() + interval;
	}

	/**
	 * a "still running indicator". A spinning character representation
	 */
	private static String[] spinnerChars = { "|", "/", "-", "\\" };
	private static int spinnerNdx = 0;
	private static final char BackSpace = 8;

	private void spinOutput() {
		System.out.print(BackSpace);
		System.out.print(BackSpace);
		System.out.print(spinnerChars[spinnerNdx++]);
		System.out.print(' ');
		if (spinnerNdx >= spinnerChars.length)
			spinnerNdx = 0;
	}

	public void spin() {
		long now = System.currentTimeMillis();
		if (now >= this.nextTime) {
			this.nextTime = System.currentTimeMillis() + this.interval;
			this.spinOutput();
		}
		return;
	}


	public String toString() {
		return "ProgressSpinner interval: " + this.interval + ", now: "
				+ System.currentTimeMillis() + ", next: " + this.nextTime;
	}

	public static void main(String[] args) {
		int interval = DefaultIntervalInMillSeconds;
		if (args.length > 0) {
			interval = Integer.valueOf(args[0]);
		}
		System.out.println("ProgressSpinner test - interval of " + interval);
		ProgressSpinner spinner = new ProgressSpinner(interval);
		while (true)
			spinner.spin();
	}
}
