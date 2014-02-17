package edu.tamucc.hri.rdbms.utils;

import java.io.BufferedWriter;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.SQLException;
import java.sql.Statement;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Vector;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.MiscUtils;

public class RdbmsConnection {

	private String rdbmsType = null;
	private String rdbmsHost = null;
	private String rdbmsPort = null;
	private String rdbmsUrl = null;
	private String rdbmsUser = null;
	private String rdbmsPassword = null;
	private String rdbmsName = null;
	private String rdbmsSchemaName = null;
	private String rdbmsJdbcDriverName = null;
	private String rdbmsJdbcPrefix = null;
	private static boolean Debug = false;
	private static String DebugPrefix = ">>>>>  ";

	private Connection connection = null;
	private Statement statement = null;

	private static final String DateFormatString = "MM/dd/yyyy";
	private static final SimpleDateFormat LocalDateFormat = new SimpleDateFormat(
			DateFormatString);

	private BufferedWriter mainOutput = null;
	private BufferedWriter exceptionOutput = null;

	private String[] allTablesInDatabase = null;

	private GarbageDetector garbageDetector = new GarbageDetector();

	public RdbmsConnection() {

	}

	@Override
	public String toString() {
		return "RdbmsConnection [rdbmsType=" + rdbmsType + ", rdbmsHost="
				+ rdbmsHost + ", rdbmsPort=" + rdbmsPort + ", rdbmsUrl="
				+ rdbmsUrl + ", rdbmsUser=" + rdbmsUser + ", rdbmsPassword="
				+ rdbmsPassword + ", rdbmsName=" + rdbmsName
				+ ", rdbmsSchemaName=" + rdbmsSchemaName
				+ ", rdbmsJdbcDriverName=" + rdbmsJdbcDriverName
				+ ", rdbmsJdbcPrefix=" + rdbmsJdbcPrefix + "]";
	}
	public String getShortDescription() {
		return "RdbmsConnection [rdbmsType=" + rdbmsType + ", rdbmsHost="
				+ rdbmsHost + ", rdbmsUrl="
				+ rdbmsUrl + ", rdbmsUser=" + rdbmsUser + ", rdbmsName=" + rdbmsName
				+ ", rdbmsSchemaName=" + rdbmsSchemaName
				+  "]";
	}
	public RdbmsConnection(String jdbcDriverName, String dbType, String host,
			String port, String user, String password, String dbName,
			String schemaName, String prefix) {
		super();
		this.rdbmsType = dbType;
		this.rdbmsHost = host;
		this.rdbmsPort = port;
		this.rdbmsUser = user;
		this.rdbmsPassword = password;
		this.rdbmsName = dbName;
		this.rdbmsSchemaName = schemaName;
		this.rdbmsJdbcDriverName = jdbcDriverName;
		this.rdbmsJdbcPrefix = prefix;
	}

	public String getDbType() {
		return rdbmsType;
	}

	public String getDbHost() {
		return rdbmsHost;
	}

	public String getDbPort() {
		return rdbmsPort;
	}

	public String getDbUrl() {
		return rdbmsUrl;
	}

	public String getDbUser() {
		return rdbmsUser;
	}

	public String getDbPassword() {
		return rdbmsPassword;
	}

	public String getDbName() {
		return rdbmsName;
	}

	public String getDbSchemaName() {
		return rdbmsSchemaName;
	}

	public String getDbDriverName() {
		return rdbmsJdbcDriverName;
	}

	public String getJdbcPrefix() {
		return rdbmsJdbcPrefix;
	}

	public static String getDebugPrefix() {
		return DebugPrefix;
	}

