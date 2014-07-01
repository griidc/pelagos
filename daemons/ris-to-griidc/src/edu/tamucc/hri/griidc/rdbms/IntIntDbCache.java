package edu.tamucc.hri.griidc.rdbms;

import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.utils.IntIntCache;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public abstract class IntIntDbCache extends IntIntCache {
	private RdbmsConnection dbCon = null;

	private String tableName = null;
	private String keyColName = null;
	private String valueColName = null;
	
	private  boolean deBug = false;
	public IntIntDbCache() {
		// TODO Auto-generated constructor stub
	}
	
	
	/**
	 * @param dbCon
	 * @param tableName
	 * @param keyColName
	 * @param valueColName
	 */
	public IntIntDbCache(RdbmsConnection dbCon, String tableName,
			String keyColName, String valueColName) {
		super();
		this.dbCon = dbCon;
		this.tableName = tableName;
		this.keyColName = keyColName;
		this.valueColName = valueColName;
		buildCacheFromDb();
	}
	

	private void buildCacheFromDb()  {

		if (this.size() == 0) {

			try {
				ResultSet results = this.dbCon.selectAllValuesFromTable(tableName);
				while (results.next()) {
					int value = results.getInt(valueColName);
					int key = results.getInt(keyColName);
					this.cacheValue(key, value);
				}
			} catch (SQLException e) {
				MiscUtils.fatalError("IntStringDbCache", "buildCacheFromDb", e.getMessage());
			} catch (TableNotInDatabaseException e) {
				MiscUtils.fatalError("IntStringDbCache", "buildCacheFromDb", "TableNotInDatabaseException: " + e.getMessage());
			}
		}
	}

}
