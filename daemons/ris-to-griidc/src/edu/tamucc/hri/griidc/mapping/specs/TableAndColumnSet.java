package edu.tamucc.hri.griidc.mapping.specs;

public class TableAndColumnSet {

	private String tableName = null;
	private String[] columnNames = null;

	public TableAndColumnSet(String tName, String[] colNames) {
		this();
	  this.setTableName(tName);
	  this.setColumnNames(colNames);
	}
	
	public TableAndColumnSet() {
		
	}

	public String getTableName() {
		return tableName;
	}

	public String[] getColumnNames() {
		return columnNames;
	}

	public void setTableName(String tableName) {
		this.tableName = tableName;
	}

	public void setColumnNames(String[] columnNames) {
		this.columnNames = columnNames;
	}
	
}
