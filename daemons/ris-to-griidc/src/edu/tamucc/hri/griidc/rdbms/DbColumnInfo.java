package edu.tamucc.hri.griidc.rdbms;

public class DbColumnInfo implements Comparable<DbColumnInfo> {

	private String colName = null;
	private String colType = null;
	private String colValue = null;
	private DefaultValue defaultValue = null;
	
	// see RdbmsUtils database types
	
	public static final String NullString = DefaultValue.getNullstring();
	
	public DbColumnInfo() {

	}
	/**
	 * @param colName
	 * @param colType
	 * @param defaultValue
	 */
	public DbColumnInfo(String colName, String colType, String colValue,
			DefaultValue defaultValue) {
		super();
		this.colName = colName;
		this.colType = colType;
		this.colValue = colValue;
		this.defaultValue = defaultValue;
	}
	/**
	 * @param colName
	 * @param colType
	 * @param defaultValue
	 */
	public DbColumnInfo(String colName, String colType,
			DefaultValue defaultValue) {
		this(colName,colType,null,defaultValue);
	}
	
	/**
	 * @param colName
	 * @param colType
	 * @param defaultValue
	 */
	public DbColumnInfo(String colName, String colType,
			String value) {
		this(colName,colType,value,null);
	}
	
	
	/**
	 * @param colName
	 * @param colType
	 * @param defaultValue
	 */
	public DbColumnInfo(String colName, String colType) {
		this(colName,colType,null,null);
	}

	public DbColumnInfo(DbColumnInfo ref) {
		this(ref.colName,ref.colType, ref.colValue, ref.defaultValue);
	}
	public String getColName() {
		return colName;
	}

	public String getColType() {
		return colType;
	}

	public String getColValue() {
		return colValue;
	}
	public DefaultValue getDefaultValue() {
		return defaultValue;
	}
	public void setColValue(int value) {
		this.setColValue(String.valueOf(value));
	}
	public void setColValue(double value) {
		this.setColValue(String.valueOf(value));
	}
	public void setColValue(boolean value) {
		this.setColValue(String.valueOf(value));
	}
	public void setColValue(String value) {
		this.colValue = value;
	}
	public boolean isDefaultValueColumn() {
		if(!this.getDefaultValue().equals(NullString))
			return true;
		return false;
	}
	public static final String formatString = "DbColumnInfo [colName: %-30s colType: %-20s colValue: %-20s default value: %-20s]";
	@Override
	public String toString() {
		return String.format(formatString, this.colName , this.colType , this.colValue, this.defaultValue);
	}

	public static String toString(DbColumnInfo[] dbci) {
		StringBuffer sb = new StringBuffer();
		boolean firstTime = true;
		for(DbColumnInfo dci : dbci) {
			if(!firstTime) {
				sb.append("\n");
			}
			sb.append(dci.toString());
			firstTime = false;
		}
		return sb.toString();
	}
	@Override
	public int hashCode() {
		final int prime = 31;
		int result = 1;
		result = prime * result + ((colName == null) ? 0 : colName.hashCode());
		result = prime * result + ((colType == null) ? 0 : colType.hashCode());
		result = prime * result
				+ ((defaultValue == null) ? 0 : defaultValue.hashCode());
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
		DbColumnInfo other = (DbColumnInfo) obj;
		if (colName == null) {
			if (other.colName != null)
				return false;
		} else if (!colName.equals(other.colName))
			return false;		
		return true;
	}

	@Override
	public int compareTo(DbColumnInfo obj) {
		DbColumnInfo other = obj;
		return (this.colName.compareTo(other.colName));
	}

}
