package edu.tamucc.hri.rdbms.utils;

import java.io.FileNotFoundException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;
import java.util.TreeMap;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.MiscUtils;

public class IntStringDbCache {

	// cache a value from the database
	private Map<Integer, String> cacheMap = Collections
			.synchronizedMap(new TreeMap<Integer, String>());

	private RdbmsConnection dbCon = null;

	private String tableName = null;
	private String keyColName = null;
	private String valueColName = null;
	private boolean squeeze = false;
	
	private  boolean deBug = false;

	/**
	 * @param dbCon
	 * @param tableName
	 * @param keyColName
	 * @param valueColName
	 */
	public IntStringDbCache(RdbmsConnection dbCon, String tableName,
			String keyColName, String valueColName) {
		this(dbCon,tableName,keyColName,valueColName,false);
	}
	
	/**
	 * @param dbCon
	 * @param tableName
	 * @param keyColName
	 * @param valueColName
	 */
	public IntStringDbCache(RdbmsConnection dbCon, String tableName,
			String keyColName, String valueColName, boolean compressValueName) {
		super();
		this.dbCon = dbCon;
		this.tableName = tableName;
		this.keyColName = keyColName;
		this.valueColName = valueColName;
		this.squeeze = compressValueName;
	}

	/**
	 * cache a value with a key
	 * 
	 * @param key
	 * @param value
	 */
	private String cacheValue(int key, String value) {
		String sv = value;
		if (this.squeeze)
			sv = MiscUtils.squeeze(value);
		String oldV = this.cacheMap.put(key, sv);
        if(this.isDeBug() ) System.err.println("IntStringDbCache:cacheValue() added " + key + "-" + sv);
		return oldV;
	}

	/**
	 * given the key return the value
	 * 
	 * @param key
	 * @return
	 */

	public String getValue(int key) throws NoRecordFoundException {
		String name = this.cacheMap.get(key);
		if (name == null)
			throw new NoRecordFoundException("No " + keyColName + " in table: " + this.tableName + " found with value: " + key);
		return name;
	}

	public void buildCacheFromDb()  {

		if (this.cacheMap.size() == 0) {

			try {
				ResultSet results = this.dbCon.selectAllValuesFromTable(tableName);
				while (results.next()) {
					String value = results.getString(valueColName);
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

	public String getTableName() {
		return tableName;
	}

	public String getKeyColName() {
		return keyColName;
	}

	public String getValueColName() {
		return valueColName;
	}

	public int size() {
		return this.cacheMap.size();
	}

	public boolean isDeBug() {
		return this.deBug;
	}

	public void setDeBug(boolean db) {
		this.deBug = db;
	}
	
}
