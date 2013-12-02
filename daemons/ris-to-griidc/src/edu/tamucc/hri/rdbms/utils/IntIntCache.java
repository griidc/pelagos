package edu.tamucc.hri.rdbms.utils;


import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;

public class IntIntCache {

	// cache a value from the database
	private Map<Integer, Integer> cacheMap = Collections
			.synchronizedMap(new HashMap<Integer, Integer>());

	/**
	 * @param dbCon
	 * @param tableName
	 * @param keyColName
	 * @param valueColName
	 */
	public IntIntCache() {
		super();
	}

	/**
	 * cache a value with a key
	 * 
	 * @param key
	 * @param value
	 */
	public Integer cacheValue(int key, int value) {
		Integer oldV = this.cacheMap.put(key, value);
		return oldV;
	}

	/**
	 * given the key return the value
	 * 
	 * @param key
	 * @return
	 */

	public int getValue(int key) throws NoRecordFoundException {
		Integer value = this.cacheMap.get(key);
		if (value == null)
			throw new NoRecordFoundException(" Key is " + key);
		return value.intValue();
	}

	public int size() {
		return this.cacheMap.size();
	}
	
}
