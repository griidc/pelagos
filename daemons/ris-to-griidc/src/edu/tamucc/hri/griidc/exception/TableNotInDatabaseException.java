package edu.tamucc.hri.griidc.exception;

public class TableNotInDatabaseException  extends Exception {

	public TableNotInDatabaseException() {
		super();
	}
	public TableNotInDatabaseException(String msg) {
		super(msg);
	}
}
