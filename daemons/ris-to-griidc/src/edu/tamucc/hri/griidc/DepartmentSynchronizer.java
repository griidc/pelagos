package edu.tamucc.hri.griidc;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import org.ini4j.InvalidFileFormatException;

import edu.tamucc.hri.griidc.exception.MultipleRecordsFoundException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.GriidcRisDepartmentMap;
import edu.tamucc.hri.griidc.support.GriidcRisInstitutionMap;
import edu.tamucc.hri.griidc.support.HeuristicMatching;
import edu.tamucc.hri.griidc.support.InstitutionDepartmentRep;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.griidc.support.RisInstDeptPeopleErrorCollection;
import edu.tamucc.hri.griidc.support.RisToGriidcConfiguration;
import edu.tamucc.hri.rdbms.utils.DbColumnInfo;
import edu.tamucc.hri.rdbms.utils.IntStringDbCache;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsConstants;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;
import edu.tamucc.hri.rdbms.utils.TableColInfo;

/**
 * reads RIS Departments records and converts to GRIIDC Department. Store the
 * RIS id and use it for future updates from RIS.
 * 
 * @author jvh
 * 
 */
public class DepartmentSynchronizer extends SynchronizerBase {

	private static final String RisTableName = RdbmsConstants.RisDeptTableName;
	private static final String GriidcTableName = RdbmsConstants.GriidcDeptTableName;

	private int risRecordCount = 0;
	private int risRecordsSkipped = 0;
	private int risRecordErrors = 0;
	private int griidcRecordsAdded = 0;
	private int griidcRecordsModified = 0;
	private int griidcRecordDuplicates = 0;

	private int risDeptId = -1;
	private int risDeptInstId = -1;
	private String risDeptName = null;
	private String risDeptAddr1 = null;
	private String risDeptAddr2 = null;
	private String risDeptCity = null;
	private String risDeptState = null;
	private String risDeptZip = null;
	private String risDeptCountry = null;
	private String risDeptURL = null;
	private double risDeptLat = 0.0;
	private double risDeptLong = 0.0;
	// String risDeptKeywords = null;
	// String risDeptVerified = null;
	/*****************************************
	 * Department_ID int Institution_ID int Department_Name varchar
	 * Department_Addr1 varchar Department_Addr2 varchar Department_City varchar
	 * Department_State varchar Department_Zip varchar Department_Country
	 * varchar Department_URL text Department_Lat decimal Department_Long
	 * decimal
	 ******************************************/

	// GRIIDC Department stuff
	private int griidcDeptNumber = -1;
	private int griidcDeptInstNumber = -1;
	private int griidcDept_RIS_ID = -1;
	private int griidcDeptPostalAreaNumber = -1;
	private String griidcDeptDeliveryPoint = null;
	private String griidcDeptName = null;
	private String griidcDeptUrl = null;

	/***************************************
	 * Department_Number integer Institution_Number integer PostalArea_Number
	 * integer Department_DeliveryPoint text Department_Name text Department_URL
	 * text Department_GeoCoordinate USER-DEFINED
	 ********************************************/

	// get all the values from the RIS Departments table

	private ResultSet rset = null;
	private ResultSet griidcRset = null;

	private static boolean debug = false;
	private static final int ReportNothing = 0;
	private static final int ErrorsOnly = 1;
	private static final int SuccessAndErrors = 2;
	private int reportLevel = ReportNothing;
	private static final boolean ShowRisRead = false;
	private boolean initialized = false;
	private HeuristicMatching heuristics = new HeuristicMatching();
	//private IntStringDbCache griidcInstitutionNumberCache = null;
	private RisInstDeptPeopleErrorCollection risInstitutionWithErrors = null;
	private GriidcRisInstitutionMap griidcRisInstitutionMap = null;
	
	public DepartmentSynchronizer() {

	}

	public boolean isInitialized() {
		return initialized;
	}

