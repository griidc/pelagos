package edu.tamucc.hri.griidc.support;

import java.util.Random;

public class RandomBoolean {
	private boolean randomUpdate = true;
	private Random rnd = new Random();
	private static RandomBoolean rbInstance = null;
	
	private RandomBoolean() {

	}
    public static RandomBoolean getInstance() {
    	if(rbInstance == null) {
    		rbInstance =  new RandomBoolean();
    	}
    	return rbInstance;
    }
	/**
	 * if randomUpdate is turned on (true)
	 * return a true or false at random.
	 * else return true;
	 * @return
	 */
	public boolean getRandomBoolean() {
		if(isOn()) {
		   return rnd.nextBoolean();
		}
		return true;
	}

	/**
	 * will return true is random update is turned on
	 * @return
	 */
	public boolean isOn() {
		return isRandomUpdate();
	}

	public boolean isRandomUpdate() {
		return randomUpdate;
	}

	/**
	 * set the value of random update to on (true) or off (false)
	 * @param randomUpdate
	 */
	public void setRandomUpdate(boolean torf) {
		this.randomUpdate = torf;
	}
	/**
	 * turn on randomUpdate by using setRandomUpdate(true);
	 */
	public void on() {
		this.setRandomUpdate(true);
	}
	/**
	 * turn off randomUpdate by using setRandomUpdate(false);
	 */
	public void off() {
		this.setRandomUpdate(false);
	}
	
	public static void main(String[] args) {
		
		int trues = 0;
		int falses = 0;
		int max = 1000;
		RandomBoolean rb = new RandomBoolean();
		rb.on();
		for(int i = 0 ; i < max; i++) {
			if(rb.getRandomBoolean()) trues++;
			else falses++;
		}
		String pFormat = "%-10s %10d%n";
		String titleFormat = "%n*************  %-40s  *************%n";
		String title = "RandomBoolean";

		
		System.out.printf(titleFormat,title);
		
		System.out.printf(pFormat, "true count:",trues);
		System.out.printf(pFormat, "false count:",falses);
		
	}
}