	/**
	 * db.url=//hydra1.lanl.gov/ db.name=ExtendedHydraOutput
	 * geoserver.admin.upload.url=hydra1.lanl.gov:8080
	 * geoserver.admin.name=geoserver-admin
	 * geoserver.admin.desc=StatelessAdministrator
	 * geoserver.host=hydra1.lanl.gov geoserver.user=admin
	 * geoserver.password=Hydr@ geoserver.store=ExtendedHydraOutput
	 * 
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 */
	public Connection setConnection(String dbType, String driverName,
			String jdbcPrefix, String host, String port, String dbName,
			String dbSchema, String dbUser, String dbPassword)
			throws SQLException, ClassNotFoundException {

		// debugMessage("RdbmsConnection.setConnection - dbType: " + dbType);
		// debugMessage("RdbmsConnection.setConnection - jdbcDriverName: "
		// + driverName);
		// debugMessage("RdbmsConnection.setConnection - jdbcPrefix: " +
		// jdbcPrefix);
		// debugMessage("RdbmsConnection.setConnection - host: " + host);
		// debugMessage("RdbmsConnection.setConnection - port: " + port);
		// debugMessage("RdbmsConnection.setConnection - dbName: " + dbName);
		// debugMessage("RdbmsConnection.setConnection - schema: " + dbSchema);
		// debugMessage("RdbmsConnection.setConnection - dbUser: " + dbUser);
		// debugMessage("RdbmsConnection.setConnection - dbPassword: " +
		// dbPassword);

		DriverManager dm = null;
		this.rdbmsJdbcDriverName = driverName;
		this.rdbmsJdbcPrefix = jdbcPrefix;
		this.rdbmsHost = host;
		this.rdbmsPort = port;
		this.rdbmsName = dbName;
		this.rdbmsSchemaName = dbSchema;
		this.rdbmsUser = dbUser;
		this.rdbmsPassword = dbPassword;
		this.rdbmsType = dbType;

		Class.forName(this.rdbmsJdbcDriverName);
		String url = RdbmsConnection.getDatabaseUrl(jdbcPrefix, host, port,
				dbName);
		debugMessage("\nThe database url: " + url + "," + dbUser + ","
				+ dbPassword);
		this.connection = DriverManager.getConnection(url, dbUser, dbPassword);
		getStatement();
		return this.getConnection();
	}

	public Connection getConnection() throws SQLException,
			ClassNotFoundException {
		if (this.connection == null)
			this.connection = this.setConnection(this.rdbmsType,
					this.rdbmsJdbcDriverName, this.rdbmsJdbcPrefix,
					this.rdbmsHost, this.rdbmsPort, this.rdbmsName,
					this.rdbmsSchemaName, this.rdbmsUser, this.rdbmsPassword);
		return this.connection;
	}

	public static String getDatabaseUrl(String prefix, String host,
			String port, String dbName) {
		return prefix + "://" + host + ":" + port + "/" + dbName;
	}

	private Statement getStatement() throws SQLException,
			ClassNotFoundException {
		if (this.statement == null)
			this.statement = this.getConnection().createStatement();
		return this.statement;
	}

	public int executeSql(String queryString) throws SQLException,
			ClassNotFoundException {
		int status = 0;
		ResultSet resultSet = null;
		try {
			resultSet = this.executeQueryResultSet(queryString);

			long stopTime = -1;
			String exceptionMsg = "";
			String metaInformation = "";

			while (resultSet.next()) {

				exceptionMsg = "";

				// meta info might not be set
				metaInformation = "";

			}

		} catch (SQLException e) {
			debugMessage("printProducts SQLException " + e.getMessage());
			e.printStackTrace();
		}
		return status;
	}

	public static void setDebug(boolean trueOrFalse) {
		Debug = trueOrFalse;
	}

	/**
	 * execute the query argument - no checking on syntax prior to execution
	 * 
	 * @param query
	 * @return
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 */
	public ResultSet executeQueryResultSet(String query) throws SQLException,
			ClassNotFoundException {

		// if (Debug)
		// debugMessage("\texecuteQueryResultSet() - query is >" + query + "<");
		ResultSet resultSet = this.getStatement().executeQuery(query);
		// statement.close();
		return resultSet;
	}

	public String[] executeQueryResultSetAsStringArray(String query)
			throws SQLException, ClassNotFoundException {
		StringArrayResultSet sars = executeQueryReturnStringArrayResultSet(query);
		return sars.getTable();
	}

