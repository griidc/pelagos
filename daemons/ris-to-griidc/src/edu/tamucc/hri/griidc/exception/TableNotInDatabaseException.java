package edu.tamucc.hri.griidc.exception;

public class TableNotInDatabaseException  extends Exception {

	private static final long serialVersionUID = -8771678680051379078L;
	public TableNotInDatabaseException() {
		super();
	}
	public TableNotInDatabaseException(String msg) {
		super(msg);
	}
}
