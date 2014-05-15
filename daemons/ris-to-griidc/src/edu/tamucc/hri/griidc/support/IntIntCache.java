package edu.tamucc.hri.griidc.support;

import java.util.Collections;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.Map.Entry;
import java.util.TreeMap;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;

public abstract class IntIntCache {
	// cache a value from the database
	private Map<Integer, Integer> cacheMap = Collections
			.synchronizedMap(new TreeMap<Integer, Integer>());

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
	 * remove all values. Do not reallocate the IntIntCache object if it exists.
	 */
	public IntIntCache initialize() {
		this.cacheMap.clear();
		return this;
	}

	/**
	 * cache a value with a key
	 * 
	 * @param key
	 * @param value
	 */
	protected Integer cacheValue(int key, int value) {
		Integer oldV = this.cacheMap.put(key, value);
		return oldV;
	}

	/**
	 * given the key return the value
	 * 
	 * @param key
	 * @return
	 */

	public int getValue(int targetKey) throws NoRecordFoundException {
		Integer value = this.cacheMap.get(targetKey);
		if (value == null)
			throwNoValueFoundException(targetKey);
		return value.intValue();
	}

	public abstract void throwNoValueFoundException(int targetKey) throws NoRecordFoundException;
	
	public int size() {
		return this.cacheMap.size();
	}

	/** find a key that corresponds to the value **/
	public int getKey(int targetValue) throws NoRecordFoundException {
		IntegerPair[] ipa = toIntArray();
		int ev = -1;
		for (int i = 0; i < ipa.length; i++) {
			ev = ipa[i].getValue().intValue();
			if (ev == targetValue)
				return ipa[i].getKey().intValue();
		}
		throwNoKeyFoundException(targetValue);
		return -1;
	}
	public abstract void throwNoKeyFoundException(int targetValue) throws NoRecordFoundException;
	
	public String toString() {
		IntegerPair[] ipa = this.toIntArray();
		StringBuffer sb = new StringBuffer();
		boolean firstOne = true;
		for (int i = 0; i < ipa.length; i++) {
			if (!firstOne)
				sb.append(", ");
			firstOne = false;
			sb.append("[" + ipa[i].getKey() + "-" + ipa[i].getValue() + "]");
		}
		return sb.toString();
	}
	public abstract String getReportHeader();
	
	public String columnerToString() {
		IntegerPair[] ip = this.toIntArray();
		StringBuffer sb = new StringBuffer(getReportHeader());
		String format = "%n%4d  %4d";
		for(int i = 0; i < ip.length;i++) {
			sb.append(String.format(format,ip[i].getKey(),ip[i].getValue()));
		}
		return sb.toString();
	}
	public IntegerPair[] toIntArray() {
		IntegerPair[] ipa = new IntegerPair[this.cacheMap.size()];
		Iterator<Entry<Integer, Integer>> it = this.cacheMap.entrySet()
				.iterator();
		Entry<Integer, Integer> e = null;
		int i = 0;
		while (it.hasNext()) {
			e = it.next();
			int k = e.getKey();
			int v = e.getValue();
			ipa[i] = new IntegerPair(k, v);
			i++;
		}
		return ipa;
	}

	public class IntegerPair {
		public Integer key = null;
		public Integer value = null;

		/**
		 * @param key
		 * @param value
		 */
		public IntegerPair(Integer key, Integer value) {
			super();
			this.key = key;
			this.value = value;
		}

		public Integer getKey() {
			return key;
		}

		public void setKey(Integer key) {
			this.key = key;
		}

		public Integer getValue() {
			return value;
		}

		public void setValue(Integer value) {
			this.value = value;
		}
	}
}