	public void initialize() {
		super.commonInitialize();
		if (!isInitialized()) {
			// a set of all the GRIIDC institution numbers
		//	this.griidcInstitutionNumberCache = new IntStringDbCache(
		//			this.griidcDbConnection, "Institution",
		//			"Institution_Number", "Institution_Name");
		//	this.griidcInstitutionNumberCache.buildCacheFromDb();
			this.griidcRisInstitutionMap = RdbmsUtils
					.getGriidcRisInstitutionMap();
			initialized = true;
		}
	}

	/*****
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws IOException
	 * @throws TableNotInDatabaseException
	 * @throws NoRecordFoundException
	 * @throws MultipleRecordsFoundException
	 */
	public RisInstDeptPeopleErrorCollection syncGriidcDepartmentFromRisDepartment(
			RisInstDeptPeopleErrorCollection risInstWithErr)
			throws ClassNotFoundException, PropertyNotFoundException,
			IOException, SQLException, TableNotInDatabaseException {
		if (isDebug())
			System.out.println(MiscUtils.BreakLine);
		this.risInstitutionWithErrors = risInstWithErr;

		this.initialize();

		String tempDeliveryPoint = null; // created from RIS info
		int tempPostalAreaNumber = -1; // created from RIS info
		String msg = null;
		// get all records from the RIS Department table
		try {
			rset = this.risDbConnection.selectAllValuesFromTable(RisTableName);
			/*
			 * if the data in RIS is unusable - skip this record - go to the
			 * next record *
			 */
			while (rset.next()) { // continue statements branch back to here
				readRisRecord();
				int countryNumber = -1;
				int mappedGriidcInstitutionNum = RdbmsConstants.NotFound;
				int mappedGriidcDepartmentNum = RdbmsConstants.NotFound;
				
				if (MiscUtils.isStringEmpty(risDeptCountry)) {
					msg = "Error In RIS Departments - record id: "
							+ risDeptId
							+ " - Department_Country is "
							+ ((risDeptCountry == null) ? "null"
									: " length zero");
					MiscUtils.writeToRisErrorLogFile(msg);
					errorMessageOut(msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // skip to while (rset.next())
				}
				
				try {
					mappedGriidcInstitutionNum = this.griidcRisInstitutionMap.getGriidcInstitutionNumber(this.risDeptInstId);
				
				} catch (NoRecordFoundException e1) { 
					invalidInstitutionReference(e1.getMessage());
					errorMessageOut(e1.getMessage());
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue;
				}
				// is the contry code good ??
				try {
					countryNumber = RdbmsUtils
							.getCountryNumberFromName(risDeptCountry);
				} catch (MultipleRecordsFoundException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					errorMessageOut(e.getMessage());
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					msg = "Error in RIS Departments - record id: "
							+ this.risDeptId + ": " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					errorMessageOut(msg);
					continue; // branch back to while (rset.next())
				}
				
				/****
				 * The Department in RIS is in GRIIDC 
				 * find and update the GRIIDC
				 * Department table with these values
				 */
				tempPostalAreaNumber = -1;

				try {
					// if postal code heuristics are turned on in the
					// db.ini file this call will try to modify the postal
					// code in a way that makes it more likely to match
					// without changing it's value
					HeuristicMatching.setDeBug(true);
					String zipCode = heuristics.fuzzyPostalCode(countryNumber,
							risDeptZip);
					tempPostalAreaNumber = RdbmsUtils
							.getGriidcDepartmentPostalNumber(countryNumber,
									risDeptState, risDeptCity, zipCode);
				} catch (MultipleRecordsFoundException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					errorMessageOut(e.getMessage());
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					msg = "Error in RIS Departments - record id: "
							+ this.risDeptId + ": " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					errorMessageOut(msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
					errorMessageOut(e.getMessage());
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (MissingArgumentsException e) {
					msg = "Error In RIS Departments - record: " + risDeptId
							+ " - " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					errorMessageOut(msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				}
				
				tempDeliveryPoint = MiscUtils.makeDeliveryPoint(
						this.risDeptAddr1, this.risDeptAddr2);
			
				try {
					mappedGriidcDepartmentNum = findMatchingGriidcDepartment();
					modifyGriidcDepartment(mappedGriidcInstitutionNum,
							tempDeliveryPoint, tempPostalAreaNumber);
				} catch (NoRecordFoundException e) {
					this.addGriidcDepartment(mappedGriidcInstitutionNum,
							tempDeliveryPoint, tempPostalAreaNumber);
				} catch (MultipleRecordsFoundException e) {
					msg = "Error In RIS Departments - record RIS Department ID: " + risDeptId
							+ " - " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					errorMessageOut(msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				}  
			} // end of main while loop
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return this.risInstitutionWithErrors;
		// end of Department
	}

	private void readRisRecord() throws SQLException {
		risRecordCount++;
		this.risDeptId = rset.getInt("Department_ID");
		this.risDeptInstId = rset.getInt("Institution_ID");
		this.risDeptName = rset.getString("Department_Name").trim();
		this.risDeptAddr1 = rset.getString("Department_Addr1").trim();
		this.risDeptAddr2 = rset.getString("Department_Addr2").trim();
		this.risDeptCity = rset.getString("Department_City").trim();
		this.risDeptState = rset.getString("Department_State").trim();
		this.risDeptZip = rset.getString("Department_Zip").trim();
		this.risDeptCountry = rset.getString("Department_Country").trim();
		this.risDeptURL = rset.getString("Department_URL").trim();
		this.risDeptLat = rset.getDouble("Department_Lat");
		this.risDeptLong = rset.getDouble("Department_Long");

		String msg = "Read RIS: " + "Dept: " + risDeptId + ", "
				+ "DeptInstId: " + risDeptInstId + ", " + "Name : "
				+ risDeptName + ", " + "Addr 1: " + risDeptAddr1 + ", "
				+ "Addr 2: " + risDeptAddr2 + ", " + "City: " + risDeptCity
				+ ", " + "State: " + risDeptState + ", " + "Zip: " + risDeptZip
				+ ", " + "Country: " + risDeptCountry + ", " + "URL: "
				+ risDeptURL + ", " + "Lat: " + risDeptLat + ", " + "lon: "
				+ risDeptLong;
		if (isDebug() || ShowRisRead)
			System.out.println("\n" + msg);
	}

	private void invalidInstitutionReference(String exMessage) {
		String msg = "Error in RIS Departments - record id: " + this.risDeptId
				+ " references an Institution that does not exist: "
				+ risDeptInstId;
		if (this.isInstitutionOnRisErrorsList(this.risDeptInstId)) {
			msg = msg
					+ "\nThe referenced RIS Institution was rejected when updating Institutions due to data errors.";
			InstitutionDepartmentRep rep = null;
			try {
				rep = this.risInstitutionWithErrors
						.findInstitution(this.risDeptInstId);
				rep.addDepartment(this.risDeptId);
			} catch (NoRecordFoundException e) {
				msg = "RIS Department: "
						+ this.risDeptId
						+ " references an Institution Number that does not exist: "
						+ risDeptInstId
						+ " - but the institution is not on the error list";
				System.err.println(msg);
				System.exit(-1);
			}
		}
		msg = msg + "\n" + exMessage;
		MiscUtils.writeToPrimaryLogFile(msg);
		MiscUtils.writeToRisErrorLogFile(msg);
	}

	/**
	 * Must read the griidc Department table to find a set of records (one
	 * record)
	 * 
	 * @param targetRisDeptId
	 * @param griidcInstNumber
	 * @return
	 */
	private String formatFindQuery(int targetRisDeptId) {
		return "SELECT * FROM "
				// + this.getWrappedGriidcShemaName() + "."
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes("Department_RIS_ID")
				+ RdbmsConstants.EqualSign + targetRisDeptId;
	}

	/**
	 * read the GRIIDC department. The GRIIDC Department_Number is unique but
	 * may not be known at this time. The RIS Department Id is also unique and
	 * is stored in the GRIIDC Department Record
	 * 
	 * @param targetRisDeptId
	 * @return the GRIIDC Department number
	 */
	private int findMatchingGriidcDepartment()
			throws NoRecordFoundException, MultipleRecordsFoundException {

		String query = formatFindQuery(this.risDeptId);
		int count = 0;
		this.griidcDeptNumber = RdbmsConstants.NotFound;
		try {
			griidcRset = this.griidcDbConnection.executeQueryResultSet(query);
			while (griidcRset.next()) {
				count++;
				this.griidcDeptNumber = griidcRset.getInt("Department_Number");
				this.griidcDeptInstNumber = griidcRset
						.getInt("Institution_Number");
				this.griidcDept_RIS_ID = griidcRset.getInt("Department_RIS_ID");
				this.griidcDeptPostalAreaNumber = griidcRset
						.getInt("PostalArea_Number");
				this.griidcDeptDeliveryPoint = griidcRset
						.getString("Department_DeliveryPoint");
				this.griidcDeptName = griidcRset.getString("Department_Name");
				this.griidcDeptUrl = griidcRset.getString("Department_URL");
			}
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		if (count == 0) {
			throw new NoRecordFoundException("In GRIIDC "
					+ RdbmsConstants.GriidcDeptTableName
					+ " table - no records match Department_RIS_ID: "
					+ this.risDeptId);
		} else if (count > 1) {
			throw new MultipleRecordsFoundException("In GRIIDC "
					+ RdbmsConstants.GriidcDeptTableName + " table  - " + count
					+ " records match Department_RIS_ID: " + this.risDeptId);
		}
		return this.griidcDeptNumber;
	}

	/**
	 * add the GRIIDC Department
	 */
	private void addGriidcDepartment(int griidcInstNumber,
			String tempDeliveryPoint, int tempPostalAreaNumber) {
		String msg = null;
		if (DepartmentSynchronizer.isDebug()) {
			msg = "Add GRIIDC Department table record " + "Department_Name: "
					+ risDeptName + ", PostalArea_Number: "
					+ griidcDeptPostalAreaNumber
					+ ", Department_DeliveryPoint: " + tempDeliveryPoint;
			System.out.println(msg);
		}
		String addQuery = null;
		try {
			addQuery = this.formatAddQuery(this.risDeptId, griidcInstNumber,
					tempPostalAreaNumber, tempDeliveryPoint, this.risDeptName,
					risDeptURL, risDeptLong, risDeptLat);
			if (DepartmentSynchronizer.isDebug())
				System.out.println("Query: " + addQuery);
			this.griidcDbConnection.executeQueryBoolean(addQuery);
			this.griidcRecordsAdded++;
		} catch (SQLException e) {
			msg = "SQL Error: Adding Department in GRIIDC - \nQuery: "
					+ addQuery + "\n" + e.getMessage();
			if (DepartmentSynchronizer.isDebug())
				System.err.println(msg);
			MiscUtils.writeToPrimaryLogFile(msg);
			errorMessageOut(msg);
		}
	}

	/**
	 * the RIS record matches one in GRIIDC 
	 * Modify Department record
	 * 
	 * @param tempDeliveryPoint
	 * @param tempPostalAreaNumber
	 */
	private void modifyGriidcDepartment(int mappedGriidcInstNumber,
			String tempDeliveryPoint, int tempPostalAreaNumber) {
		String msg = null;
		
		if (isCurrentRecordEqual(mappedGriidcInstNumber,
				tempPostalAreaNumber, tempDeliveryPoint, this.risDeptName,
				this.risDeptURL,
				this.griidcDeptInstNumber, this.griidcDeptPostalAreaNumber,
				this.griidcDeptDeliveryPoint, this.griidcDeptName,
				this.griidcDeptUrl)) {
			this.griidcRecordDuplicates++;
			errorMessageOut("Duplicate record");
		} else {
			if (DepartmentSynchronizer.isDebug()) {
				msg = "Modify GRIIDC Department table matching "
						+ "griidcDeptNumber: " + risDeptId
						+ ", Department_Name: " + risDeptName
						+ ", PostalArea_Number: " + griidcDeptPostalAreaNumber
						+ ", Department_DeliveryPoint: " + tempDeliveryPoint;
				if (isDebug())
					System.out.println(msg);
			}

			String modifyQuery = null;
			try {
				modifyQuery = this.formatModifyQuery(this.risDeptId,
						this.risDeptInstId, tempPostalAreaNumber,
						tempDeliveryPoint, this.risDeptName, risDeptURL,
						risDeptLong, risDeptLat);

				if (DepartmentSynchronizer.isDebug())
					System.out.println("Query: " + modifyQuery);
				this.griidcDbConnection.executeQueryBoolean(modifyQuery);
				this.griidcRecordsModified++;
				successMessageOut(msg);
			} catch (SQLException e) {
				msg = "SQL Error: Modify Department in GRIIDC - Query: "
						+ modifyQuery;
				System.err.println(msg);
				e.printStackTrace();
				errorMessageOut(msg);
			}
		}
		return;
	}
	/**
	 * if any of the parameter pairs don't match they are not equal
	 * This looks at the payload of the record not the key
	 * 
	 * @param rRisDeptId
	 * @param rName
	 * @param rPostalAreaNumber
	 * @param rDeliveryPoint
	 * @param rUrl
	 * @param rLon
	 * @param rLat
	 * @param gDeptNum
	 * @param gName
	 * @param gPostalAreaNumber
	 * @param gDeliveryPoint
	 * @param gUrl
	 * @param gLon
	 * @param gLat
	 * @return
	 */
	private boolean isCurrentRecordEqual(int mappedGriidcInstNumber,
			int rPostalAreaNumber, String rDeliveryPoint, String rName,
			String rUrl, int gInstNumber, int gPostalAreaNumber,
			String gDeliveryPoint, String gName, String gUrl) { 

		String dformat = "%n%10d %10d %10d %30s %30s %50s";

		if(!(mappedGriidcInstNumber == gInstNumber))
			return false;
		if (!(rName.equals(gName)))
			return false;
		if (!(rDeliveryPoint.equals(gDeliveryPoint)))
			return false;
		if (!(rPostalAreaNumber == gPostalAreaNumber))
			return false;
		if (!(rUrl.equals(gUrl)))
			return false;
		return true;

	}
	private boolean isInstitutionOnRisErrorsList(int risDeptInstId) {
		return this.risInstitutionWithErrors.containsInstitution(risDeptInstId);
	}

	private DbColumnInfo[] getDbColumnInfo(int griidcDept_RIS_ID,
			int griidcDeptInstNumber, int griidcPostalAreaNumber,
			String deliveryPoint, String risDeptName, String risDeptURL,
			double risDeptLon, double risDeptLat) throws SQLException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);

		tci.getDbColumnInfo("Department_RIS_ID").setColValue(
				String.valueOf(griidcDept_RIS_ID));
		tci.getDbColumnInfo("Institution_Number").setColValue(
				String.valueOf(griidcDeptInstNumber));
		tci.getDbColumnInfo("PostalArea_Number").setColValue(
				String.valueOf(griidcPostalAreaNumber));
		tci.getDbColumnInfo("Department_DeliveryPoint").setColValue(
				deliveryPoint);
		tci.getDbColumnInfo("Department_Name").setColValue(risDeptName);
		tci.getDbColumnInfo("Department_URL").setColValue(risDeptURL);
		tci.getDbColumnInfo("Department_GeoCoordinate").setColValue(
				RdbmsUtils.makeSqlGeometryPointString(risDeptLon, risDeptLat));
		return tci.getDbColumnInfo();
	}

	private String formatAddQuery(int risDeptId, int gDeptInstNum,
			int griidcPostalAreaNumber, String deliveryPoint,
			String risDeptName, String risDeptURL, double risDeptLon,
			double risDeptLat) throws SQLException {

		DbColumnInfo[] info = getDbColumnInfo(risDeptId, gDeptInstNum,
				griidcPostalAreaNumber, deliveryPoint, risDeptName, risDeptURL,
				risDeptLon, risDeptLat);
		String query = RdbmsUtils.formatInsertStatement(GriidcTableName, info);
		return query;

	}

	private String formatModifyQuery(int griidcDept_RIS_ID,
			int griidcDeptInstNumber, int griidcPostalAreaNumber,
			String deliveryPoint, String risDeptName, String risDeptURL,
			double risDeptLon, double risDeptLat) throws SQLException {

		DbColumnInfo[] info = getDbColumnInfo(griidcDept_RIS_ID,
				griidcDeptInstNumber, griidcPostalAreaNumber, deliveryPoint,
				risDeptName, risDeptURL, risDeptLon, risDeptLat);
		DbColumnInfo[] whereInfo = new DbColumnInfo[1];

		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);

		tci.getDbColumnInfo("Department_RIS_ID").setColValue(
				String.valueOf(griidcDept_RIS_ID));

		whereInfo[0] = tci.getDbColumnInfo("Institution_Number");

		String query = RdbmsUtils.formatUpdateStatement(
				DepartmentSynchronizer.GriidcTableName, info, whereInfo);

		if (DepartmentSynchronizer.isDebug())
			System.out.println("formatModifyQuery() " + query);
		return query;
	}

	private String griidcDepartmentToStringAll() {
		return griidcDepartmentToString() + ", Dept postal area: "
				+ this.griidcDeptPostalAreaNumber + ", Dept delivery point: "
				+ this.griidcDeptDeliveryPoint + ", Dept name: "
				+ this.griidcDeptName + ", Dept URL: " + this.griidcDeptUrl;
	}

	private String griidcDepartmentToString() {
		return "Dept Num: " + this.griidcDeptNumber + ", Dept Inst Num: "
				+ this.griidcDeptInstNumber + ", RIS Dept Id: "
				+ this.griidcDept_RIS_ID;
	}

	

	public String getPrimaryLogFileName() throws PropertyNotFoundException,
			InvalidFileFormatException, IOException {
		return RisToGriidcConfiguration.getPrimaryLogFileName();
	}

	public String getRisErrorLogFileName() throws PropertyNotFoundException,
			InvalidFileFormatException, IOException {
		return RisToGriidcConfiguration.getRisErrorLogFileName();
	}

	public static boolean isDebug() {
		return DepartmentSynchronizer.debug;
	}

	public static void setDebug(boolean debug) {
		DepartmentSynchronizer.debug = debug;
	}

	public void reportDepartmentTable() throws IOException,
			PropertyNotFoundException, SQLException, ClassNotFoundException,
			TableNotInDatabaseException {
		RdbmsUtils.reportTables(RisTableName, GriidcTableName);
		return;
	}

	private void errorMessageOut(String msg) {
		if (reportLevel >= ErrorsOnly)
			System.out.println(">>> Error: " + msg);
	}

	private void successMessageOut(String msg) {
		if (reportLevel >= SuccessAndErrors)
			System.out.println("+++ Success: " + msg);
	}

	public int getRisRecordCount() {
		return risRecordCount;
	}

	public int getGriidcRecordsAdded() {
		return griidcRecordsAdded;
	}

	public int getGriidcRecordsModified() {
		return griidcRecordsModified;
	}

	public int getGriidcRecordDuplicates() {
		return griidcRecordDuplicates;
	}

	public int getRisRecordsSkipped() {
		return risRecordsSkipped;
	}

	public int getRisRecordErrors() {
		return risRecordErrors;
	}
}