	public StringArrayResultSet executeQueryReturnStringArrayResultSet(
			String query) throws SQLException, ClassNotFoundException {
		ResultSet rs = executeQueryResultSet(query);
		ResultSetMetaData metaData = rs.getMetaData();
		int colCount = metaData.getColumnCount();
		String[] colNames = new String[colCount];
		String[] colTypes = new String[colCount];
		for (int i = 0; i < colCount; i++) {
			colNames[i] = metaData.getColumnName(i + 1);
			colTypes[i] = metaData.getColumnTypeName(i + 1);
		}
		StringArrayResultSet sars = new StringArrayResultSet("\t", colNames,
				colTypes);
		sars.addResultSet(rs);
		return sars;
	}

	public String[] resultSetToStringArray(ResultSet rs) throws SQLException {
		String[] sa = new String[rs.getMetaData().getColumnCount()];
		return sa;
	}

	/**
	 * execute the query argument - no checking on syntax prior to execution
	 * 
	 * @param query
	 * @return
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 */
	public boolean executeQueryBoolean(String query) throws SQLException,
			ClassNotFoundException {
		// if (Debug)
		// debugMessage(DebugPrefix + "\texecuteQueryBoolean() - query is "
		// + query);
		boolean result = this.getStatement().execute(query);
		return result;
	}

	public int executeUpdate(String query) throws SQLException,
			ClassNotFoundException {
		// if (Debug)
		// debugMessage(DebugPrefix + "\texecuteUpdate() - query is " + query);
		int updateCount = this.getStatement().executeUpdate(query);
		return updateCount;
	}

	public static String getFirstAlpha(String s) {
		char c = 0;
		StringBuffer sb = null;
		for (int i = 0; i < s.length(); i++) {
			c = s.charAt(i);
			if ((c >= 'A' && c <= 'Z') || (c >= 'a' && c <= 'z')) {
				sb = new StringBuffer();
				sb.append(c);
				return sb.toString();
			}
		}
		return null;
	}

	/**
	 * db.url=//hydra1.lanl.gov/ db.name=ExtendedHydraOutput
	 * geoserver.admin.upload.url=hydra1.lanl.gov:8080
	 * geoserver.admin.name=geoserver-admin
	 * geoserver.admin.desc=StatelessAdministrator
	 * geoserver.host=hydra1.lanl.gov geoserver.user=admin
	 * geoserver.password=Hydr@ geoserver.store=ExtendedHydraOutput
	 */
	private void loadDatabaseDriver(String jdbcDriverName) {
		try {
			Class.forName(jdbcDriverName);
		} catch (ClassNotFoundException e) {
			System.err
					.println("this.loadDatabaseDriver() - ClassNotFoundException for jdbc driver : "
							+ jdbcDriverName);
			e.printStackTrace();
		}
	}

	/**
	 * start a transaction and lock all the tables that are to be modified
	 * 
	 * @throws ClassNotFoundException
	 */
	public void beginTransaction() throws SQLException, ClassNotFoundException {
		String query = "BEGIN TRANSACTION;";
		this.executeQueryBoolean(query);
	}

	/**
	 * lock all the tables
	 * 
	 * @throws ClassNotFoundException
	 */
	public void lockTable(String tableName) throws SQLException,
			ClassNotFoundException {
		final String prefix = "LOCK TABLE ";
		final String sufix = "  IN SHARE ROW EXCLUSIVE MODE; ";

		int i = 0;
		String query = prefix + wrapInDoubleQuotes(tableName) + sufix;
		this.executeQueryBoolean(query);
	}

	/**
	 * create a table
	 * 
	 * @throws ClassNotFoundException
	 */
	public void crateTable(String tableName, String[] columnNames, String[] type)
			throws SQLException, ClassNotFoundException {

		StringBuffer query = new StringBuffer("CREATE TABLE " + tableName + "(");

		for (int i = 0; i < columnNames.length; i++) {
			query.append(columnNames[i] + "  " + type[i]);
			if (i < (columnNames.length - 1))
				query.append(",");
		}
		query.append(");");
		this.executeQueryBoolean(query.toString());
	}

