package edu.tamucc.hri.rdbms.utils;

import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

public class TableColInfo implements Comparable {

	private String tableName = null;
	private SortedSet<DbColumnInfo> columnInfoSet = Collections
			.synchronizedSortedSet(new TreeSet<DbColumnInfo>());

	/**
	 * @param tableName
	 */
	public TableColInfo(String tableName) {
		super();
		this.tableName = tableName;
	}

	public DbColumnInfo addDbColumnInfo(DbColumnInfo dci) {
		this.columnInfoSet.add(dci);
		return dci;
	}
	public DbColumnInfo addDbColumnInfo(String colName, String colType,String colValue,
			DefaultValue defaultValue) {
		return addDbColumnInfo(new DbColumnInfo(colName, colType,colValue, defaultValue));
	}
	public DbColumnInfo addDbColumnInfo(String colName, String colType,String colValue) {
		return addDbColumnInfo(new DbColumnInfo(colName, colType,colValue));
	}
	public DbColumnInfo addDbColumnInfo(String colName, String colType,
			DefaultValue defaultValue) {
		return addDbColumnInfo(new DbColumnInfo(colName, colType, defaultValue));
	}

	/**
	 * find the object that matches colName and return it.
	 * If not found return null;
	 * @param colName
	 * @param colType
	 * @return
	 */
	public DbColumnInfo getDbColumnInfo(String colName) {

		DbColumnInfo temp = null;
		Iterator<DbColumnInfo> it = this.getColumnInfoIterator();
		while (it.hasNext()) {
			temp = it.next();
			if (colName.trim().equals(temp.getColName())) {
				return temp;
			}
		}
		return null;
	}
	/**
	 * find the object that matches colName and colType and return it.
	 * If not found return null;
	 * @param colName
	 * @param colType
	 * @return
	 */
	public DbColumnInfo getDbColumnInfo(String colName, String colType) {

		DbColumnInfo temp = null;
		DbColumnInfo dci = new DbColumnInfo(colName, colType);
		Iterator<DbColumnInfo> it = this.getColumnInfoIterator();
		while (it.hasNext()) {
			temp = it.next();
			if (dci.equals(temp)) {
				return temp;
			}
		}
		return null;
	}

	/**
	 * return the number of DbColumnInfo entries in the collection
	 * @return
	 */
	public int size() {
		return this.columnInfoSet.size();
	}
	public String[] getColumnNames() {
		String[] v = new String[this.size()];
		DbColumnInfo temp = null;
		Iterator<DbColumnInfo> it = this.getColumnInfoIterator();
		int ndx = 0;
		while (it.hasNext()) {
			temp = it.next();
			v[ndx++] = temp.getColName();
		}
		return v;
	}
	public String[] getColumnTypes() {
		String[] v = new String[this.size()];
		DbColumnInfo temp = null;
		Iterator<DbColumnInfo> it = this.getColumnInfoIterator();
		int ndx = 0;
		while (it.hasNext()) {
			temp = it.next();
			v[ndx++] = temp.getColType();
		}
		return v;
	}
	public String[] getColumnValues() {
		String[] v = new String[this.size()];
		DbColumnInfo temp = null;
		Iterator<DbColumnInfo> it = this.getColumnInfoIterator();
		int ndx = 0;
		while (it.hasNext()) {
			temp = it.next();
			v[ndx++] = temp.getColValue();
		}
		return v;
	}
	public String[] getColumnDefaultValues() {
		String[] v = new String[this.size()];
		DbColumnInfo temp = null;
		Iterator<DbColumnInfo> it = this.getColumnInfoIterator();
		int ndx = 0;
		while (it.hasNext()) {
			temp = it.next();
			v[ndx++] = temp.getDefaultValue().toString();
		}
		return v;
	}
	public Iterator<DbColumnInfo> getColumnInfoIterator() {
		return this.columnInfoSet.iterator();
	}

	public boolean containsDefaultValueColumn() {
		DbColumnInfo temp = null;
		Iterator<DbColumnInfo> it = getColumnInfoIterator();
		while (it.hasNext()) {
			temp = it.next();
			if (temp.isDefaultValueColumn()) {
				return true;
			}
		}
		return false;
	}
	/**
	 * make another instance of the same type that 
	 * contains a subset of column descriptions in which
	 * the default values are non null;
	 * If this object (TableColInfo) does not have any
	 * columns with default values return null;
	 * @return
	 */
	public TableColInfo getDefaultValueTableColInfo() {
		if(this.containsDefaultValueColumn()) {
			TableColInfo newTableColInfo = new TableColInfo(this.tableName);
			DbColumnInfo thisDbColInfo = null;
		
			Iterator<DbColumnInfo> it = this.getColumnInfoIterator();
			while (it.hasNext()) {
				thisDbColInfo = it.next();
				if (thisDbColInfo.isDefaultValueColumn()) {
					newTableColInfo.addDbColumnInfo(thisDbColInfo.getColName(),thisDbColInfo.getColType(),thisDbColInfo.getDefaultValue());
				}
			}
			return newTableColInfo;
		}
		return null;
	}

	public String getTableName() {
		return tableName;
	}

	public SortedSet<DbColumnInfo> getColumnInfoSet() {
		return columnInfoSet;
	}

	@Override
	public String toString() {
		StringBuffer sb = new StringBuffer("TableColInfo [tableName: "
				+ tableName + ", columnInfoSet:\n");
		Iterator<DbColumnInfo> it = this.getColumnInfoIterator();
		while (it.hasNext()) {
			DbColumnInfo colInfo = it.next();
			sb.append("\t" + colInfo.toString() + "\n");
		}
		return sb.toString();
	}

	@Override
	public int hashCode() {
		final int prime = 31;
		int result = 1;
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
		TableColInfo other = (TableColInfo) obj;
		if (tableName == null) {
			if (other.tableName != null)
				return false;
		} else if (!tableName.equals(other.tableName))
			return false;
		return true;
	}

	@Override
	public int compareTo(Object obj) {
		TableColInfo other = (TableColInfo) obj;
		return (this.tableName.compareTo(other.tableName));
	}

}
