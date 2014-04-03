package edu.tamucc.hri.griidc.exception;

public class IniSectionNotFoundException extends Exception {

	public IniSectionNotFoundException() {
		// TODO Auto-generated constructor stub
	}
	/**
	 * An exception to be thrown when the section specified for a property within
	 * and Ini file is not accessible 
	 */
	
	public  IniSectionNotFoundException(String msg) {
		super(msg);
	}

}
