package edu.tamucc.hri.rdbms.utils;

import java.io.FileNotFoundException;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.support.RisPropertiesAccess;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

public class RdbmsConnectionFactory {
	
	private static RdbmsConnection RisDbConnectionInstance = null;

	private static RdbmsConnection GriidcDbConnectionInstance = null;

	private static RdbmsConnection GriidcSecondaryDbConnection = null;
	
	public RdbmsConnectionFactory() {
		// TODO Auto-generated constructor stub
	}
	
	public static RdbmsConnection getRisDbConnectionInstance()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		if (RdbmsConnectionFactory.RisDbConnectionInstance == null) {
			RdbmsConnectionFactory.RisDbConnectionInstance = RdbmsConnectionFactory.createNewRisDbConnection();
		}
		return RdbmsConnectionFactory.RisDbConnectionInstance;
	}

	public static  RdbmsConnection getGriidcDbConnectionInstance()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		if (RdbmsConnectionFactory.GriidcDbConnectionInstance == null) {
			RdbmsConnectionFactory.GriidcDbConnectionInstance = RdbmsConnectionFactory.createNewGriidcDbConnection();
		}
		return RdbmsConnectionFactory.GriidcDbConnectionInstance;
	}

	public static  RdbmsConnection getGriidcSecondaryDbConnectionInstance()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		if (RdbmsConnectionFactory.GriidcSecondaryDbConnection == null) {
			RdbmsConnectionFactory.GriidcSecondaryDbConnection = RdbmsConnectionFactory.createNewGriidcDbConnection();
		}
		return RdbmsConnectionFactory.GriidcSecondaryDbConnection;
	}

	public static void closeGriidcSecondaryDbConnection() throws SQLException {
		if (RdbmsConnectionFactory.GriidcSecondaryDbConnection == null)
			return;
		RdbmsConnectionFactory.GriidcSecondaryDbConnection.closeConnection();
		RdbmsConnectionFactory.GriidcSecondaryDbConnection = null;

	}
	
	private  static int griidcInstanceCount = 0;
	private  static int risInstanceCount = 0;

	private static RdbmsConnection createNewGriidcDbConnection()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		RisPropertiesAccess risProperties = RisPropertiesAccess.getInstance();
		String jdbcDriverName = risProperties
				.getProperty("griidc.db.driver.name");
		String jdbcPrefix = risProperties.getProperty("griidc.db.jdbcPrefix");

		String dbType = risProperties.getProperty("griidc.db.type");
		String dbHost = risProperties.getProperty("griidc.db.host");
		String dbPort = risProperties.getProperty("griidc.db.port");
		String dbName = risProperties.getProperty("griidc.db.name");
		String dbSchema = risProperties.getProperty("griidc.db.schema");
		String dbUser = risProperties.getProperty("griidc.db.user");
		String dbPassword = risProperties.getProperty("griidc.db.password");

		RdbmsConnection con = new RdbmsConnection();
		con.setConnection(dbType, jdbcDriverName, jdbcPrefix, dbHost, dbPort,
				dbName, dbSchema, dbUser, dbPassword);
		RdbmsConnectionFactory.griidcInstanceCount++;
		return con;
	}

	private static RdbmsConnection createNewRisDbConnection()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		RisPropertiesAccess risProperties = RisPropertiesAccess.getInstance();
		String jdbcDriverName = risProperties.getProperty("ris.db.driver.name");
		String jdbcPrefix = risProperties.getProperty("ris.db.jdbcPrefix");
		String dbType = risProperties.getProperty("ris.db.type");
		String dbHost = risProperties.getProperty("ris.db.host");
		String dbPort = risProperties.getProperty("ris.db.port");
		String dbName = risProperties.getProperty("ris.db.name");
		String dbSchema = risProperties.getProperty("ris.db.schema");
		String dbUser = risProperties.getProperty("ris.db.user");
		String dbPassword = risProperties.getProperty("ris.db.password");

		RdbmsConnection con = new RdbmsConnection();
		con.setConnection(dbType, jdbcDriverName, jdbcPrefix, dbHost, dbPort,
				dbName, dbSchema, dbUser, dbPassword);
		RdbmsConnectionFactory.risInstanceCount++;
		return con;
	}

	public  static  int getGriidcInstanceCount() {
		return griidcInstanceCount;
	}

	public  static  int getRisInstanceCount() {
		return risInstanceCount;
	}

}
