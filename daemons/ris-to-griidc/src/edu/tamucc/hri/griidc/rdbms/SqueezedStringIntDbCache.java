package edu.tamucc.hri.griidc.rdbms;

import java.io.FileNotFoundException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Set;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.utils.CompressedString;

public class SqueezedStringIntDbCache {

	// cache a value from the database
	private Map<String, Integer> cacheMap = Collections
			.synchronizedMap(new HashMap<String, Integer>());

	private RdbmsConnection dbCon = null;

	private String tableName = null;
	private String keyColName = null;
	private String valueColName = null;

	private boolean changedSincedArray = false;
	private String[] allKeys = null;
	private static boolean Debug = false;

	/**
	 * @param dbCon
	 * @param tableName
	 * @param keyColName
	 * @param valueColName
	 */
	public SqueezedStringIntDbCache(RdbmsConnection dbCon, String tableName,
			String keyColName, String valueColName) {
		super();
		this.dbCon = dbCon;
		this.tableName = tableName;
		this.keyColName = keyColName;
		this.valueColName = valueColName;
	}

	/**
	 * cache a value with a key
	 * 
	 * @param key
	 * @param value
	 */
	private Integer cacheValue(String unsqueezedKey, Integer value) {
		CompressedString keyS = new CompressedString(unsqueezedKey);
		Integer oldV = this.cacheMap.put(keyS.getCompressedString(), value);
		changedSincedArray = true;
		if (isDebug()) {
			System.out.println("\n\tsize: " + this.cacheMap.size()
					+ ", cached: " + unsqueezedKey + " : " + value
					+ " - squeezed: " + keyS.getCompressedString());
			String prefix = this.cacheMap.containsKey(keyS.getCompressedString()) ? "Does contain key "
					: "Does NOT contain key ";
			System.out.println(" -> " + prefix + " - "
					+ keyS.getCompressedString() + "<-");
		}
		return oldV;
	}

	/**
	 * given the key return the value
	 * 
	 * @param key
	 * @return
	 */

	public Integer getValue(String unsqueezedKey) throws NoRecordFoundException {
		CompressedString ss = new CompressedString(unsqueezedKey);
		if (isDebug()) {
			System.out.println("\ngetValue() DbCache: "
					+ this.getCacheDescription());
			System.out.println("\tsize: " + this.cacheMap.size()
					+ ", looking for " + ss);
			String prefix = this.cacheMap.containsKey(ss.getCompressedString()) ? "Does contain key "
					: "Does NOT contain key ";
			System.out.println(" => " + prefix + " - " + ss.getCompressedString()
					+ "<=");
		}
		Integer id = this.cacheMap.get(ss.getCompressedString());
		if (id == null)
			throw new NoRecordFoundException("No value found for key: "
					+ unsqueezedKey + " - " + this.getCacheDescription());
		return id;
	}

	public void buildCacheFromDb() throws FileNotFoundException, SQLException,
			ClassNotFoundException, PropertyNotFoundException, TableNotInDatabaseException {

		if (isDebug())
			System.out.println("\n" + this.getCacheDescription());
		if (this.cacheMap.size() == 0) {

			ResultSet results = this.dbCon.selectAllValuesFromTable(tableName);
			while (results.next()) {
				Integer value = results.getInt(valueColName);
				String key = results.getString(keyColName);
				this.cacheValue(key, value);
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

	public String[] getKeys() {

		if (this.allKeys == null || this.changedSincedArray) {
			this.allKeys = new String[cacheMap.size()];
			Set<String> set = cacheMap.keySet();
			Iterator<String> it = set.iterator();
			int i = 0;
			while (it.hasNext()) {
				String ss = it.next();
				this.allKeys[i++] = ss;
			}
			changedSincedArray = false;
		}
		return this.allKeys;
	}

	public String getCacheDescription() {
		return "Size: " + this.cacheMap.size() + " DB: "
				+ this.dbCon.getDbName() + ", table name: " + this.tableName
				+ ", key col: " + this.keyColName + ", value col: "
				+ this.valueColName;
	}

	public String toString() {
		Set<Entry<String, Integer>> ts = cacheMap.entrySet();
		List<String> list = new ArrayList<String>();

		StringBuffer sb = new StringBuffer(this.getCacheDescription() + "\n");

		Iterator<Entry<String, Integer>> it = ts.iterator();
		while (it.hasNext()) {
			Entry<String, Integer> entry = it.next();
			String ss = entry.getKey();
			list.add(ss);
		}

		Collections.sort(list);
		Iterator<String> listIterator = list.iterator();
		while (listIterator.hasNext()) {
			String s = listIterator.next();
			Integer v = null;
			try {
				v = this.getValue(s);
				sb.append("\n\tkey: " + s + "\tvalue: " + v);
			} catch (NoRecordFoundException e) {
				System.out.println(e.getMessage());
			}
		}
		return sb.toString();
	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
	}

}
