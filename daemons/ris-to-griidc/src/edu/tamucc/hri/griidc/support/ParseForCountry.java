package edu.tamucc.hri.griidc.support;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

/**
 * written to parse a string presuming 2nd line of an address, looking for one
 * of the predefined strings used for coding country names as stored in the
 * GRIIDC Contry table.
 * 
 * @author jvh
 * 
 */
public class ParseForCountry {

	private SortedSet<CountryStructure> countrySet = null;
	private static boolean Debug = false;

	public ParseForCountry() {

	}

	private void loadCountryReferenceData() {

		if (this.countrySet == null) {
			this.countrySet = Collections
					.synchronizedSortedSet(new TreeSet<CountryStructure>());
			int countryNumber;
			String code2;
			String code3;
			String name;
			String query = "SELECT * FROM  "
			// + getWrappedGriidcShemaName() + "."
					+ RdbmsConnection.wrapInDoubleQuotes("Country");

			// System.out.println("Query: " + query);
			ResultSet rset;

			try {
				rset = RdbmsUtils.getGriidcSecondaryDbConnectionInstance()
						.executeQueryResultSet(query);

				int count = 0;
				while (rset.next()) {

					try {
						count++;
						countryNumber = rset.getInt("Country_Number");
						code2 = rset.getString("Country_ISO3166Code2");
						code3 = rset.getString("Country_ISO3166Code3");
						name = rset.getString("Country_Name");
						CountryStructure cs = new CountryStructure(
								countryNumber, code2, code3, name);
						this.countrySet.add(cs);
					} catch (SQLException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}

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
	}

	private void reportCountrySet() {
		Iterator<CountryStructure> cset = this.countrySet.iterator();
		CountryStructure country = null;
		System.out.println("Report of Countries in the GRIIDC database");
		while (cset.hasNext()) {
			country = cset.next();
			System.out.println(country.toString());
		}
	}

	private class CountryStructure implements Comparable {

		private int countryNumber;
		private String code2;
		private String code3;
		private String name;

		/**
		 * @param countryNumber
		 * @param code2
		 * @param code3
		 * @param name
		 */
		public CountryStructure(int countryNumber, String code2, String code3,
				String name) {
			super();
			this.countryNumber = countryNumber;
			this.code2 = code2;
			this.code3 = code3;
			this.name = name;
		}

		public int getCountryNumber() {
			return countryNumber;
		}

		public String getCode2() {
			return code2;
		}

		public String getCode3() {
			return code3;
		}

		public String getName() {
			return name;
		}

		@Override
		public String toString() {
			return "CountryStructure [countryNumber=" + countryNumber
					+ ", code2=" + code2 + ", code3=" + code3 + ", name="
					+ name + "]";
		}

		@Override
		public int compareTo(Object other) {
			CountryStructure cs = (CountryStructure) other;
			int thisN = this.getCountryNumber();
			int otherN = cs.getCountryNumber();

			if (thisN == otherN)
				return 0;
			else if (thisN < otherN)
				return -1;
			return 1;
		}
	}

	public static void main(String[] args) throws IOException,
			PropertyNotFoundException, SQLException, ClassNotFoundException, TableNotInDatabaseException {
		ParseForCountry pfc = new ParseForCountry();
		pfc.loadCountryReferenceData();
		// RdbmsUtils.reportTables("Departments", "Department");
		//pfc.reportCountrySet();
		RdbmsUtils.reportTables("People", "Person");
		//pfc.reportPeople();

		System.out.println("\nRIS address fields");
		String[] adds = pfc.readRisPeopleAddr2();
/**
		for (int i = 0; i < adds.length; i++) {
			System.out.println(adds[i]);
		}
**/
		System.out.println("\nFinished RIS address 2 fields");
	}

	public String[] readRisPeopleAddr2() {

		// RdbmsConnection.setDebug(true);
		SortedSet<String> sSet = Collections
				.synchronizedSortedSet(new TreeSet<String>());
		String query = "SELECT * FROM  People";

		ResultSet rset = null;
		String[] addrs = null;

		System.out.println("SQL Query: " + query);

		try {
			rset = RdbmsUtils.getRisDbConnectionInstance().executeQueryResultSet(query);
			// rset =
			// RdbmsUtils.getRisDbConnectionInstance().selectAllValuesFromTable("People");
			int count = 0;
			String composit = null;
			String addr2 = null;
			String addr1 = null;
			String lastName = null;
			String city = null;
			String state = null;
			String zip = null;
			while (rset.next()) {
				composit = "";
				lastName = rset.getString("People_LastName");
				addr1 = rset.getString("People_AdrStreet1");
				addr2 = rset.getString("People_AdrStreet2");
				city = rset.getString("People_AdrCity");
				state = rset.getString("People_AdrState");
				zip = rset.getString("People_AdrZip");

				if(isDebug() ) System.out.println("\n" + lastName + ": " + ">" + addr1 + "< >"
						+ addr2 + "<");
				if(isDebug() )  System.out.println("\t" + city + ": " + ">" + state + "< >"
						+ zip + "<");
				if (!MiscUtils.isStringEmpty(addr1)) {
					composit += addr1;
					composit += " ";
					if(isDebug() ) System.out.println("composit 1: " + composit);
				}
				if (!MiscUtils.isStringEmpty(addr2)) {
					composit += addr2;

					if(isDebug() ) System.out.println("composit 2: " + composit);
				}
				if (composit != null) {
					composit = composit.trim();
					if (composit.startsWith("null")) {
						int ndx = composit.indexOf("null");
						addr1 = composit.substring(0, ndx);
					}
					if (composit.length() > 0) {
						sSet.add(composit);
						if(isDebug() ) System.out.println("added composit: " + composit);
					}
				}
			}
			if(isDebug() ) System.out.println("Vector size: " + sSet.size());
			addrs = new String[sSet.size()];
			Iterator<String> its = sSet.iterator();
			int n = 0;
			while (its.hasNext()) {
				addrs[n++] = its.next();
			}
			return addrs;

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

		return addrs;
	}

	private String concatAddr(String addr1, String addr2, 
			String city, String state, String zip) {
       return addr1 + " " +
			  addr2 + " " +
    		   city + " " + 
			  state + " " +
    		   zip;
	}

	private void reportPeople() throws IOException, PropertyNotFoundException,
			SQLException, ClassNotFoundException, TableNotInDatabaseException {
		RdbmsUtils.reportTables("People", "Person");
	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
	}
	
	
}
