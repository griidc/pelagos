package edu.tamucc.hri.griidc.mapping.specs;

import java.util.Collections;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.exception.TableDescriptionException;

public class TableMappingSpecification {
	
	private String tableName = null;
	private SortedSet<String> columnNames = Collections
			.synchronizedSortedSet(new TreeSet<String>());

	public TableMappingSpecification(String tName) {
		this.tableName = tName;
	}
	
	/**
	 * probably need to validate this against a database.
	 * Is the table name real? Are the columns really in the table?
	 * @return
	 */
	public boolean isValid(final String databaseName) {
		return true;
	}
	
	public boolean addColumn(final String colName) {
		return this.columnNames.add(colName);
	}
	

	/**
	 * Column name must be unique in the collection - NO duplicates
	 * 
	 * @param String
	 * @return true if OK
	 */
	public boolean isAllowable( final String colName) throws  TableDescriptionException {
		this.isUniqueInCollection(colName);
		return true;
	}

	

	private boolean isUniqueInCollection(final String colName)
			throws TableDescriptionException {
		if (this.columnNames.contains(colName)) {
			throw new TableDescriptionException("Duplicates are NOT allowed "
					+ colName);
		}
		return true;
	}

	public String[] getColumnNameArray() {
		String[] colNames = new String[this.columnNames.size()];
		return this.columnNames.toArray(colNames);
	}
	
	public String getTableName() {
		return this.tableName;
	}
}
