package edu.tamucc.hri.rdbms.utils;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.SQLException;

import org.ini4j.InvalidFileFormatException;

import edu.tamucc.hri.griidc.support.RisToGriidcConfiguration;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

public class RdbmsConnectionFactory {
	
	private static RdbmsConnection RisDbConnectionInstance = null;

	private static RdbmsConnection GriidcDbConnectionInstance = null;

	private static RdbmsConnection GriidcSecondaryDbConnection = null;
	
	public RdbmsConnectionFactory() {
		// TODO Auto-generated constructor stub
	}
	
	public static RdbmsConnection getRisDbConnectionInstance()
			throws SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		if (RdbmsConnectionFactory.RisDbConnectionInstance == null) {
			RdbmsConnectionFactory.RisDbConnectionInstance = RdbmsConnectionFactory.createNewRisDbConnection();
		}
		return RdbmsConnectionFactory.RisDbConnectionInstance;
	}

	public static  RdbmsConnection getGriidcDbConnectionInstance()
			throws SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		if (RdbmsConnectionFactory.GriidcDbConnectionInstance == null) {
			RdbmsConnectionFactory.GriidcDbConnectionInstance = RdbmsConnectionFactory.createNewGriidcDbConnection();
		}
		return RdbmsConnectionFactory.GriidcDbConnectionInstance;
	}

	public static  RdbmsConnection getGriidcSecondaryDbConnectionInstance()
			throws SQLException, ClassNotFoundException,
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
			throws SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		String jdbcDriverName = RisToGriidcConfiguration.getRisToGriiidcIniProp(RisToGriidcConfiguration.getRisToGriidcGriidcDbSection(),"driverName");
		String jdbcPrefix = RisToGriidcConfiguration.getRisToGriiidcIniProp(RisToGriidcConfiguration.getRisToGriidcGriidcDbSection(),"jdbcPrefix");
		String dbSchema = RisToGriidcConfiguration.getRisToGriiidcIniProp(RisToGriidcConfiguration.getRisToGriidcGriidcDbSection(),"schema");
		
		String sectionName = RisToGriidcConfiguration.getGriidcDbIniSection();
		String dbType = RisToGriidcConfiguration.getDbIniProp(sectionName,"type");
		String dbHost = RisToGriidcConfiguration.getDbIniProp(sectionName,"host");
		String dbPort = RisToGriidcConfiguration.getDbIniProp(sectionName,"port");
		String dbName = RisToGriidcConfiguration.getDbIniProp(sectionName,"dbname");
		String dbUser = RisToGriidcConfiguration.getDbIniProp(sectionName,"username");
		String dbPassword = RisToGriidcConfiguration.getDbIniProp(sectionName,"password");

		RdbmsConnection con = new RdbmsConnection();
		con.setConnection(dbType, jdbcDriverName, jdbcPrefix, dbHost, dbPort,
				dbName, dbSchema, dbUser, dbPassword);
		RdbmsConnectionFactory.griidcInstanceCount++;
		return con;
	}

	private static RdbmsConnection createNewRisDbConnection()
			throws SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		String jdbcDriverName = RisToGriidcConfiguration.getRisToGriiidcIniProp(RisToGriidcConfiguration.getRisToGriidcRisDbSection(),"driverName");
		String jdbcPrefix = RisToGriidcConfiguration.getRisToGriiidcIniProp(RisToGriidcConfiguration.getRisToGriidcRisDbSection(),"jdbcPrefix");
		
		
		String sectionName = RisToGriidcConfiguration.getRisDbIniSection();
		String dbType = RisToGriidcConfiguration.getDbIniProp(sectionName,"type");
		String dbHost = RisToGriidcConfiguration.getDbIniProp(sectionName,"host");
		String dbPort = RisToGriidcConfiguration.getDbIniProp(sectionName,"port");
		String dbName = RisToGriidcConfiguration.getDbIniProp(sectionName,"dbname");
		String dbUser = RisToGriidcConfiguration.getDbIniProp(sectionName,"username");
		String dbPassword = RisToGriidcConfiguration.getDbIniProp(sectionName,"password");
		String dbSchema = null;

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
