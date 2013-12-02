package edu.tamucc.hri.griidc.mapping.intake;

import java.util.Comparator;

public abstract class MappingStructure implements Comparable<MappingStructure>  {

	private String tableName = null;
	private String columnName = null;
	
	public MappingStructure() {
		super();
	}
	public MappingStructure(String tableName, String columnName) {
		super();
		this.setTableName(tableName);
		this.setColumnName(columnName);
	}
	public String getTableName() {
		return tableName;
	}
	public void setTableName(String tableName) {
		this.tableName = tableName.trim();
	}
	public String getColumnName() {
		return columnName;
	}
	public void setColumnName(String columnName) {
		this.columnName = columnName.trim();
	}
	
	public int compareTo(MappingStructure other) {
		int r = this.tableName.compareTo(other.tableName);
		if(r != 0)
			return r;
	    return this.columnName.compareTo(other.columnName);
	}
	
	public abstract boolean isTarget();
	public abstract boolean isSource();
	@Override
	public String toString() {
		String tors = "UNKNOWN";
		if(this.isSource()) tors = "SOURCE";
		else if(this.isTarget()) tors = "TARGET";
		return tors + " [tableName=" + tableName + ", columnName="
				+ columnName  + "]";
	}
	@Override
	public int hashCode() {
		final int prime = 31;
		int result = 1;
		result = prime * result
				+ ((columnName == null) ? 0 : columnName.hashCode());
		result = prime * result
				+ ((tableName == null) ? 0 : tableName.hashCode());
		return result;
	}
	@Override
	public boolean equals(Object obj) {
		if (this == obj)
			return true;
		if (obj == null)
			return false;
		if (getClass() != obj.getClass())
			return false;
		MappingStructure other = (MappingStructure) obj;
		if (columnName == null) {
			if (other.columnName != null)
				return false;
		} else if (!columnName.equals(other.columnName))
			return false;
		if (tableName == null) {
			if (other.tableName != null)
				return false;
		} else if (!tableName.equals(other.tableName))
			return false;
		return true;
	}
	
	
}
