package edu.tamucc.hri.griidc.rdbms;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collections;
import java.util.SortedSet;
import java.util.TreeSet;
import java.util.Vector;

import edu.tamucc.hri.griidc.exception.IllegalFundingSourceCodeException;
import edu.tamucc.hri.griidc.exception.MultipleRecordsFoundException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.utils.PubsConstants;

public class RdbmsPubsUtils {

	public static boolean Debug = false;

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
	}

	public static String getPgBoolean(boolean flag) {
		return (flag) ? RdbmsConstants.TRUE : RdbmsConstants.FALSE;
	}

	public RdbmsPubsUtils() {

	}

	public static TableColInfoCollection GriidcDefaultValueTableColInfoCollection = null;
	public static TableColInfoCollection GriidcTableColInfoCollection = null;

	/**
	 * make an insert statement for the tableName supplied with the info/data in
	 * the colInfo DbColumnInfo array
	 * 
	 * @param tableName
	 * @param colInfo
	 * @see RdbmsUtils.getMetaDataForTable()
	 * @return
	 */
	public static String formatInsertStatement(String tableName,
			DbColumnInfo[] colInfo) {
		StringBuffer sb = new StringBuffer("INSERT INTO ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes(tableName)
				+ RdbmsConstants.SPACE + "(");

		String colName = null;
		String colType = null;
		String colValue = null;

		// format the column name part
		boolean notTheFirstTime = false;
		for (DbColumnInfo dbColInfo : colInfo) {
			colName = dbColInfo.getColName();
			colType = dbColInfo.getColType();
			colValue = dbColInfo.getColValue();
			if (colValue != null) { // there is a value here
				if (notTheFirstTime)
					sb.append(RdbmsConstants.CommaSpace);
				sb.append(RdbmsConnection.wrapInDoubleQuotes(colName));
				notTheFirstTime = true;
			}
		}
		// add the value part of the clause
		sb.append(") VALUES (");
		notTheFirstTime = false;
		for (DbColumnInfo dbColInfo : colInfo) {
			colName = dbColInfo.getColName();
			colType = dbColInfo.getColType();
			colValue = dbColInfo.getColValue();
			if (colValue != null) { // there is a value here
				if (notTheFirstTime)
					sb.append(RdbmsConstants.CommaSpace);
				sb.append(RdbmsPubsUtils
						.wrapDbValue(colName, colType, colValue));

				notTheFirstTime = true;
			}
		}
		sb.append(" )");

		return sb.toString();
	}

	/**
	 * For storage in the database all values must be wrapped in either single
	 * quotes, double quotes or left un wrapped. USER-DEFINED types are a
	 * special case. Currently this handles two USER-DEFINED types,
	 * GeoCoordinate and Telephone-Type. These are detected by examining the
	 * column name. If other types are added this code will break. JVH
	 * 
	 * @param colName
	 * @param colType
	 * @param colValue
	 * @return
	 */
	private static String wrapDbValue(String colName, String colType,
			String colValue) {

		if (RdbmsPubsUtils.isDebug())
			System.out.println("RdbmsUtils.wrapDbValue(" + colName + ", "
					+ colType + ", " + colValue + ")");

		String rtnValue = colValue;
		if (colType.equals(RdbmsConstants.DbBoolean)
				|| colType.equals(RdbmsConstants.DbInteger)
				|| colType.equals(RdbmsConstants.DbNumeric)) {
			rtnValue = colValue;
		} else if (colType.equals(RdbmsConstants.DbUserDefined)) {

			if (colName.toUpperCase().contains("GeoCoordinate".toUpperCase())) {
				rtnValue = colValue;
			} else if (colName.toUpperCase().contains(
					"Telephone_Type".toUpperCase())) {
				rtnValue = RdbmsConnection.wrapInSingleQuotes(colValue);
			}
		} else { // else colType is some sort of String thing
			rtnValue = RdbmsConnection.wrapInSingleQuotes(colValue);
		}
		if (RdbmsPubsUtils.isDebug())
			System.out
					.println("RdbmsUtils.wrapDbValue() returning " + rtnValue);
		return rtnValue;
	}

	/**
	 * make a SELECT statement from Column information stored in the
	 * DbColumnInfo array
	 * 
	 * @param tableName
	 * @param whereColInfo
	 * @see RdbmsUtils.getMetaDataForTable()
	 * @return
	 */
	public static String formatSelectStatement(String tableName,
			DbColumnInfo[] whereColInfo) {
		StringBuffer sb = new StringBuffer("SELECT * FROM  ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes(tableName));
		sb.append(formatWhereClause(whereColInfo));
		return sb.toString();
	}

	/**
	 * 
	 * @param tableName
	 * @param updateColInfo
	 * @param whereColInfo
	 * @see RdbmsUtils.getMetaDataForTable()
	 * @return
	 * @throws SQLException
	 */
	public static String formatUpdateStatement(String tableName,
			DbColumnInfo[] updateColInfo, DbColumnInfo[] whereColInfo)
			throws SQLException {

		// format the column name part
		boolean notTheFirstTime = false;
		String colName = null;
		String colType = null;
		String colValue = null;
		StringBuffer sb = new StringBuffer("UPDATE  ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes(tableName)
				+ RdbmsConstants.SPACE + " SET ");
		for (DbColumnInfo dbColInfo : updateColInfo) {
			colType = dbColInfo.getColType();
			colName = dbColInfo.getColName();
			colValue = dbColInfo.getColValue();
			if (colValue != null) { // there is a value here
				if (notTheFirstTime)
					sb.append(RdbmsConstants.CommaSpace);
				sb.append(RdbmsConnection.wrapInDoubleQuotes(colName));
				sb.append(RdbmsConstants.EqualSign);

				sb.append(RdbmsPubsUtils
						.wrapDbValue(colName, colType, colValue));
				notTheFirstTime = true;
			}
		}
		sb.append(formatWhereClause(whereColInfo));
		if (RdbmsPubsUtils.isDebug())
			System.out.println("RdbmsUtils.formatModifyQuery() "
					+ sb.toString());
		return sb.toString();
	}

	/**
	 * Using DbColumnInfo array format the where statement for use in Select and
	 * Update queries.
	 * 
	 * @see RdbmsConstants.getMetaDataForTable()
	 * 
	 * @param whereColInfo
	 * @return
	 */
	public static String formatWhereClause(DbColumnInfo[] whereColInfo) {
		String colName = null;
		String colType = null;
		String colValue = null;
		StringBuffer sb = new StringBuffer(" WHERE ");

		boolean notTheFirstTime = false;
		for (DbColumnInfo dbColInfo : whereColInfo) {
			colType = dbColInfo.getColType();
			colName = dbColInfo.getColName();
			colValue = dbColInfo.getColValue();
			if (colValue != null) { // there is a value here
				if (notTheFirstTime) {
					sb.append(RdbmsConstants.And);
				}
				sb.append(RdbmsConnection.wrapInDoubleQuotes(colName));
				sb.append(RdbmsConstants.EqualSign);
				sb.append(RdbmsPubsUtils
						.wrapDbValue(colName, colType, colValue));
				notTheFirstTime = true;
			}
		}
		if (RdbmsPubsUtils.isDebug())
			System.out.println("RdbmsUtils.formatWhereClause() "
					+ sb.toString());
		return sb.toString();
	}

	/**
	 * TODO modify this for switching between full name, two char abbreviation
	 * and three char abbreviation
	 * 
	 * @param countryName
	 * @return
	 * @throws FileNotFoundException
	 * @throws SQLException
	 * 
	 * @throws PropertyNotFoundException
	 * @throws MultipleRecordsFoundException
	 * @throws NoRecordFoundException
	 */
	public static final int Country_ISO3166Code_Length3 = 3;
	public static final int Country_ISO3166Code_Length2 = 2;

	public static int getCountryNumberFromName(String countryCode)
			throws SQLException, MultipleRecordsFoundException,
			NoRecordFoundException {

		int codeLength = countryCode.trim().length();

		String countryColumnName = "Country_Name"; // could be full name, 3
													// char, 2 char, null or
													// garbage

		if (codeLength == Country_ISO3166Code_Length2) {
			countryColumnName = "Country_ISO3166Code2";
		} else if (codeLength == Country_ISO3166Code_Length3) {
			countryColumnName = "Country_ISO3166Code3";
		} else {
			countryColumnName = "Country_Name";
		}

		int num = -1; // this is the key in Country table

		String query = "SELECT * FROM  "
				// + getWrappedGriidcShemaName() + "."
				+ RdbmsConnection.wrapInDoubleQuotes("Country") + "  WHERE  "
				+ RdbmsConnection.wrapInDoubleQuotes(countryColumnName)
				+ RdbmsConstants.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(countryCode);

		// System.out.println("Query: " + query);
		ResultSet rset = RdbmsPubsUtils
				.getGriidcSecondaryDbConnectionInstance()
				.executeQueryResultSet(query);
		int count = 0;
		while (rset.next()) {
			count++;
			num = rset.getInt("Country_Number");

		}
		if (count == 0) {
			String msg = "NO record found in the GRIIDC Country table with the Country_Name: "
					+ countryCode;
			throw new NoRecordFoundException(msg);
		} else if (count > 1) { // duplicates
			String msg = "There are "
					+ count
					+ " records in the GRIIDC Country table with the Country_Name: "
					+ countryCode;
			throw new MultipleRecordsFoundException(msg);
		}
		return num;
	}

	public static int getIntValueFromTable(RdbmsConnection con,
			String tableName, String keyColumnName, int keyValue,
			String targetColName) throws SQLException, NoRecordFoundException,
			MultipleRecordsFoundException {

		String query = "SELECT * FROM  "
				// + getWrappedGriidcShemaName() + "."
				+ RdbmsConnection.wrapInDoubleQuotes(tableName) + "  WHERE  "
				+ RdbmsConnection.wrapInDoubleQuotes(keyColumnName)
				+ RdbmsConstants.EqualSign + keyValue;

		ResultSet rset = null;
		try {
			rset = con.executeQueryResultSet(query);
		} catch (Exception e) {
			System.out.println("SQL Exception on query" + query
					+ "\n message: " + query);
		}
		int returnValue = -1;
		int count = 0;
		while (rset.next()) {
			count++;
			returnValue = rset.getInt(targetColName);
		}
		if (count == 0) {
			String msg = "NO record found in the " + con.getDbName() + " "
					+ tableName + " table for " + keyColumnName + ": "
					+ keyValue;
			throw new NoRecordFoundException(msg);
		} else if (count > 1) { // duplicates
			String msg = "There are " + count + " records in the  GRIIDC "
					+ tableName + " table which match " + keyColumnName + ": "
					+ keyValue;
			throw new MultipleRecordsFoundException(msg);
		}
		//
		// only one match found - return the number
		//
		return returnValue;
	}

	/**
	 * 
	 * @param risTableName
	 * @param griidcTableName
	 * @throws IOException
	 * @throws PropertyNotFoundException
	 * @throws SQLException
	 * 
	 *             TODO: this function gets reports a different path for the
	 *             file than it is using
	 * @throws TableNotInDatabaseException
	 */
	public static void reportPeopleTableData() throws IOException,
			SQLException, TableNotInDatabaseException {
		ResultSet rset = RdbmsPubsUtils.getRisDbConnectionInstance()
				.selectAllValuesFromTable("People");
		int id = -1;
		String lastName = null;
		String firstName = null;
		int institution = -1;
		int count = 0;
		while (rset.next()) { // continue statements branch back to here
			count++;
			id = rset.getInt("People_ID");
			institution = rset.getInt("People_Institution");
			lastName = rset.getString("People_LastName");
			firstName = rset.getString("People_FirstName");
			System.out.println("People: id: " + id + ", name: " + lastName
					+ ", " + firstName + " Inst: " + institution);
		}
		System.out.println("count: " + count);
		return;
	}

	/**
	 * 
	 * @param risTableName
	 * @param griidcTableName
	 * @throws IOException
	 * @throws PropertyNotFoundException
	 * @throws SQLException
	 * 
	 * @throws TableNotInDatabaseException
	 */
	public static void reportTables(String risTableName, String griidcTableName)
			throws IOException, SQLException, TableNotInDatabaseException {
		String[] tableNames = { risTableName };
		RdbmsPubsUtils.getRisDbConnectionInstance()
				.reportTableColumnNamesAndDataType(tableNames);
		tableNames[0] = griidcTableName;
		RdbmsPubsUtils.getGriidcDbConnectionInstance()
				.reportTableColumnNamesAndDataType(tableNames);
		return;
	}

	public static RdbmsConnection getRisDbConnectionInstance()
			throws SQLException {
		return RdbmsConnectionFactory.getRisDbConnectionInstance();
	}

	public static RdbmsConnection getGriidcDbConnectionInstance()
			throws SQLException {
		return RdbmsConnectionFactory.getGriidcDbConnectionInstance();
	}

	public static RdbmsConnection getGriidcSecondaryDbConnectionInstance()
			throws SQLException {
		return RdbmsConnectionFactory.getGriidcSecondaryDbConnectionInstance();
	}

	public static void closeGriidcSecondaryDbConnection() throws SQLException {
		RdbmsConnectionFactory.closeGriidcSecondaryDbConnection();
	}

	/**
	 * interogate the database system to determine if there is a column in the
	 * specified table (tableName) that has one or more columns that have
	 * default values and if so what is the value. SELECT column_default FROM
	 * information_schema.columns WHERE table_name = 'Institution-Telephone';
	 * 
	 * @param tableName
	 * @return
	 * @throws FileNotFoundException
	 * @throws SQLException
	 * 
	 * @throws PropertyNotFoundException
	 */
	public static String[] getColumnDefaultValue(RdbmsConnection connection,
			String tableName) throws FileNotFoundException, SQLException {
		String query = "SELECT column_default FROM information_schema.columns WHERE table_name = "
				+ RdbmsConnection.wrapInSingleQuotes(tableName);
		ResultSet rset = connection.executeQueryResultSet(query);
		Vector<String> v = new Vector<String>();
		String s = null;
		while (rset.next()) {
			s = rset.getString(1);
			v.add(s);
		}
		String[] tArray = new String[v.size()];
		tArray = v.toArray(tArray);
		return tArray;

	}

	/**
	 * read all the tables in the database specified in the RdbmsConnection and
	 * return a string that reports the col and type for each table.
	 * 
	 * @param dbcon
	 * @return
	 * @throws FileNotFoundException
	 * @throws SQLException
	 * 
	 * @throws TableNotInDatabaseException
	 */
	public static String getColumnNamesAndDataTypesFromTables(
			RdbmsConnection dbcon, String[] targetTables)
			throws FileNotFoundException, SQLException,
			TableNotInDatabaseException {
		String formatString = "%-30s  %-40s";
		String[] tableName = null;
		if (targetTables != null)
			tableName = targetTables;
		else
			tableName = dbcon.getTableNamesForDatabase();
		String[][] colAndType = null;
		final int COL = 0;
		final int DT = 1; // data type
		StringBuffer sb = new StringBuffer();
		sb.append(dbcon.getShortDescription() + RdbmsConstants.NewLine
				+ RdbmsConstants.NewLine);
		for (String t : tableName) {
			if (isDebug())
				System.out.println(t);
			sb.append(t + RdbmsConstants.NewLine);
			colAndType = dbcon.getColumnNamesAndDataTypesFromTable(t);
			for (int i = 0; i < colAndType[COL].length; i++) {
				String col = colAndType[COL][i];
				String type = colAndType[DT][i];
				sb.append(RdbmsConstants.Tab
						+ String.format(formatString, col.trim(), type.trim())
						+ RdbmsConstants.NewLine);
			}
			sb.append("--------------------------------------------------"
					+ RdbmsConstants.NewLine);
		}
		return sb.toString();
	}

	public static String[] getUniqueDataTypes(RdbmsConnection dbcon,
			String[] targetTables) throws SQLException,
			TableNotInDatabaseException {

		String formatString = "%-30s  %-40s";
		String[][] colAndType = null;
		final int COL = 0;
		final int DT = 1; // data type
		SortedSet<String> unique = Collections
				.synchronizedSortedSet(new TreeSet<String>());
		for (String t : targetTables) {
			if (isDebug())
				System.out.println(t);
			colAndType = dbcon.getColumnNamesAndDataTypesFromTable(t);
			for (int i = 0; i < colAndType[COL].length; i++) {
				String col = colAndType[COL][i];
				String type = colAndType[DT][i];
				unique.add(type.trim());
				if (isDebug()) {
					System.out.println(RdbmsConstants.Tab
							+ String.format(formatString, col.trim(),
									type.trim()) + RdbmsConstants.NewLine);
				}
			}
		}
		String[] solitary = new String[unique.size()];
		solitary = unique.toArray(solitary);
		return solitary;
	}

	public static void reportColumnNamesAndDataTypesFromTables(
			RdbmsConnection dbcon, String[] tables)
			throws FileNotFoundException, SQLException,
			TableNotInDatabaseException {
		System.out.println(RdbmsPubsUtils.getColumnNamesAndDataTypesFromTables(
				dbcon, tables));
	}

	public static TableColInfoCollection createTableColInfoCollection(
			RdbmsConnection conn, String[] tableNames) throws SQLException {
		TableColInfoCollection tciCollection = new TableColInfoCollection();
		for (String tName : tableNames) {
			TableColInfo tci = RdbmsPubsUtils.createTableColInfo(conn, tName);
			tciCollection.addTableColInfo(tci);
		}
		return tciCollection;
	}

	public static TableColInfo getMetaDataForTable(
			RdbmsConnection dbConnection, String tableName) throws SQLException {
		// metaData is a set of descriptions for the columns in the table
		return createTableColInfo(dbConnection, tableName);
	}

	/**
	 * For a given table return a two D array (R) inwhich R[0][?] is the column
	 * name and R[1][?] is the data type of the column. Return the 2D table
	 * which could be empty but will not be null
	 * 
	 * @param tableName
	 * @return
	 * @throws FileNotFoundException
	 * @throws SQLException
	 */
	public static TableColInfo createTableColInfo(RdbmsConnection conn,
			String tableName) throws SQLException {
		String querry = "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "
				+ RdbmsConnection.wrapInSingleQuotes(tableName);

		TableColInfo tableColInfo = new TableColInfo(tableName);
		ResultSet rs = conn.executeQueryResultSet(querry);

		String colName = null;
		String colType = null;
		DefaultValue defaultValue = null;
		while (rs.next()) {
			colName = rs.getString(1);
			colType = rs.getString(2);
			defaultValue = new DefaultValue(rs.getString(3));
			tableColInfo.addDbColumnInfo(colName, colType, defaultValue);
		}
		return tableColInfo;
	}

	public static TableColInfoCollection getAllDataFromTable(
			RdbmsConnection dbConnection, String tableName)
			throws SQLException, TableNotInDatabaseException {
		TableColInfoCollection dataSet = new TableColInfoCollection();
		// metaData is a set of descriptions for the columns in the table
		TableColInfo metaData = createTableColInfo(dbConnection, tableName);
		String[] colName = metaData.getColumnNames();
		String[] colType = metaData.getColumnTypes();
		// tci is one row of data from the table with meta data info
		TableColInfo tci = null;
		ResultSet rs = RdbmsPubsUtils.getGriidcDbConnectionInstance()
				.selectAllValuesFromTable(tableName);
		while (rs.next()) { // for every record returned
			// make a new column info object
			tci = new TableColInfo(tableName);
			String value = null;
			// get the value for each column in the record
			for (int i = 0; i < colName.length; i++) {
				value = rs.getString(colName[i]);
				tci.addDbColumnInfo(colName[i], colType[i], value);
			}
			// this record for ResultSet is finished. Put it in the
			// TableColInfoCollection to return
			dataSet.addTableColInfo(tci);
		}
		return dataSet;
	}

	public static String stripDefaultValue(String s) {
		int start = s.indexOf('\'');
		start++;
		int end = s.indexOf('\'', start);
		String ts = s.substring(start, end);
		System.out.println(s + " turns into " + ts);
		return ts;
	}

	/**
	 * turn a longitude and lattitude (in decimal degrees) into a proper
	 * Postgresql geometry point
	 * 
	 * @param lon
	 * @param lat
	 * @return
	 */
	public static String makeSqlGeometryPointString(double lon, double lat) {
		return "ST_SetSRID(ST_MakePoint(" + lon + "," + lat + "), 4326)";
	}

	/**
	 * given a RIS People Table People_ID find a return the corresponding GRIIDC
	 * Person_Number from the GoMRIPerson-Department-RIS_ID Table. Throw a
	 * NoRecordFoundException if a match is not found
	 * 
	 * @throws SQLException
	 */

	public static int getGriidcPersonNumberForRisPeopleId(int risPeopleId)
			throws NoRecordFoundException, SQLException {
		int griidcPersonNumber = PubsConstants.Undefined;
		String tableName = "GoMRIPerson-Department-RIS_ID";
		String peopleIdColName = "RIS_People_ID";
		String personNumberColName = "Person_Number";
		String query = "SELECT "
				+ RdbmsConnection.wrapInDoubleQuotes(personNumberColName)
				+ " FROM " + RdbmsConnection.wrapInDoubleQuotes(tableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(peopleIdColName) + " = "
				+ risPeopleId;
		if (RdbmsPubsUtils.isDebug()) {
			System.out
					.println("RdbmsUtils.getGriidcPersonNumberForRisPeopleId("
							+ risPeopleId + ") query: " + query);
		}
		ResultSet rset = RdbmsPubsUtils.getGriidcDbConnectionInstance()
				.executeQueryResultSet(query);
		while (rset.next()) {
			griidcPersonNumber = rset.getInt(personNumberColName);
		}
		if (griidcPersonNumber == PubsConstants.Undefined) {
			throw new NoRecordFoundException("No " + tableName
					+ " record found in which " + peopleIdColName + " equals "
					+ risPeopleId);
		}
		return griidcPersonNumber;
	}

	/**
	 * this is straight from Patrick Krepps SQL CASE FundingSource.Fund_Source
	 * 
	 * @param risFundingSource
	 * @return
	 */
	public static String convertRisFundingSourceToGriidcFormat(
			String risFundingSource) throws IllegalFundingSourceCodeException {
		String target = risFundingSource.trim().toUpperCase();

		if (target.equals("FIO"))
			return "B01";
		else if (target.equals("LSU"))
			return "B02";
		else if (target.equals("MESC"))
			return "B03";
		else if (target.equals("NGI"))
			return "B04";
		else if (target.equals("NIH"))
			return "B05";
		else if (target.equals("RFP-I"))
			return "R01";
		else if (target.equals("RFP-II"))
			return "R02";
		else if (target.equals("RFP-III"))
			return "R03";
		throw new IllegalFundingSourceCodeException("Funding Source Code: "
				+ risFundingSource + " is not one of "
				+ "FIO, LSU, MESC, NGI, NIH, RFP-I, RFP-II,RFP-III");
	}

	public static void main(String[] args) {
		// RdbmsUtils.setDebug(true);
		int[] peopleIds = { 23, 25, 26, 27, 29, 30, 31, 32, 33, 35, 36, 38, 39,
				40, 43 };
		for (int pid : peopleIds) {
			try {
				int personId = RdbmsPubsUtils
						.getGriidcPersonNumberForRisPeopleId(pid);
				System.out.println("GRIIDC Person: " + personId
						+ " matches RIS people ID: " + pid);
			} catch (NoRecordFoundException e) {
				System.out.println(e.getMessage());
				e.printStackTrace();
			} catch (SQLException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
				System.out.println("");
			}
		}
	}

	private static GriidcRisDepartmentMap griidcRisDepartmentMapInstance = null;

	/**
	 * get a collection of objects that contain the correspondence between Ris
	 * Department to GRIIDC department
	 * 
	 * @return
	 */

	public static GriidcRisDepartmentMap getGriidcRisDepartmentMap() {
		if (RdbmsPubsUtils.griidcRisDepartmentMapInstance == null) {
			RdbmsPubsUtils.griidcRisDepartmentMapInstance = new GriidcRisDepartmentMap();
			RdbmsPubsUtils.griidcRisDepartmentMapInstance.initialize();
			String query = "SELECT * FROM  "
					+ RdbmsConnection
							.wrapInDoubleQuotes(RdbmsConstants.GriidcDeptTableName);

			ResultSet rset;
			try {
				rset = RdbmsPubsUtils.getGriidcSecondaryDbConnectionInstance()
						.executeQueryResultSet(query);

				int risDptId = -1;
				int griidcDptNum = -1;
				while (rset.next()) {
					griidcDptNum = rset.getInt("Department_Number");
					risDptId = rset.getInt("Department_RIS_ID");
					RdbmsPubsUtils.griidcRisDepartmentMapInstance.put(risDptId,
							griidcDptNum);
				}
			} catch (SQLException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		}
		return RdbmsPubsUtils.griidcRisDepartmentMapInstance;
	}
	
	
}
