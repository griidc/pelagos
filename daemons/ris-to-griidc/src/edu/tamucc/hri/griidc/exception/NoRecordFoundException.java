package edu.tamucc.hri.griidc.exception;

public class NoRecordFoundException extends Exception {

	/**
	 * 
	 */
	private static final long serialVersionUID = -3184758855281733646L;
	private String recordName = null;
	public NoRecordFoundException() {
		// TODO Auto-generated constructor stub
	}

	public NoRecordFoundException(String arg0) {
		super(arg0);
		// TODO Auto-generated constructor stub
	}

	public String getRecordName() {
		return recordName;
	}

	public void setRecordName(String recordName) {
		this.recordName = recordName;
	}
    
}