	/**
	 * delete a table
	 * 
	 * @throws ClassNotFoundException
	 */
	public void dropTable(String tableName) throws SQLException,
			ClassNotFoundException {
		String query = "DROP TABLE " + tableName;
		this.executeQueryBoolean(query);
	}

	public void commitTransaction() throws SQLException, ClassNotFoundException {

		String query = "COMMIT TRANSACTION;";
		this.executeQueryBoolean(query);
	}

	/**
	 * abort the transaction - which unlocks all tables
	 * 
	 * @throws ClassNotFoundException
	 */
	public void abortTransaction() throws SQLException, ClassNotFoundException {

		String query = "ABORT TRANSACTION;";
		this.executeQueryBoolean(query);
	}

	private String formatDate(long dateL) {
		return RdbmsConnection.LocalDateFormat.format(new Date(dateL));
	}

	/**
	 * a string for creating SimpleDateFormat
	 * 
	 * @return
	 */
	public String getDateFormatString() {
		return RdbmsConnection.DateFormatString;
	}

	/**
	 * process the string results from the dream output into the values needed
	 * for the plotting service
	 * 
	 * @param results
	 */
	/****
	 * private String formatDateToken(String date, String time) { StringBuffer
	 * sb = new StringBuffer(); // date contains yyyymmdd String year =
	 * date.substring(0, 4); String month = date.substring(4, 6); String
	 * dayOfMonth = date.substring(6, 8);
	 * 
	 * sb.append(year); sb.append("-"); sb.append(month); sb.append("-");
	 * sb.append(dayOfMonth); sb.append(" "); sb.append(time); return
	 * sb.toString(); }
	 ****/

	/**
	 * 20021101 0.0 put the date in the format needed for the dream input file
	 * that is yyyymmdd h.h
	 * 
	 * h.h is decimal hours in 24 hour clock
	 * 
	 * @param date
	 * @return
	 */

