package edu.tamucc.hri.rdbms.utils;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.sql.Array;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.SQLException;
import java.sql.Statement;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Vector;

import com.mysql.jdbc.DatabaseMetaData;

import edu.tamucc.hri.griidc.RisPropertiesAccess;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

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

		errorMsg("RdbmsConnection.setConnection - dbType: " + dbType);
		errorMsg("RdbmsConnection.setConnection - jdbcDriverName: "
				+ driverName);
		errorMsg("RdbmsConnection.setConnection - jdbcPrefix: " + jdbcPrefix);
		errorMsg("RdbmsConnection.setConnection - host: " + host);
		errorMsg("RdbmsConnection.setConnection - port: " + port);
		errorMsg("RdbmsConnection.setConnection - dbName: " + dbName);
		errorMsg("RdbmsConnection.setConnection - schema: " + dbSchema);
		errorMsg("RdbmsConnection.setConnection - dbUser: " + dbUser);
		errorMsg("RdbmsConnection.setConnection - dbPassword: " + dbPassword);

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

		errorMsg("getConnection() Class.forName(" + this.rdbmsJdbcDriverName
				+ ")");
		Class.forName(this.rdbmsJdbcDriverName);
		String url = RdbmsConnection.getDatabaseUrl(jdbcPrefix, host, port,
				dbName);
		errorMsg("\nThe database url: " + url + "," + dbUser + "," + dbPassword);
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
			errorMsg("printProducts SQLException " + e.getMessage());
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

		if (Debug)
			errorMsg("\texecuteQueryResultSet() - query is >" + query + "<");
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
		if (Debug)
			errorMsg(DebugPrefix + "\texecuteQueryBoolean() - query is "
					+ query);
		boolean result = this.getStatement().execute(query);
		return result;
	}

	public int executeUpdate(String query) throws SQLException,
			ClassNotFoundException {
		if (Debug)
			errorMsg(DebugPrefix + "\texecuteUpdate() - query is " + query);
		int updateCount = this.getStatement().executeUpdate(query);
		return updateCount;
	}

	/**
	 * A convenience function to wrap stings in single quotes for
	 * 
	 * @param token
	 * @return
	 */
	public static String wrapInSingleQuotes(final String token) {
		if (token == null)
			return null;
		if (Debug)
			System.out.println("RdbmsConnection.wrapInSingleQuotes(" + token
					+ ")");
		if (token.contains("\'")) {
			return wrapInDollarQuotes(token);
		}
		return "'" + token + "'";
	}

	public static String wrapInDollarQuotes(final String messyString) {
		String dollarQuote = "$jvh$";
		return dollarQuote + messyString + dollarQuote;
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

	public static String wrapInDoubleQuotes(final String token) {
		return "\"" + token + "\"";
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
		/****
		 * old query - does not seem to work with mysql String query =
		 * "select table_name from information_schema.tables " +
		 * " where table_type = 'BASE TABLE' and table_schema = " +
		 * RdbmsConnection.wrapInSingleQuotes(this.getDbSchemaName()) +
		 * " order by table_name ASC";
		 ***************************************/
		String query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES";
		String[] output = null;
		errorMsg("RdbmsConnection:getTableNamesForDatabase() - query: " + query);
		ResultSet rs = this.executeQueryResultSet(query);
		Vector<String> v = new Vector<String>();

		while (rs.next()) {
			v.add(rs.getString(1));
		}
		output = new String[v.size()];
		v.toArray(output);
		return output;
	}

	public String[] getColumnNamesFromTable(String tableName)
			throws FileNotFoundException, SQLException, ClassNotFoundException {
		String querry = "select column_name from information_schema.columns where table_name="
				+ RdbmsConnection.wrapInSingleQuotes(tableName);
		String[] output = null;

		errorMsg("getColumnNamesFromTable() - query: " + querry);
		ResultSet rs = this.executeQueryResultSet(querry);
		Vector<String> v = new Vector<String>();

		while (rs.next()) {
			v.add(rs.getString(1));
		}
		output = new String[v.size()];
		v.toArray(output);
		return output;
	}

	public ResultSet selectAllValuesFromTable(String tableName)
			throws SQLException, ClassNotFoundException, FileNotFoundException {
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
		errorMsg("RdbmsConnection.selectAllValuesFromTable(" + tableName
				+ ") query is " + query.toString());
		return this.executeQueryResultSet(query.toString());
	}

	public String[] processResultSet(ResultSet rset, String[] colNames) {

		errorMsg(DebugPrefix + "\tprocessResultSet() ");

		StringBuffer sb = null;
		Vector<String> v = new Vector<String>();
		try {
			while (rset.next()) {
				if (Debug)
					errorMsg(DebugPrefix + "result set has next");
				sb = new StringBuffer();
				for (String cname : colNames) {
					if (Debug)
						errorMsg(DebugPrefix + "colName: " + cname);

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
		errorMsg("\ninterogateResultSet");
		ResultSetMetaData rsmd = rs.getMetaData();
		String tableName = rsmd.getTableName(1);
		errorMsg("Table Name: " + tableName);
		int colCount = rs.getMetaData().getColumnCount();
		errorMsg("Column count: " + colCount);
		int ndx = 1;
		for (int i = 0; i < colCount; i++) {
			ndx = i + 1;
			String colName = rs.getMetaData().getColumnName(ndx);
			errorMsg("\tcol name: " + colName);
		}

		String value = null;
		while (rs.next()) {
			for (int i = 0; i < colCount; i++) {
				ndx = i + 1;
				value = rs.getString(ndx);
				errorMsg("\t" + value);
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
			ClassNotFoundException {

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
				this.errorMsg(msg);

			} else {
				msg = "reading " + tableNames.length
						+ " selected tables from database " + this.getDbName();
				this.writeLineToFile(msg);
				System.out.println(msg);
				this.errorMsg(msg);
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
			throws SQLException, ClassNotFoundException, IOException {
		reportTableAndColumnNames(targetTables,null);
	}
	public void reportTableAndColumnNames(final String[] targetTables, BufferedWriter bw)
			throws SQLException, ClassNotFoundException, IOException {


		MiscUtils.writeIt(bw,"\n" + getFormatedDbInfo());

		try {

			MiscUtils.writeIt(bw,"db connection: " + this.toString());

			// MiscUtils.writeIt(bw,"\n\nenter to continue");
			// /myint = keyboard.nextLine();

			String msg = null;
			String[] tableNames = targetTables;
			String outputFileName = this.getDbName() + "TablesAndColumns";
			this.mainOutput = MiscUtils.openOutputFile(outputFileName);

			if (targetTables == null || targetTables.length == 0) {
				tableNames = this.getTableNamesForDatabase();
				msg = "reading all " + tableNames.length
						+ " tables from database " + this.getDbName();
				this.writeLineToFile(msg);
				MiscUtils.writeIt(bw,msg);
				this.errorMsg(msg);

			} else {
				msg = "reading " + tableNames.length
						+ " selected tables from database " + this.getDbName();
				this.writeLineToFile(msg);
				MiscUtils.writeIt(bw,msg);
				this.errorMsg(msg);
			}

			for (String t : tableNames) {
				String[] colNames = this.getColumnNamesFromTable(t);
				this.writeLineToFile("\nTable: " + t);
				StringBuffer sb = new StringBuffer();
				for (String colN : colNames) {
					sb.append(colN + "\t");
				}
				this.writeLineToFile(sb.toString());

			}
			MiscUtils.writeIt(bw,"Output written to: " + outputFileName);
			if (!(this.mainOutput == null)) {
				this.mainOutput.close();
				this.mainOutput = null;
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

	private void errorMsg(String line) {
		if (Debug)
			System.err.println("\n<><>" + line);
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

}
