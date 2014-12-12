package edu.tamucc.hri.griidc.rdbms;

import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.utils.GriidcConfiguration;


public class RdbmsConnectionFactory {
	
	private RdbmsConnection RisDbConnectionInstance = null;

	private RdbmsConnection GriidcDbConnectionInstance = null;

	private RdbmsConnection GriidcSecondaryDbConnection = null;
	
	
	private static boolean DeBug = false;
	private static RdbmsConnectionFactory instance = null;
	
	public static RdbmsConnectionFactory getInstance() {
		if(instance == null) {
			RdbmsConnectionFactory.instance = new RdbmsConnectionFactory();
		}
		return RdbmsConnectionFactory.instance;
	}
	private RdbmsConnectionFactory() {
		// TODO Auto-generated constructor stub
	}
	
	public RdbmsConnection getRisDbConnectionInstance()
			throws SQLException {
		if (this.RisDbConnectionInstance == null) {
			this.RisDbConnectionInstance = this.createNewRisDbConnection();
		}
		return this.RisDbConnectionInstance;
	}

	public  RdbmsConnection getGriidcDbConnectionInstance()
			throws SQLException {
		if (this.GriidcDbConnectionInstance == null) {
			this.GriidcDbConnectionInstance = this.createNewGriidcDbConnection();
		}
		return this.GriidcDbConnectionInstance;
	}

	public RdbmsConnection getGriidcSecondaryDbConnectionInstance()
			throws SQLException {
		if (this.GriidcSecondaryDbConnection == null) {
			this.GriidcSecondaryDbConnection = this.createNewGriidcDbConnection();
		}
		return this.GriidcSecondaryDbConnection;
	}

	public void closeGriidcSecondaryDbConnection() throws SQLException {
		if (this.GriidcSecondaryDbConnection == null)
			return;
		this.GriidcSecondaryDbConnection.closeConnection();
		this.GriidcSecondaryDbConnection = null;

	}
	
	private  static int griidcInstanceCount = 0;
	private  static int risInstanceCount = 0;

	private RdbmsConnection createNewGriidcDbConnection()
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

	
	private RdbmsConnection createNewRisDbConnection()
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
	
	public int getGriidcInstanceCount() {
		return griidcInstanceCount;
	}

	public int getRisInstanceCount() {
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
