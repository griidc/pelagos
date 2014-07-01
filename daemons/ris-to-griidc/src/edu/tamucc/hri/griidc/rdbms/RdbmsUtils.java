package edu.tamucc.hri.griidc.rdbms;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.SQLException;
import java.util.Collections;
import java.util.SortedSet;
import java.util.TreeSet;
import java.util.Vector;

import edu.tamucc.hri.griidc.exception.MultipleRecordsFoundException;
import edu.tamucc.hri.griidc.exception.IllegalFundingSourceCodeException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.utils.GriidcRisDepartmentMap;
import edu.tamucc.hri.griidc.utils.GriidcRisInstitutionMap;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public class RdbmsUtils {

	public static boolean Debug = false;

	public static String getPgBoolean(boolean flag) {
		return (flag) ? RdbmsConstants.TRUE : RdbmsConstants.FALSE;
	}

	public RdbmsUtils() {

	}

	public static TableColInfoCollection GriidcDefaultValueTableColInfoCollection = null;
	public static TableColInfoCollection GriidcTableColInfoCollection = null;

	/**
	 * make an insert statment for the tableName supplied with the info/data in
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
				sb.append(RdbmsUtils.wrapDbValue(colName, colType, colValue));

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

		if (RdbmsUtils.isDebug())
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
		if (RdbmsUtils.isDebug())
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

				sb.append(RdbmsUtils.wrapDbValue(colName, colType, colValue));
				notTheFirstTime = true;
			}
		}
		sb.append(formatWhereClause(whereColInfo));
		if (RdbmsUtils.isDebug())
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
				sb.append(RdbmsUtils.wrapDbValue(colName, colType, colValue));
				notTheFirstTime = true;
			}
		}
		if (RdbmsUtils.isDebug())
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
		ResultSet rset = RdbmsUtils.getGriidcSecondaryDbConnectionInstance()
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

	/**
	 * get a collection of objects that contain the correspondence
	 * between Ris Department to GRIIDC department
	 * @return
	 */
	public static GriidcRisDepartmentMap getGriidcRisDepartmentMap() {
		GriidcRisDepartmentMap grdm = GriidcRisDepartmentMap.getInstance();
		grdm.initialize();
		String query = "SELECT * FROM  "
		// + getWrappedGriidcShemaName() + "."
				+ RdbmsConnection
						.wrapInDoubleQuotes(RdbmsConstants.GriidcDeptTableName);

		ResultSet rset;
		try {
			rset = RdbmsUtils.getGriidcSecondaryDbConnectionInstance()
					.executeQueryResultSet(query);

			int risDptId = -1;
			int griidcDptNum = -1;
			while (rset.next()) {
				griidcDptNum = rset.getInt("Department_Number");
				risDptId = rset.getInt("Department_RIS_ID");
				grdm.put(risDptId, griidcDptNum);
			}
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return grdm;
	}

	public static GriidcRisInstitutionMap getGriidcRisInstitutionMap() {
		GriidcRisInstitutionMap grim = GriidcRisInstitutionMap.getInstance();
		grim.initialize();
		String query = "SELECT * FROM  "
		// + getWrappedGriidcShemaName() + "."
				+ RdbmsConnection
						.wrapInDoubleQuotes(RdbmsConstants.GriidcInstTableName);
		try {
			ResultSet rset = RdbmsUtils
					.getGriidcSecondaryDbConnectionInstance()
					.executeQueryResultSet(query);

			int risIntsId = -1;
			int griidcIntsNum = -1;
			while (rset.next()) {
				griidcIntsNum = rset.getInt("Institution_Number");
				risIntsId = rset.getInt("Institution_RIS_ID");
				grim.put(risIntsId, griidcIntsNum);
			}
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return grim;
	}

	public static boolean doesGriidcDepartmentExist(int institutionNumber,
			int departmentNumber) throws SQLException, NoRecordFoundException,
			MultipleRecordsFoundException {

		String query = "SELECT * FROM  "
				// + getWrappedGriidcShemaName() + "."
				+ RdbmsConnection
						.wrapInDoubleQuotes(RdbmsConstants.GriidcDeptTableName)
				+ "  WHERE  "
				+ RdbmsConnection.wrapInDoubleQuotes("Department_Number")
				+ RdbmsConstants.EqualSign + departmentNumber
				+ RdbmsConstants.And
				+ RdbmsConnection.wrapInDoubleQuotes("Institution_Number")
				+ RdbmsConstants.EqualSign + institutionNumber;
		ResultSet rset = RdbmsUtils.getGriidcSecondaryDbConnectionInstance()
				.executeQueryResultSet(query);

		int count = 0;
		while (rset.next()) {
			count++;
		}
		if (count <= 0)
			throw new NoRecordFoundException(
					"In Department table - no records match Department_Number: "
							+ departmentNumber + ", Institution_Number: "
							+ institutionNumber);
		if (count == 1)
			return true;

		if (count > 1)
			throw new MultipleRecordsFoundException("In Department table - "
					+ count + " records match Department_Number: "
					+ departmentNumber + ", Institution_Number: "
					+ institutionNumber);
		return false;

	}

	public static int getGriidcPersonNumberMatchingRisPeopleId(int risPeopleId)
			throws SQLException, NoRecordFoundException,
			MultipleRecordsFoundException {

		return getGriidcNumMatchingRisId(
				RdbmsConstants.GriidcPersonDepartmentRisPeopleIdTableName,
				"RIS_People_ID", risPeopleId);
	}

	public static int getGriidcInstitutionNumberMatchingRisInstitutionId(
			int risInstId) throws SQLException, NoRecordFoundException,
			MultipleRecordsFoundException {

		return getGriidcNumMatchingRisId(RdbmsConstants.GriidcInstTableName,
				"Institution_RIS_ID", risInstId);
	}

	public static int getGriidcDepartmentNumberMatchingRisDepartmentsId(
			int risDeptId) throws SQLException, NoRecordFoundException,
			MultipleRecordsFoundException {

		return getGriidcNumMatchingRisId(RdbmsConstants.GriidcDeptTableName,
				"Department_RIS_ID", risDeptId);
	}

	public static int getGriidcNumMatchingRisId(String tableName,
			String risIdColName, int id) throws SQLException,
			NoRecordFoundException, MultipleRecordsFoundException {

		String query = "SELECT * FROM  "
				// + getWrappedGriidcShemaName() + "."
				+ RdbmsConnection.wrapInDoubleQuotes(tableName) + "  WHERE  "
				+ RdbmsConnection.wrapInDoubleQuotes(risIdColName)
				+ RdbmsConstants.EqualSign + id;
		ResultSet rset = RdbmsUtils.getGriidcSecondaryDbConnectionInstance()
				.executeQueryResultSet(query);

		int num = -1;
		int count = 0;
		while (rset.next()) {
			count++;
			num = rset.getInt(risIdColName);
		}
		if (count <= 0) {
			throw new NoRecordFoundException("In GRIIDC " + tableName
					+ " table - no records match " + risIdColName + ": " + id);
		} else if (count > 1) {
			throw new MultipleRecordsFoundException("In GRIIDC " + tableName
					+ " table  - " + count + " records match " + risIdColName
					+ ": " + id);
		}

		return num;
	}

	public static int getGriidcDepartmentCountryNumber(int departmentNumber)
			throws SQLException, NoRecordFoundException,
			MultipleRecordsFoundException {
		int postalCode = RdbmsUtils
				.getGriidcDepartmentPostalNumber(departmentNumber);
		// get the country code from the PostalArea table using the postalCode
		int countryNumber = RdbmsUtils.getIntValueFromTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), "PostalArea",
				"PostalArea_Number", postalCode, "Country_Number");
		return countryNumber;
	}

	public static int getGriidcDepartmentPostalNumber(int departmentNumber)
			throws SQLException, NoRecordFoundException,
			MultipleRecordsFoundException {
		return RdbmsUtils.getIntValueFromTable(
				RdbmsUtils.getGriidcDbConnectionInstance(),
				RdbmsConstants.GriidcDeptTableName, "Department_Number",
				departmentNumber, "PostalArea_Number");
	}

	/**
	 * looking in the PostalArea table for a match from a RIS record
	 * 
	 * @param city
	 * @param state
	 * @param zip
	 * @param countryName
	 * @return
	 * @throws FileNotFoundException
	 * @throws SQLException
	 * 
	 * @throws PropertyNotFoundException
	 * @throws MultipleRecordsFoundException
	 * @throws NoRecordFoundException
	 * @throws MissingArgumentsException
	 */
	public static int getGriidcDepartmentPostalNumber(int countryNumber,
			String state, String city, String zip)
			throws FileNotFoundException, SQLException,
			MultipleRecordsFoundException, NoRecordFoundException,
			MissingArgumentsException {

		MiscUtils.isValidPostalAreaData(state, city, zip);

		String query = "SELECT * FROM "
				// + this.getWrappedGriidcShemaName() + "."
				+ RdbmsConnection.wrapInDoubleQuotes("PostalArea")
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes("Country_Number")
				+ RdbmsConstants.EqualSign
				+ countryNumber
				+ RdbmsConstants.And
				+ RdbmsConnection
						.wrapInDoubleQuotes("PostalArea_AdministrativeAreaAbbr")
				+ RdbmsConstants.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(state)
				+ RdbmsConstants.And
				+ RdbmsConnection.wrapInDoubleQuotes("PostalArea_City")
				+ RdbmsConstants.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(city) + RdbmsConstants.And
				+ RdbmsConnection.wrapInDoubleQuotes("PostalArea_PostalCode")
				+ RdbmsConstants.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(zip);

		ResultSet rset = null;
		try {
			rset = RdbmsUtils.getGriidcSecondaryDbConnectionInstance()
					.executeQueryResultSet(query);
		} catch (Exception e) {
			System.out.println("SQL Exception on query" + query
					+ "\n message: " + query);
		}
		int postalAreaNumber = RdbmsConstants.NotFound; // this is the key
		int count = 0;
		while (rset.next()) {
			count++;
			postalAreaNumber = rset.getInt("PostalArea_Number");
		}
		if (count == 0) {
			String msg = "NO record found in the GRIIDC PostalArea table for  country number: "
					+ countryNumber
					+ ",  state: "
					+ state
					+ ", city: "
					+ city
					+ ", zip: " + zip;
			throw new NoRecordFoundException(msg);
		} else if (count > 1) { // duplicates
			String msg = "There are "
					+ count
					+ " records in the  GRIIDC PostalArea table which match  country number: "
					+ countryNumber + ",  state: " + state + ", city: " + city
					+ ". zip: " + zip;
			throw new MultipleRecordsFoundException(msg);
		}
		//
		// only one match found - return the number
		//
		return postalAreaNumber;
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
		ResultSet rset = RdbmsUtils.getRisDbConnectionInstance()
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
		RdbmsUtils.getRisDbConnectionInstance()
				.reportTableColumnNamesAndDataType(tableNames);
		tableNames[0] = griidcTableName;
		RdbmsUtils.getGriidcDbConnectionInstance()
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
		System.out.println(RdbmsUtils.getColumnNamesAndDataTypesFromTables(
				dbcon, tables));
	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
	}

	public static final String RisShortListTables[] = { "FundingSource",
			"Programs", "ProjKeywords", "Projects", "Roles" };

	public static final String RisDatabaseApplicationTables[] = { "ConfReg",
			"Country", "Departments", "FundingSource", "G_Project",
			"GulfBaseInstitutions", "GulfBasePeople", "Institutions",
			"Keywords", "Log", "People", "PeoplePublication", "Programs",
			"ProjKeywords", "ProjPeople", "ProjPublication", "ProjThemes",
			"Projects", "Roles", "State", "Students", "Themes", "pubsInfo" };

	public static final String GriidcShortListTables[] = { "FundingEnvelope",
			"FundingOrganization", "ProjRole", "Project", "Task", "TaskRole" };

	public static final String GriidcDatabaseApplicationTables[] = { "Country",
			"Department-Telephone", "Department",
			"Dept-GoMRIPerson-Project-Role", "Dept-GoMRIPerson-Role-Task",
			"EmailInfo", "FundingEnvelope", "FundingOrganization",
			"GoMRIPerson-Department-RIS_ID", "GoMRIPerson", "GoMRIStudent",
			"Institution-Telephone", "Institution", "Person-Telephone",
			"Person", "PostalArea", "ProjRole", "Project", "Task", "TaskRole",
			"Telephone"
	/****
	 * ,
	 * 
	 * "pDataAccessPoint", "pDataFormat", "pDataFormat_VideoAttribute",
	 * "pDataGroup", "pDataMetadataStandard", "pDataObservation", "pDataset",
	 * "pDgDataAcquisitionMethod", "pDgDataClassification",
	 * "pDgDataNationalDataCenter", "pDsDataAcquisitionMethod",
	 * "pDsDataClassification", "pDsDataNationalDataCenter", "pMetadata",
	 * "pvDataAccessPoint", "pvDataFormat", "pvDataFormat_VideoAttribute",
	 * "pvDataGroup", "pvDataMetadataStandard", "pvDataObservation",
	 * "pvDataset", "pvDgDataAcquisitionMethod", "pvDgDataClassification",
	 * "pvDgDataNationalDataCenter", "pvDsDataAcquisitionMethod",
	 * "pvDsDataClassification", "pvDsDataNationalDataCenter", "pvMetadata",
	 * "tDataAccessPoint", "tDataFormat", "tDataFormat_VideoAttribute",
	 * "tDataGroup", "tDataMetadataStandard", "tDataObservation", "tDataset",
	 * "tDgDataAcquisitionMethod", "tDgDataClassification",
	 * "tDgDataNationalDataCenter", "tDsDataAcquisitionMethod",
	 * "tDsDataClassification", "tDsDataNationalDataCenter", "tMetadata",
	 * "tvDataAccessPoint", "tvDataFormat", "tvDataFormat_VideoAttribute",
	 * "tvDataGroup", "tvDataMetadataStandard", "tvDataObservation",
	 * "tvDataset", "tvDgDataAcquisitionMethod", "tvDgDataClassification",
	 * "tvDgDataNationalDataCenter", "tvDsDataAcquisitionMethod",
	 * "tvDsDataClassification", "tvDsDataNationalDataCenter", "tvMetadata"
	 ***/
	};

	public static TableColInfoCollection createTableColInfoCollection(
			RdbmsConnection conn, String[] tableNames) throws SQLException {
		TableColInfoCollection tciCollection = new TableColInfoCollection();
		for (String tName : tableNames) {
			TableColInfo tci = RdbmsUtils.createTableColInfo(conn, tName);
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

	public static TableColInfoCollection getGriidcTableColInfoCollection()
			throws SQLException {
		if (RdbmsUtils.GriidcTableColInfoCollection == null) {
			RdbmsUtils.createTableColInfoCollection(
					RdbmsUtils.getGriidcDbConnectionInstance(),
					RdbmsUtils.GriidcDatabaseApplicationTables);
		}
		return RdbmsUtils.GriidcTableColInfoCollection;
	}

	public static TableColInfoCollection getGriidcDefaultValueTableColInfoCollection()
			throws SQLException {
		if (RdbmsUtils.GriidcDefaultValueTableColInfoCollection == null) {
			RdbmsUtils.GriidcDefaultValueTableColInfoCollection = RdbmsUtils
					.getGriidcTableColInfoCollection()
					.getDefaultValuesTableColInfoCollection();
		}
		return RdbmsUtils.GriidcDefaultValueTableColInfoCollection;
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
		ResultSet rs = RdbmsUtils.getGriidcDbConnectionInstance()
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

	public static RisFundSrcProgramsStartEndCollection progFundSrcCollection = null;

	public static RisFundSrcProgramsStartEndCollection getRisFundSrcProgramsStartEndCollection()
			throws SQLException {
		return RdbmsUtils.startEndDateInRisPrograms();
	}

	private static RisFundSrcProgramsStartEndCollection startEndDateInRisPrograms()
			throws SQLException {
		if (RdbmsUtils.progFundSrcCollection == null) {
			String tableName = "Programs";
			String startCol = "Program_StartDate";
			String endCol = "Program_EndDate";
			String idCol = "Program_ID";
			String fundSrcCol = "Program_FundSrc";
			java.sql.Date startDate = null;
			java.sql.Date endDate = null;
			int programId = -1;
			int fundSrc = -1;
			RdbmsUtils.progFundSrcCollection = new RisFundSrcProgramsStartEndCollection();
			try {
				ResultSet rs = RdbmsUtils.getRisDbConnectionInstance()
						.selectAllValuesFromTable(tableName);
				while (rs.next()) {
					try {
						fundSrc = rs.getInt(fundSrcCol);
						programId = rs.getInt(idCol);
						startDate = rs.getDate(startCol);
						endDate = rs.getDate(endCol);
						progFundSrcCollection.addRisProgramStartEnd(fundSrc,
								programId, startDate, endDate);
						if (RdbmsUtils.isDebug())
							System.out.println("Fund_Src: " + fundSrc
									+ ", Program ID: " + programId
									+ ", start: " + startDate + ", end: "
									+ endDate);
					} catch (SQLException e) {
						String msg = "RIS Error: Fund Src: " + fundSrc
								+ ", Program ID: " + programId + " - "
								+ e.getMessage();
						if (RdbmsUtils.isDebug())
							System.err.println(msg);
						MiscUtils.writeToErrorLogFile(msg);
						continue;
					}
				}
			} catch (TableNotInDatabaseException e) {
				System.err
						.println("TableNotInDatabaseException in RdbmsUtils.startEndDateInRisPrograms() table name: "
								+ tableName);
				System.err.println("exception: " + e.getMessage());
				System.exit(-1);
			}
		}
		return RdbmsUtils.progFundSrcCollection;
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
	 * get the text description of a constraint clause. Used to provide a human
	 * readable error message.
	 * 
	 * @param args
	 * @throws SQLException 
	 */
	/* <><><><>
	 * 
	 * BROKEN dont use. Returns zero rows in the result set.
	 <><><><><>*/
	public static String getCheckClause(String tableName, String constraintName) throws SQLException {
		String q1 = "SELECT  " +
				// table_name, column_name, 
				// " check_clause " +
				"count(*) " + 
				" FROM information_schema.check_constraints "
				//+ " INNER JOIN "
				//+ " information_schema.constraint_column_usage on "
				//+ " information_schema.check_constraints.constraint_name = information_schema.constraint_column_usage.constraint_name "
				// + " where TABLE_NAME = "
			//	+ RdbmsConnection.wrapInSingleQuotes(tableName)
				//+ " AND "
				//+ " information_schema.constraint_column_usage.constraint_name = "
				//+ RdbmsConnection.wrapInSingleQuotes(constraintName)
				;

		System.out.println("getCheckClause() query: " + q1);
		ResultSet rset = RdbmsUtils.getGriidcSecondaryDbConnectionInstance().executeQueryResultSet(q1);
		
		String cn = null;
		String tn = null;
		String cc = null;
		
        int rows = 0;
        ResultSetMetaData rsmd = rset.getMetaData();
        int colCount = rsmd.getColumnCount();
        System.out.println("meta data column count: " + colCount);
        for(int i = 0; i < colCount;i++) {
        	int j = i+1;
        	String n = rsmd.getColumnName(j);
        	String t = rsmd.getColumnTypeName(j);
        	System.out.println("Col: " + j + " name: " + n +  ", type: " + t);
        }
		while (rset.next()) {
			rows++;
		//	tn = rset.getString("table_name");
		//	cn = rset.getString("column_name");
		//	cc = rset.getString("check_clause");
			System.out.println(rset.getInt(1));
		}
		System.out.println("getCheckClause() rows: " + rows);
		return cc;
	}

	public static void main(String[] args) {

		System.out.println("Rdbmsutils.main() - Start -");
		RisFundSrcProgramsStartEndCollection foo = null;
		int[] fundId = { 2, 4, 8, 9, 12 }; // 12 is bad
		int[] progId = { 10, 30, 80, 85, 150, 170, 220 };
		String[] fundSrc = { "FIO", "LSU", "MESC", "JVH", "NGI", "NIH",
				"RFP-I", "JTCH", "RFP-II", "RFP-III" };
		String fundCycle = null;
		for (String fs : fundSrc) {
			try {
				fundCycle = RdbmsUtils
						.convertRisFundingSourceToGriidcFormat(fs);
				System.out.println("Converted " + fs + " to " + fundCycle);
			} catch (IllegalFundingSourceCodeException e) {
				System.err.println(e.getMessage());
			}
		}
		try {
			// tcic =
			// RdbmsUtils.getAllDataFromTable(RdbmsUtils.getGriidcDbConnectionInstance(),"FundingOrganization");
			// System.out.println(tcic.toString());
			foo = RdbmsUtils.startEndDateInRisPrograms();
			RisProgramStartEnd rpse = null;
			String msg = null;
			System.out.println("\nStart/End fund & program:\n"
					+ foo.toStringBrief());
			for (int i = 0; i < fundId.length; i++) {
				rpse = foo.getFundSourceStartEndDate(fundId[i]);
				msg = "NULL";
				if (rpse != null)
					msg = rpse.toString();
				System.out.println(" For Fund: " + fundId[i] + " min/max "
						+ msg);
			}
			for (int i = 0; i < fundId.length; i++) {
				for (int j = 0; j < progId.length; j++) {
					rpse = foo.getFundSourceProgramStartEndDate(fundId[i],
							progId[j]);
					msg = "NULL";
					if (rpse != null)
						msg = rpse.toString();
					System.out.println(" For Fund: " + fundId[i] + ", Prog: "
							+ progId[j] + " min/max " + msg);
				}
			}
			String TableName = "Person-Telephone";
			TableColInfo tci = RdbmsUtils.createTableColInfo(
					RdbmsUtils.getGriidcDbConnectionInstance(), TableName);
			System.out.println(tci);
			stripDefaultValue(tci.getDbColumnInfo("Telephone_Type")
					.getDefaultValue().getValue());

		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		String tableName = "FundingEnvelope";
		String constraintName = "chk_FundingEnvelope_StartDate";
		try {
			String cl = RdbmsUtils.getCheckClause(tableName, constraintName);
			System.out.println("Table: " + tableName + ", constraint: "
					+ constraintName + ", text: " + cl);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		System.out.println("Rdbmsutils.main() - END -");
	}
}
