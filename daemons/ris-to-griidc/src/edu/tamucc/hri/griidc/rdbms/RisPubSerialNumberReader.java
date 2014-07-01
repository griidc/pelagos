package edu.tamucc.hri.griidc.rdbms;

import java.io.ByteArrayOutputStream;
import java.io.PrintStream;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.pubs.RefBaseWebService;
import edu.tamucc.hri.griidc.pubs.SynchronizerBase;
import edu.tamucc.hri.griidc.utils.PubsConstants;
import edu.tamucc.hri.griidc.utils.MiscUtils;

/**
 * read the Ris database and collect all the publication serial numbers from the
 * PubsInfo. The actual data for the publications is collected via the RefBase
 * web service.
 * 
 * @see RefBaseWebService
 * @author jvh
 * 
 */
public class RisPubSerialNumberReader extends SynchronizerBase {

	private ResultSet risRS = null;

	public static final String RisPubsInfoTableName = "pubsInfo";
	public static final String RisPubsInfoColName = "pubSerial";

	public static final String RisPeoplePublicationTableName = "PeoplePublication";
	public static final String RisPeoplePublicationColName = "Pub_Serial";

	public static final String RisProjPublicationTableName = "ProjPublication";
	public static final String RisProjPublicationColName = RisPeoplePublicationColName;

	public static final String[] table = { // PubsInfoTableName,  // ignore the Pubs Info table. If pub is not connected to Project or Person we don't care
			RisPeoplePublicationTableName, RisProjPublicationTableName };
	public static final String[] colName = { // PubsInfoColName,
			RisPeoplePublicationColName, RisProjPublicationColName };
	
	private   Integer[] pubs = new Integer[table.length];  // count of pubs per table
	private   Integer[] added = new Integer[table.length]; // count added to master set per table
	private   Integer[] duplicates = new Integer[table.length]; // count of duplicates found per table

	private int pubSerialNumberCount = 0;

	private SortedSet<Integer> pubNumberSet = Collections
			.synchronizedSortedSet(new TreeSet<Integer>());

	public RisPubSerialNumberReader() {

	}

	public int getTotalPubsForTable(String targetTableName) {
		int ndx = this.getTableNdx(targetTableName);
		if(ndx != PubsConstants.NotFound) return this.pubs[ndx].intValue();
		return ndx;
	}
	public int getPubsAddedForTable(String targetTableName) {
		int ndx = this.getTableNdx(targetTableName);
		if(ndx != PubsConstants.NotFound) return this.added[ndx].intValue();
		return ndx;
	}
	public int getDuplicatePubsForTable(String targetTableName) {
		int ndx = this.getTableNdx(targetTableName);
		if(ndx != PubsConstants.NotFound) return this.duplicates[ndx].intValue();
		return ndx;
	}
	/**
	 * find the targetTableName and return it's index.
	 * If not found return PubsConstants.NotFound
	 * @param targetTableName
	 * @return
	 */
	private int getTableNdx(String targetTableName) {
		for(int i = 0;i < table.length;i++) {
			if(table[i].equals(targetTableName))
				return i;
		}
		return PubsConstants.NotFound;
	}
	
	/**
	 * get the table names used to collect pubs serial numbers
	 */
	public String[] getTableNames() {
		return table;
	}
	
	/**
	 * get the column names used to collect pubs serial numbers from the tables
	 */
	public String[] getColumnNames() {
		return colName;
	}
	@Override
	protected void initialize() {
		super.commonInitialize();
	}

	private String makeSelectStatement(String tableName, String colName) {
		return "select " + colName + " from " + tableName + " order by "
				+ colName;
	}

	/**
	 * return a string that shows the number of pub numbers found, added and duplicates for each table
	 * @return
	 */
	public static final String ReportFormat = "%nTable: %-25s read: %5d, added: %5d, duplicates: %5d";
	public String[] getReportStrings() {
		int reportNdx = 0;
		String[] report = new String[this.getTableNames().length + 1];
		report[reportNdx++] = "Publication serial numbers read from RIS";
		for(int i = 0; i < this.getTableNames().length;i++) {
			report[reportNdx++] = MiscUtils.getFormattedString(ReportFormat, this.getTableNames()[i], this.pubs[i], this.added[i], this.duplicates[i] );
		}
		return report;
	}
	
	public int[] getAllRisPubSerialNumbers() throws SQLException {
		initialize();
		String query = null;
		String pubNumberColName = null;
		this.pubNumberSet = Collections
				.synchronizedSortedSet(new TreeSet<Integer>());
		// initialize counters
		for (int i = 0; i < table.length; i++) {
			pubs[i] = added[i] = duplicates[i] = 0;
		}
		
		for (int i = 0; i < table.length; i++) {
			pubNumberColName = colName[i];
			query = this.makeSelectStatement(table[i], pubNumberColName);
			this.risRS = this.getRisDbConnection().executeQueryResultSet(query);
            
			int sNum = -1;
			while (this.risRS.next()) {
				pubSerialNumberCount++;
				sNum = this.risRS.getInt(pubNumberColName);
				pubs[i]++;
				if(pubNumberSet.add(sNum)) {
					added[i]++;
				} else { // duplicate
					duplicates[i]++;
				}
			}
		}
		int[] sNumbers = new int[pubNumberSet.size()];
		int ndx = 0;
		Iterator<Integer> it = pubNumberSet.iterator();
		while (it.hasNext())
			sNumbers[ndx++] = it.next().intValue();
		return sNumbers;
	}
}
