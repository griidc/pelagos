package edu.tamucc.hri.griidc.exception;

public class PropertyNotFoundException extends Exception {

	/**
	 * An exception to be thrown when the property identifying string that the Properties class (RisPropertiesAccess)
	 * trys to load is not found.
	 */
	private static final long serialVersionUID = 2835821467064241116L;
	
	public  PropertyNotFoundException(String msg) {
		super(msg);
	}

}