	private InputStream getFileInputStream(String path) {
		FileInputStream inputStream = null;
		try {
			inputStream = new FileInputStream(path);
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return inputStream;
	}

	public static String getWorkingDirectory() {
		return System.getProperty("user.dir");
	}

	public String[] getTableNamesForDatabase() throws SQLException,
			ClassNotFoundException, FileNotFoundException {
		if (this.allTablesInDatabase == null) {
			String query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES";
			debugMessage("RdbmsConnection:getTableNamesForDatabase() - query: "
					+ query);
			ResultSet rs = this.executeQueryResultSet(query);
			Vector<String> v = new Vector<String>();

			while (rs.next()) {
				v.add(rs.getString(1).trim());
			}
			this.allTablesInDatabase = new String[v.size()];
			this.allTablesInDatabase = v.toArray(this.allTablesInDatabase);
		}
		return this.allTablesInDatabase;
	}
	
	public boolean isTableInDatabase(String tableName) throws FileNotFoundException, SQLException, ClassNotFoundException, TableNotInDatabaseException {
		String[] tabNames = this.getTableNamesForDatabase();
		for(String tn: tabNames) {
			if(tableName.trim().equals(tn)) return true;
		}
		String msg = "In Database: " + this.getDbName() + " table: " + tableName + " does not exist.";
		this.debugMessage(msg);
		throw new TableNotInDatabaseException(msg);
	}

	public String[] getColumnNamesFromTable(String tableName)
			throws FileNotFoundException, SQLException, ClassNotFoundException, TableNotInDatabaseException {

		this.isTableInDatabase(tableName);
		String query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "
				+ RdbmsConnection.wrapInSingleQuotes(tableName);
		String[] output = null;

		debugMessage("getColumnNamesFromTable() - query: " + query);
		ResultSet rs = this.executeQueryResultSet(query);
		Vector<String> colName = new Vector<String>();

		while (rs.next()) {
			colName.add(rs.getString(1));
		}
		output = new String[colName.size()];
		output = colName.toArray(output);
		return output;
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
	 * @throws ClassNotFoundException
	 * @throws TableNotInDatabaseException 
	 */
	public String[][] getColumnNamesAndDataTypesFromTable(String tableName)
			throws FileNotFoundException, SQLException, ClassNotFoundException, TableNotInDatabaseException {
		this.isTableInDatabase(tableName);
		String query = "SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "
				+ RdbmsConnection.wrapInSingleQuotes(tableName);
		String[] colOut = null;
		String[] typeOut = null;
		debugMessage("getColumnNamesAndDataTypeFromTable() - query: " + query);
		Vector<String> colName = new Vector<String>();
		Vector<String> colDType = new Vector<String>();

		int count = 0;
		String cName = null;
		String dType = null;
		ResultSet rs = this.executeQueryResultSet(query);
		while (rs.next()) {
			cName = rs.getString(1);
			dType = rs.getString(2);
			colName.add(cName);
			colDType.add(dType);
			debugMessage("\trs[" + count + "] = " + cName + "  -  " + dType);
			count++;
		}
		debugMessage("\tRS count: " + count);
		colOut = new String[colName.size()];
		typeOut = new String[colDType.size()];
		colOut = colName.toArray(colOut);
		typeOut = colDType.toArray(typeOut);
		String[][] allOut = new String[2][];
		allOut[0] = colOut;
		allOut[1] = typeOut;
		return allOut;
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
	 * @throws ClassNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws TableNotInDatabaseException 
	 */
	public String[] getColumnDefaultValue(String tableName)
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException, TableNotInDatabaseException {

		this.isTableInDatabase(tableName);
		String query = "SELECT column_default FROM information_schema.columns WHERE table_name = "
				+ RdbmsConnection.wrapInSingleQuotes(tableName);
		ResultSet rset = this.executeQueryResultSet(query);
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

	public ResultSet selectAllValuesFromTable(String tableName)
			throws SQLException, ClassNotFoundException, FileNotFoundException, TableNotInDatabaseException {
		this.isTableInDatabase(tableName);
		StringBuffer query = new StringBuffer("select * from ");
		if (this.getDbType().equals("postgres")) {

			query.append(RdbmsConnection.wrapInDoubleQuotes(this
					.getDbSchemaName()));
			query.append(".");
			query.append(RdbmsConnection.wrapInDoubleQuotes(tableName));
		} else { // - we presume mysql
			// query.append(RdbmsConnection.wrapInSingleQuotes(tableName));
			query.append(tableName);
		}
		debugMessage("RdbmsConnection.selectAllValuesFromTable(" + tableName
				+ ") query is " + query.toString());
		return this.executeQueryResultSet(query.toString());
	}

	public String[] processResultSet(ResultSet rset, String[] colNames) {

		debugMessage(DebugPrefix + "\tprocessResultSet() ");

		StringBuffer sb = null;
		Vector<String> v = new Vector<String>();
		try {
			while (rset.next()) {
				if (Debug)
					debugMessage(DebugPrefix + "result set has next");
				sb = new StringBuffer();
				for (String cname : colNames) {
					if (Debug)
						debugMessage(DebugPrefix + "colName: " + cname);

					String temp;
					try {
						temp = rset.getString(cname);
					} catch (SQLException e) {
						temp = "UNKNOWN";
						System.err.println("SQL Exception on query: col name: "
								+ cname + " - " + e.getMessage());
					}
					sb.append(temp);
					sb.append("\t");
				}
				v.add(sb.toString());
			}
		} catch (SQLException e) {
			e.printStackTrace();
		}
		String[] strings = new String[v.size()];
		v.toArray(strings);
		return strings;
	}

	public boolean doesTableExist(String targetTableName)
			throws FileNotFoundException, SQLException, ClassNotFoundException {
		String[] tableNames = this.getTableNamesForDatabase();
		String target = targetTableName.trim();
		for (String tName : tableNames) {
			if (tName.trim().equals(target))
				return true;
		}
		return false;
	}

	public void interogateResultSet(ResultSet rs) throws SQLException {
		debugMessage("\ninterogateResultSet");
		ResultSetMetaData rsmd = rs.getMetaData();
		String tableName = rsmd.getTableName(1);
		debugMessage("Table Name: " + tableName);
		int colCount = rs.getMetaData().getColumnCount();
		debugMessage("Column count: " + colCount);
		int ndx = 1;
		for (int i = 0; i < colCount; i++) {
			ndx = i + 1;
			String colName = rs.getMetaData().getColumnName(ndx);
			debugMessage("\tcol name: " + colName);
		}

		String value = null;
		while (rs.next()) {
			for (int i = 0; i < colCount; i++) {
				ndx = i + 1;
				value = rs.getString(ndx);
				debugMessage("\t" + value);
			}
		}
	}

	public String getFormatedDbInfo() {
		StringBuffer sb = new StringBuffer();
		sb.append("jdbcDriverName: " + this.getDbDriverName());
		sb.append("\njdbcPrefix: " + this.getJdbcPrefix());
		sb.append("\nRdbmsConnection.readDatabase - host: " + this.getDbHost());
		sb.append("\nRdbmsConnection.readDatabase - port: " + this.getDbPort());
		sb.append("\nRdbmsConnection.readDatabase - dbName: "
				+ this.getDbName());
		sb.append("\nRdbmsConnection.readDatabase - dbUser: "
				+ this.getDbUser());
		sb.append("\nRdbmsConnection.readDatabase - dbPassword: "
				+ this.getDbPassword());
		return sb.toString();
	}

	public void readDatabase(final String[] targetTables) throws SQLException,
			ClassNotFoundException, TableNotInDatabaseException {

		System.out.println("\n" + getFormatedDbInfo());
		try {

			// this.switchedWriterln("\n\nenter to continue");
			// String myint = keyboard.nextLine();

			System.out.println("Main: dbName = >>" + this.getDbName() + "<<");

			System.out.println("db connection: " + this.toString());

			// System.out.println("\n\nenter to continue");
			// /myint = keyboard.nextLine();

			String msg = null;
			String[] tableNames = targetTables;
			if (targetTables == null || targetTables.length == 0) {
				tableNames = this.getTableNamesForDatabase();
				msg = "reading all " + tableNames.length + " from database "
						+ this.getDbName();
				this.writeLineToFile(msg);
				System.out.println(msg);
				this.debugMessage(msg);

			} else {
				msg = "reading " + tableNames.length
						+ " selected tables from database " + this.getDbName();
				this.writeLineToFile(msg);
				System.out.println(msg);
				this.debugMessage(msg);
			}

			for (String t : tableNames) {
				String outputFileName = this.getDbName() + "-" + t;
				this.mainOutput = MiscUtils.openOutputFile(outputFileName);
				String[] colNames = this.getColumnNamesFromTable(t);
				ResultSet rs = this.selectAllValuesFromTable(t);
				String[] lines = this.processResultSet(rs, colNames);
				// print headers
				this.writeLineToFile("\nTable: " + t);
				StringBuffer sb = new StringBuffer();
				for (String colN : colNames) {
					sb.append(colN + "\t");
				}
				this.writeLineToFile(sb.toString());

				for (String l : lines) {
					if (this.garbageDetector.hasHtmlCode(l))
						this.writeQcException(l, t);
					else
						this.writeLineToFile(l);
				}
				// System.out.println("\n\nenter to continue");
				// myint = keyboard.nextLine();
				if (!(this.exceptionOutput == null)) {
					this.exceptionOutput.close();
					this.exceptionOutput = null;
				}
				if (!(this.mainOutput == null)) {
					this.mainOutput.close();
					this.mainOutput = null;
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
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	public void reportTableAndColumnNames(final String[] targetTables)
			throws SQLException, ClassNotFoundException, IOException, TableNotInDatabaseException {
		String outputFileName = this.getDbName()
				+ "TablesAndColumnNamesOut.txt";
		try {
			BufferedWriter localBw = MiscUtils.openOutputFile(outputFileName);

			localBw.write("db connection: " + this.toString());

			// MiscUtils.writeIt(bw,"\n\nenter to continue");
			// /myint = keyboard.nextLine();

			String msg = null;
			String[] tableNames = targetTables;

			if (targetTables == null || targetTables.length == 0) {
				tableNames = this.getTableNamesForDatabase();
				msg = "reading all " + tableNames.length
						+ " tables from database " + this.getDbName();
				localBw.write(msg);
				this.debugMessage(msg);

			} else {
				msg = "reading " + tableNames.length
						+ " selected tables from database " + this.getDbName();
				localBw.write(msg);
				this.debugMessage(msg);
			}

			for (String t : tableNames) {
				String[] colNames = this.getColumnNamesFromTable(t);
				localBw.write("\nTable: " + t);

				StringBuffer sb = new StringBuffer();
				for (String colNam : colNames) {
					sb.append(colNam + "\t");
				}
				localBw.write(sb.toString());

			}
			System.out.println("Table And Column Names Output written to: "
					+ MiscUtils.getAbsoluteFileName(outputFileName));
			localBw.close();
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

		return;
	}

	public void reportTableColumnNamesAndDataType(final String[] targetTables)
			throws SQLException, ClassNotFoundException, IOException, TableNotInDatabaseException {

		try {

			String outputFileName = MiscUtils.getAbsoluteFileName(this
					.getDbName() + "TablesColumnsAndDataTypesOut.txt");
			BufferedWriter localBw = MiscUtils.openOutputFile(outputFileName);

			localBw.write("\n" + this.getFormatedDbInfo());

			localBw.write("db connection: " + this.toString());

			// MiscUtils.writeIt(bw,"\n\nenter to continue");
			// /myint = keyboard.nextLine();

			String msg = null;
			String[] tableNames = targetTables;

			if (targetTables == null || targetTables.length == 0) {
				tableNames = this.getTableNamesForDatabase();
				msg = "reading all " + tableNames.length
						+ " tables from database " + this.getDbName();
				localBw.write(msg);
				this.debugMessage(msg);

			} else {
				msg = "reading " + tableNames.length
						+ " selected tables from database " + this.getDbName();
				localBw.write(msg);
				this.debugMessage(msg);
			}

			String formatString2 = "%-30s%-10s%n";
			localBw.write("Database: " + this.rdbmsName);
			System.out.println("Database: " + this.rdbmsName);
			for (String t : tableNames) {
				String[][] colNamesAndDataType = this
						.getColumnNamesAndDataTypesFromTable(t);
				localBw.write("\nTable: " + t + "\n");
				System.out.println("\n" + " For Table: " + t + "\n");

				String[] colNames = colNamesAndDataType[0];
				String[] typeNames = colNamesAndDataType[1];

				for (int i = 0; i < colNames.length; i++) {
					localBw.write(String.format(formatString2,
							colNames[i].trim(), typeNames[i].trim()));
					System.out.format(formatString2, colNames[i].trim(),
							typeNames[i].trim());
				}
			}

			System.out
					.println("Table Column Names And Data Type Output written to: "
							+ outputFileName);
			localBw.close();

		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	private void writeQcException(String line, String tableName)
			throws IOException {
		if (this.exceptionOutput == null) {
			this.exceptionOutput = MiscUtils.openOutputFile(this.getDbName()
					+ "-" + tableName + "Exceptions");
		}
		this.exceptionOutput.write("\n" + line);
	}

	private void writeLineToFile(String line) throws IOException {
		if (this.mainOutput != null)
			this.mainOutput.write("\n" + line);
		// System.out.println(line);
	}

	private void debugMessage(String line) {
		if (Debug)
			System.out.println("\nRdbmsConnection: " + line);
	}

	public String[] getSchemaNames() throws SQLException {
		java.sql.DatabaseMetaData meta = this.connection.getMetaData();
		ResultSet schemas = meta.getSchemas();
		Vector<String> vs = new Vector<String>();
		while (schemas.next()) {
			String tableSchema = schemas.getString(1); // "TABLE_SCHEM"
			vs.add(tableSchema);
			String tableCatalog = schemas.getString(2); // "TABLE_CATALOG"
			// System.out.println("tableSchema "+tableSchema);
		}
		String[] sss = new String[vs.size()];
		sss = vs.toArray(sss);
		return sss;
	}

	public void closeConnection() throws SQLException {
		this.connection.close();
	}

	/**
	 * A convenience function to wrap stings in single quotes for
	 * 
	 * @param token
	 * @return
	 */
	public static String wrapInSingleQuotes(final String str) {
		if (str == null)
			return null;
		if (needsDollarQuotes(str)) {
			return wrapInDollarQuotes(str);
		}
		return "'" + str + "'";
	}
    private static boolean needsDollarQuotes(final String str) {
    	for(String tkn: badTokens) {
    		if (str.contains(tkn)) return true;
    	}
    	return false;
    }
	//  if the data contains any of these characters wrap the string in dollar quotes
	public static String[] badTokens = {
		"\'",  // single quote
		"/",
		"<",
		">"
	};
	public static String wrapInDollarQuotes(final String messyString) {
		String dollarQuote = "$jvh$";
		return dollarQuote + messyString + dollarQuote;
	}

	public static String wrapInDoubleQuotes(final String token) {
		return "\"" + token + "\"";
	}

	/**
	 * find all the " ' " characters (single quote - Apostrophe ) and stuff an
	 * escape character before it
	 * 
	 * @param s
	 * @return
	 */
	public static String escapeApostrophe(final String uString) {
		/*******
		 * String pString = uString; String target = "\'"; String replacement =
		 * "#"; String escapedString = "\\\'"; while(pString.contains("\'")) {
		 * pString = pString.replace(target, replacement); }
		 * 
		 * while(pString.contains(replacement)) { pString =
		 * pString.replace(replacement, escapedString); }
		 *******/
		return RdbmsConnection.escapeSpecialCharacter("\'", uString);
	}

	public static String escapeParens(final String uString) {
		String pString = uString;
		pString = RdbmsConnection.escapeSpecialCharacter("(", pString);
		pString = RdbmsConnection.escapeSpecialCharacter(")", pString);
		return pString;
	}

	public static String escapeSpecialCharacter(String specChar,
			final String uString) {
		String pString = uString;
		String target = specChar;
		String replacement = "#";
		String escapedString = "\\\\" + specChar;
		while (pString.contains(specChar)) {
			pString = pString.replace(target, replacement);
		}
		while (pString.contains(replacement)) {
			pString = pString.replace(replacement, escapedString);
		}
		return pString;
	}

	public static void main(String[] args) {
		String[] risTableNames = { "Institutions", "Departments", "People" };
		String[] griidcTableNames = { "Institution", "Department", "Person",
				"Institution-Telephone" };
		System.out.println("RdbmsConnection.main() - Start -");
		GriidcPgsqlEnumType gpet = new GriidcPgsqlEnumType();
		RdbmsConnection.setDebug(false);
		try {
			RdbmsUtils.setDebug(true);
			String fileName = RdbmsUtils.getRisDbConnectionInstance()
					.getDbName() + "TableColTypeReport.txt";
			String s = RdbmsUtils.getColumnNamesAndDataTypesFromTables(
					RdbmsUtils.getRisDbConnectionInstance(), risTableNames);
			MiscUtils.writeStringToFile(fileName, s);

			fileName = RdbmsUtils.getGriidcDbConnectionInstance().getDbName()
					+ "TableColTypeReport.txt";
			s = RdbmsUtils.getColumnNamesAndDataTypesFromTables(
					RdbmsUtils.getGriidcDbConnectionInstance(),
					griidcTableNames);
			MiscUtils.writeStringToFile(fileName, s);

			System.out.println("\nGRIIDC Table Names");

			for (String tabName : griidcTableNames) {
				System.out.println("\n" + tabName);
				String[] defaults = RdbmsUtils.getGriidcDbConnectionInstance()
						.getColumnDefaultValue(tabName);
				String[] colNames = RdbmsUtils.getGriidcDbConnectionInstance()
						.getColumnNamesFromTable(tabName);

				for (int i = 0; i < defaults.length && i < colNames.length; i++) {
					System.out.println("\tcol: " + colNames[i] + "\tdefault: "
							+ defaults[i]);
				}
			}

		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (TableNotInDatabaseException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

		System.out.println("Rdbmsutils.main() - END -");
	}
}
