package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

public class CountryTableCache {
	private static final String TableName = "Country";
	private static final String CountryNumberCol = "Country_Number";
	private static final String CallingCodeCol = "Country_CallingCode";
	private static final String CountryNameCol = "Country_Name";
	private static final String ISO3166Code2Col = "Country_ISO3166Code2";
	private static final String ISO3166Code3Col = "Country_ISO3166Code3";

	private static boolean DeBug = false;

	private SortedSet<CountryTableStruct> countryCache = null;

	private static CountryTableCache instance = null;

	private CountryTableCache() {

	}

	public static CountryTableCache getInstance() {
		if (CountryTableCache.instance == null) {
			CountryTableCache.instance = new CountryTableCache();
		}
		return CountryTableCache.instance;
	}

	/**
	 * lazy instantiation of the cache.
	 * @return
	 */
	private SortedSet<CountryTableStruct> getCountryCacheInstance() {
		if (this.countryCache == null)
			loadCacheFromDatabase();
		return this.countryCache;
	}

	/**
	 * read all the country codes records from the GRIIDC database and cache
	 * them here
	 */
	private void loadCacheFromDatabase() {
		this.countryCache = Collections
				.synchronizedSortedSet(new TreeSet<CountryTableStruct>());
		int countryNumber = -1; // the table key
		String callingCode = null;
		String isO3166Code2 = null;
		String isO3166Code3 = null;
		String countryName = null;
		CountryTableStruct countryTemp = null;
		String q = "SELECT * FROM "
				+ RdbmsConnection.wrapInDoubleQuotes(TableName);

		try {
			ResultSet rs = RdbmsUtils.getGriidcDbConnectionInstance()
					.executeQueryResultSet(q);
			while (rs.next()) {
				countryNumber = rs.getInt(CountryNumberCol);
				callingCode = rs.getString(CallingCodeCol);
				isO3166Code2 = rs.getString(ISO3166Code2Col);
				isO3166Code3 = rs.getString(ISO3166Code3Col);
				countryName = rs.getString(CountryNameCol);
				countryTemp = new CountryTableStruct(countryNumber,
						callingCode, isO3166Code2, isO3166Code3, countryName);
				countryCache.add(countryTemp);
			}
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

	}

	public CountryTableStruct findCountry(int countryNumberKey) {
		Iterator<CountryTableStruct> it = getCountryCacheInstance().iterator();
		CountryTableStruct temp = null;
		while (it.hasNext()) {
			temp = it.next();
			if (temp.getNumber() == countryNumberKey) {
				return temp;
			}
		}
		return null;
	}

	public boolean doesCountryExist(int countryNumberKey) {
		CountryTableStruct cts = findCountry(countryNumberKey);
		if (cts == null)
			return false;
		return true;
	}

	public Iterator<CountryTableStruct> iterator() {
		return getCountryCacheInstance().iterator();
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	public class CountryTableStruct implements Comparable<CountryTableStruct> {

		private int number = -1; // the table key
		private String callingCode = null;
		private String ISO3166Code2 = null;
		private String ISO3166Code3 = null;
		private String name = null;

		/**
		 * @param number
		 * @param callingCode
		 * @param iSO3166Code2
		 * @param iSO3166Code3
		 * @param name
		 */
		public CountryTableStruct(int number, String callingCode,
				String iSO3166Code2, String iSO3166Code3, String name) {
			super();
			this.number = number;
			this.callingCode = callingCode;
			ISO3166Code2 = iSO3166Code2;
			ISO3166Code3 = iSO3166Code3;
			this.name = name;
		}

		public String getCallingCode() {
			return callingCode;
		}

		public void setCallingCode(String callingCode) {
			this.callingCode = callingCode;
		}

		public String getISO3166Code2() {
			return ISO3166Code2;
		}

		public void setISO3166Code2(String iSO3166Code2) {
			ISO3166Code2 = iSO3166Code2;
		}

		public String getISO3166Code3() {
			return ISO3166Code3;
		}

		public void setISO3166Code3(String iSO3166Code3) {
			ISO3166Code3 = iSO3166Code3;
		}

		public String getName() {
			return name;
		}

		public void setName(String name) {
			this.name = name;
		}

		public int getNumber() {
			return number;
		}

		@Override
		public String toString() {
			return "CountryTableStruct [number=" + number + ", callingCode="
					+ callingCode + ", ISO3166Code2=" + ISO3166Code2
					+ ", ISO3166Code3=" + ISO3166Code3 + ", name=" + name + "]";
		}

		@Override
		public int compareTo(CountryTableStruct ref) {
			return (this.number - ref.number);
		}

		@Override
		public int hashCode() {
			final int prime = 31;
			int result = 1;
			result = prime * result + getOuterType().hashCode();
			result = prime * result + number;
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
			CountryTableStruct other = (CountryTableStruct) obj;
			if (!getOuterType().equals(other.getOuterType()))
				return false;
			if (number != other.number)
				return false;
			return true;
		}

		private CountryTableCache getOuterType() {
			return CountryTableCache.this;
		}

	}

	public static void main(String[] args) {
		CountryTableCache ctc = CountryTableCache.getInstance();
		Iterator<CountryTableStruct> it = ctc.iterator();
		while (it.hasNext()) {
			System.out.println(it.next().toString());
		}
		int[] code = { 555, 320, 20, 40, 100, 251, 1, 3000, 253 };
		for (int c : code) {
			boolean exists = CountryTableCache.getInstance()
					.doesCountryExist(c);
			System.out.println("Country with code: " + c
					+ ((exists) ? " Exists " : " Does Not Exist"));
		}
	}
}
