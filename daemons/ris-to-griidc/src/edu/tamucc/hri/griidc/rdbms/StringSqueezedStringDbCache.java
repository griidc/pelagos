package edu.tamucc.hri.griidc.rdbms;

import java.io.FileNotFoundException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;
import java.util.Set;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.utils.CompressedString;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public class StringSqueezedStringDbCache {

	
	// cache a value from the database
		private Map<String, CompressedString> cacheMap = Collections
				.synchronizedMap(new HashMap<String, CompressedString>());

		private RdbmsConnection dbCon = null;

		private String tableName = null;
		private String keyColName = null;
		private String valueColName = null;

		/**
		 * @param dbCon
		 * @param tableName
		 * @param keyColName
		 * @param valueColName
		 */
		public StringSqueezedStringDbCache(RdbmsConnection dbCon, String tableName,
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
		private CompressedString cacheValue(String key, String value) {
			CompressedString sv = new CompressedString(value);
			CompressedString oldV = this.cacheMap.put(key, sv);
			return oldV;
		}

		/**
		 * given the key return the value
		 * 
		 * @param key
		 * @return
		 */

		public CompressedString getValue(String key) throws NoRecordFoundException {
			CompressedString name = this.cacheMap.get(key);
			if (name == null)
				throw new NoRecordFoundException(" Key is " + key);
			return name;
		}

		public void buildCacheFromDb() throws FileNotFoundException, SQLException,
				ClassNotFoundException, PropertyNotFoundException, TableNotInDatabaseException {

			if (this.cacheMap.size() == 0) {

				ResultSet results = this.dbCon.selectAllValuesFromTable(tableName);
				while (results.next()) {
					String value = results.getString(valueColName);
					String key = results.getString(keyColName);
					this.cacheValue(key, MiscUtils.squeeze(value));
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
		
		public String[]  getKeys() {
			Set<String> keys = cacheMap.keySet();
			String[] keyArray = new String[cacheMap.size()];
			keyArray = keys.toArray(keyArray);
			return keyArray;
		}
}
