package edu.tamucc.hri.griidc.rdbms;


import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.utils.GriidcConfiguration;


public class RdbmsConnectionFactory {
	
	private static RdbmsConnection RisDbConnectionInstance = null;

	private static RdbmsConnection GriidcDbConnectionInstance = null;

	private static RdbmsConnection GriidcSecondaryDbConnection = null;
	
	private static boolean DeBug = false;
	
	public RdbmsConnectionFactory() {
		// TODO Auto-generated constructor stub
	}
	
	public static RdbmsConnection getRisDbConnectionInstance()
			throws SQLException {
		if (RdbmsConnectionFactory.RisDbConnectionInstance == null) {
			RdbmsConnectionFactory.RisDbConnectionInstance = RdbmsConnectionFactory.createNewRisDbConnection();
		}
		return RdbmsConnectionFactory.RisDbConnectionInstance;
	}

	public static  RdbmsConnection getGriidcDbConnectionInstance()
			throws SQLException {
		if (RdbmsConnectionFactory.GriidcDbConnectionInstance == null) {
			RdbmsConnectionFactory.GriidcDbConnectionInstance = RdbmsConnectionFactory.createNewGriidcDbConnection();
		}
		return RdbmsConnectionFactory.GriidcDbConnectionInstance;
	}

	public static  RdbmsConnection getGriidcSecondaryDbConnectionInstance()
			throws SQLException {
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
			throws SQLException {
		String dbIniSectionName = GriidcConfiguration.getGriidcDbIniSection();
		String risToGriidcIniSectionName = GriidcConfiguration.getRisToGriidcGriidcDbSection();
		
		debugMsg(" createDbConnection(" + dbIniSectionName +")");
		RdbmsConnection con = null;
		String dbConnectionDescription = null;
		try {
			String dbType =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"type");
			String dbHost =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"host");
			String dbPort =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"port");
			String dbName =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"dbname");
			String dbUser =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"username");
			String dbPassword =     GriidcConfiguration.getDbIniProp(dbIniSectionName,"password");
			
		//  these properties are in the RisToGriidc ini 
			String dbSchema =       GriidcConfiguration.getRisToGriiidcIniProp(risToGriidcIniSectionName,"schema");
			String jdbcDriverName = GriidcConfiguration.getRisToGriiidcIniProp(risToGriidcIniSectionName,"driverName");
			String jdbcPrefix =     GriidcConfiguration.getRisToGriiidcIniProp(risToGriidcIniSectionName,"jdbcPrefix");

			con = new RdbmsConnection(dbType, jdbcDriverName, jdbcPrefix, dbHost, dbPort,
					dbName, dbSchema, dbUser, dbPassword);
			dbConnectionDescription = con.toString();
			debugMsg(" createDbConnection() " + dbConnectionDescription);
			con.setConnection();
			RdbmsConnectionFactory.griidcInstanceCount++;
			return con;
		} catch (PropertyNotFoundException e) {
			System.err.println("RdbmsConnection.createNewRisDbConnection() " + e.getMessage());
			e.printStackTrace();
			System.exit(-1);
		} catch (SQLException e) {
			String reason = e.getMessage() + " for connection: " + dbConnectionDescription;
			throw new SQLException(reason,e.getSQLState());
		} catch (IniSectionNotFoundException e) {
			System.err.println("RdbmsConnection.createNewRisDbConnection() " + e.getMessage());
			e.printStackTrace();
			System.exit(-1);
		}
		return con;
	}

	
	private static RdbmsConnection createNewRisDbConnection()
			throws SQLException {
		String dbIniSectionName =  GriidcConfiguration.getRisDbIniSection();
		String risToGriidcIniSectionName = GriidcConfiguration.getRisToGriidcRisDbSection();
		debugMsg(" createDbConnection(" + dbIniSectionName +")");
		RdbmsConnection con = null;
		String dbConnectionDescription = null;
		try {
			String dbType =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"type");
			String dbHost =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"host");
			String dbPort =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"port");
			String dbName =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"dbname");
			String dbUser =         GriidcConfiguration.getDbIniProp(dbIniSectionName,"username");
			String dbPassword =     GriidcConfiguration.getDbIniProp(dbIniSectionName,"password");
			//  these properties are in the RisToGriidc ini 
			String dbSchema =       null; // GriidcConfiguration.getRisToGriiidcIniProp(risToGriidcIniSectionName,"schema");
			String jdbcDriverName = GriidcConfiguration.getRisToGriiidcIniProp(risToGriidcIniSectionName,"driverName");
			String jdbcPrefix =     GriidcConfiguration.getRisToGriiidcIniProp(risToGriidcIniSectionName,"jdbcPrefix");
			

			con = new RdbmsConnection(dbType, jdbcDriverName, jdbcPrefix, dbHost, dbPort,
					dbName, dbSchema, dbUser, dbPassword);
			dbConnectionDescription = con.toString();
			debugMsg(" createDbConnection() " + dbConnectionDescription);
			con.setConnection();
			RdbmsConnectionFactory.griidcInstanceCount++;
			return con;
		} catch (PropertyNotFoundException e) {
			System.err.println("RdbmsConnection.createNewRisDbConnection() " + e.getMessage());
			e.printStackTrace();
			System.exit(-1);
		} catch (SQLException e) {
			String reason = e.getMessage() + " for connection: " + dbConnectionDescription;
			throw new SQLException(reason,e.getSQLState());
		} catch (IniSectionNotFoundException e) {
			System.err.println("RdbmsConnection.createNewRisDbConnection() " + e.getMessage());
			e.printStackTrace();
			System.exit(-1);
		}
		return con;
	}
	
	public  static  int getGriidcInstanceCount() {
		return griidcInstanceCount;
	}

	public  static  int getRisInstanceCount() {
		return risInstanceCount;
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}
	
	private static void debugMsg(String msg) {
		if(RdbmsConnectionFactory.isDeBug()) {
			System.out.println("RdbmsConnectionFactory: " + msg);
		}
	}
}
