package edu.tamucc.hri.griidc.ris;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Iterator;
import java.util.Vector;

import org.ini4j.InvalidFileFormatException;

import edu.tamucc.hri.griidc.exception.MultipleRecordsFoundException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.IntStringDbCache;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.SynchronizerBase;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.utils.GriidcRisDepartmentMap;
import edu.tamucc.hri.griidc.utils.GriidcRisInstitutionMap;
import edu.tamucc.hri.griidc.utils.HeuristicMatching;
import edu.tamucc.hri.griidc.utils.InstitutionDepartmentRep;
import edu.tamucc.hri.griidc.utils.MessageContainer;
import edu.tamucc.hri.griidc.utils.MiscUtils;
import edu.tamucc.hri.griidc.utils.RisInstDeptPeopleErrorCollection;
import edu.tamucc.hri.griidc.utils.GriidcConfiguration;

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
	// private IntStringDbCache griidcInstitutionNumberCache = null;
	private RisInstDeptPeopleErrorCollection risInstitutionWithErrors = null;
	private GriidcRisInstitutionMap griidcRisInstitutionMap = null;

	private MessageContainer messagePackage = new MessageContainer();

	public DepartmentSynchronizer() {

	}

	public boolean isInitialized() {
		return initialized;
	}

	public void initialize() {
		super.commonInitialize();
		if (!isInitialized()) {
			// a set of all the GRIIDC institution numbers
			// this.griidcInstitutionNumberCache = new IntStringDbCache(
			// this.griidcDbConnection, "Institution",
			// "Institution_Number", "Institution_Name");
			// this.griidcInstitutionNumberCache.buildCacheFromDb();
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
				//
				// read another RIS record
				//
				if (DepartmentSynchronizer.isDebug()) {
					if (this.messagePackage.size() > 0) {
                        this.messagePackage.toOut();
					}
				}
				readRisRecord();
				int countryNumber = -1;
				int correspondingGriidcInstitutionNum = RdbmsConstants.NotFound;
				int correspondingGriidcDepartmentNum = RdbmsConstants.NotFound;

				if (MiscUtils.isStringEmpty(risDeptCountry)) {
					msg = "Error D-1 In RIS Departments - record id: "
							+ risDeptId
							+ " - Department_Country is "
							+ ((risDeptCountry == null) ? "null"
									: " length zero");
					MiscUtils.writeToRisErrorLogFile(msg);
					errorMessageOut(msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					
					if (DepartmentSynchronizer.isDebug())
					       this.messagePackage.add(msg);
					continue; // skip to while (rset.next())
				}
				//
				// get the GRIIDC Institution number (previously stored)
				// corresponding to the RIS Department Institutuion Id
				//
				try {
					correspondingGriidcInstitutionNum = this.griidcRisInstitutionMap
							.getGriidcInstitutionNumber(this.risDeptInstId);

				} catch (NoRecordFoundException e1) {
					invalidInstitutionReference(e1.getMessage());
					errorMessageOut(e1.getMessage());
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					if (DepartmentSynchronizer.isDebug())
					       this.messagePackage.add(e1.getMessage());
					
					continue;
				}
				//
				// is the contry code good ??
				//
				try {
					countryNumber = RdbmsUtils
							.getCountryNumberFromName(risDeptCountry);
				} catch (MultipleRecordsFoundException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					errorMessageOut(e.getMessage());
					this.risRecordsSkipped++;

					if (DepartmentSynchronizer.isDebug())
					       this.messagePackage.add(e.getMessage());
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					msg = "Error D-2 in RIS Departments - record id: "
							+ this.risDeptId + ": " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					errorMessageOut(msg);
					if (DepartmentSynchronizer.isDebug())
					       this.messagePackage.add(msg);
					continue; // branch back to while (rset.next())
				}

				/****
				 * The Department in RIS is in GRIIDC find and update the GRIIDC
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

					if (DepartmentSynchronizer.isDebug())
					       this.messagePackage.add(e.getMessage());
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					msg = "Error D-3 in RIS Departments - record id: "
							+ this.risDeptId + ": " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					errorMessageOut(msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;

					if (DepartmentSynchronizer.isDebug())
					       this.messagePackage.add(msg);
					continue; // branch back to while (rset.next())
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
					errorMessageOut(e.getMessage());
					this.risRecordErrors++;
					this.risRecordsSkipped++;

					if (DepartmentSynchronizer.isDebug())
					       this.messagePackage.add(e.getMessage());
					continue; // branch back to while (rset.next())
				} catch (MissingArgumentsException e) {
					msg = "Error D-4 In RIS Departments - record: " + risDeptId
							+ " - " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					errorMessageOut(msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;

					if (DepartmentSynchronizer.isDebug())
					       this.messagePackage.add(msg);
					continue; // branch back to while (rset.next())
				}

				tempDeliveryPoint = MiscUtils.makeDeliveryPoint(
						this.risDeptAddr1, this.risDeptAddr2);

				//
				// read the GRIIDC database to see if the Department record has
				// been stored previously
				// If it is not found - add it @see the NoRecordFoundException
				// If it IS found modify it if it is NOT equal to the existing
				// record
				try {
					// find the griidc dept (within the Institution) that
					// matches the ris dept
					correspondingGriidcDepartmentNum = RdbmsConstants.NotFound;
					correspondingGriidcDepartmentNum = findMatchingGriidcDepartment(
							this.risDeptId, correspondingGriidcInstitutionNum);
					if (isDebug())
						this.messagePackage
								.add("Found Griidc Department record with corresponding Griidc Dept Number: "
										+ correspondingGriidcDepartmentNum);
					modifyGriidcDepartment(correspondingGriidcInstitutionNum,
							tempDeliveryPoint, tempPostalAreaNumber);
				} catch (NoRecordFoundException e) {
					if(DepartmentSynchronizer.isDebug()) {
						this.messagePackage.add("Record NOT found");
					}
					this.addGriidcDepartment(correspondingGriidcInstitutionNum,
							tempDeliveryPoint, tempPostalAreaNumber);
				} catch (MultipleRecordsFoundException e) {
					msg = "Error D-5 In RIS Departments - record RIS Department ID: "
							+ risDeptId + " - " + e.getMessage();
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

		String msg = "Read RIS: " + "R Dept: " + risDeptId + ", "
				+ "R DeptInstId: " + risDeptInstId + ", " + "Name : "
				+ risDeptName + ", " + "Addr 1: " + risDeptAddr1 + ", "
				+ "Addr 2: " + risDeptAddr2 + ", " + "City: " + risDeptCity
				+ ", " + "State: " + risDeptState + ", " + "Zip: " + risDeptZip
				+ ", " + "Country: " + risDeptCountry + ", " + "URL: "
				+ risDeptURL + ", " + "Lat: " + risDeptLat + ", " + "lon: "
				+ risDeptLong;
		this.messagePackage.initialize();
		this.messagePackage.add("\nMessage package RDN: " + risDeptId + ", RDIN: " + risDeptInstId);
		this.messagePackage.add(msg);
		// if (isDebug() || ShowRisRead)
		// System.out.println("\n" + msg);
	}

	private void invalidInstitutionReference(String exMessage) {
		String msg = "Error D-6 in RIS Departments - record id: "
				+ this.risDeptId
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
	 * Format the query to find a GRIIDC Department record that matches the two
	 * keys of RIS Department ID and Griidc Institution number
	 * 
	 * @param targetRisDeptId
	 * @param targetGriidcInstitutionNum
	 * @return
	 */
	private String formatFindQuery(int targetRisDeptId,
			int targetGriidcInstitutionNum) {
		return "SELECT * FROM "
				// + this.getWrappedGriidcShemaName() + "."
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes("Department_RIS_ID")
				+ RdbmsConstants.EqualSign + targetRisDeptId
				+ RdbmsConstants.And
				+ RdbmsConnection.wrapInDoubleQuotes("Institution_Number")
				+ RdbmsConstants.EqualSign + targetGriidcInstitutionNum;
	}

	/**
	 * read the GRIIDC department. The GRIIDC Department_Number is unique but
	 * there may not yet be a stored GRIIDC department record. The RIS
	 * Department Id is also unique and so can be used to find the GRIIDC
	 * Department record in the GRIIDC Department Record
	 * 
	 * @param targetRisDeptId
	 * @return the GRIIDC Department number
	 */
	private int findMatchingGriidcDepartment(int risDepartmentId,
			int griidcInstitutionNum) throws NoRecordFoundException,
			MultipleRecordsFoundException {

		String query = formatFindQuery(risDepartmentId, griidcInstitutionNum);
		int count = 0;
		if(DepartmentSynchronizer.isDebug()) {
			this.messagePackage.add("Find griidc department() looking for risDeptId: " + risDepartmentId + ", GRIIDC dept Inst: " + griidcInstitutionNum);
		}
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
		// if you get this far the record has been stored previously
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
			this.messagePackage.add(msg);
		}
		String addQuery = null;
		try {
			addQuery = this.formatAddQuery(griidcInstNumber,
					tempPostalAreaNumber, tempDeliveryPoint, this.risDeptName,this.risDeptId, 
					this.risDeptURL, this.risDeptLong, this.risDeptLat);
			if (DepartmentSynchronizer.isDebug())
				this.messagePackage.add("Query: " + addQuery);
			this.griidcDbConnection.executeQueryBoolean(addQuery);
			this.griidcRecordsAdded++;
			this.messagePackage.add(msg);
		} catch (SQLException e) {
			msg = "SQL Error: Adding Department in GRIIDC - \nQuery: "
					+ addQuery + "\n" + e.getMessage();
			if (DepartmentSynchronizer.isDebug()) {
				this.messagePackage.add(msg);
				this.messagePackage.toErr();
				this.messagePackage.initialize();
			}
			MiscUtils.writeToPrimaryLogFile(msg);
			errorMessageOut(msg);
		}
	}

	/**
	 * the RIS record matches one in GRIIDC Modify Department record by keys. If
	 * the data values are euqual there is no need to modify. If not some values
	 * may have changed and need to updated.
	 * 
	 * @param tempDeliveryPoint
	 * @param tempPostalAreaNumber
	 */
	private void modifyGriidcDepartment(int mappedGriidcInstNumber,
			String tempDeliveryPoint, int tempPostalAreaNumber) {
		String msg = null;
		if (isCurrentRecordEqual(mappedGriidcInstNumber, tempPostalAreaNumber,
				tempDeliveryPoint, this.risDeptName, this.risDeptURL,

				this.griidcDeptInstNumber, this.griidcDeptPostalAreaNumber,
				this.griidcDeptDeliveryPoint, this.griidcDeptName,
				this.griidcDeptUrl)) {
			this.griidcRecordDuplicates++;
			errorMessageOut("Duplicate record");
			if (DepartmentSynchronizer.isDebug()) {
				this.messagePackage
						.add("DepartmentSynchronizer.modifyGriidcDepartmen Record is found - a duplicate");
			}
		} else { // not equal (other than keys) so modify
			if (DepartmentSynchronizer.isDebug()) {
				msg = "Modify GRIIDC Department table matching "
						+ "griidcDeptNumber: " + this.griidcDeptNumber
						+ "griidcRis_DEPT_ID: " + this.griidcDept_RIS_ID
						+ "griidcDeptInstNumber: " + mappedGriidcInstNumber
						+ ", Department_Name: " + risDeptName
						+ ", PostalArea_Number: " + griidcDeptPostalAreaNumber
						+ ", Department_DeliveryPoint: " + tempDeliveryPoint
						+ " \n<><> because: " + getWhyFailedEqual();
				this.messagePackage.add(msg);
			}

			String modifyQuery = null;
			try {
				modifyQuery = this.formatModifyQuery(this.griidcDeptNumber,
						this.griidcDeptInstNumber, tempPostalAreaNumber,
						tempDeliveryPoint, this.risDeptName,this.risDeptId, risDeptURL,
						risDeptLong, risDeptLat);

				if (DepartmentSynchronizer.isDebug())
					this.messagePackage.add("Query: " + modifyQuery);
				this.griidcDbConnection.executeQueryBoolean(modifyQuery);
				this.griidcRecordsModified++;
				successMessageOut(msg);
			} catch (SQLException e) {
				msg = "SQL Error: Modify Department in GRIIDC - Query: "
						+ modifyQuery;
				this.messagePackage.add(msg);
				this.messagePackage.toOut();
				e.printStackTrace();
				errorMessageOut(msg);
			}
		}
		return;
	}

	/**
	 * if any of the parameter pairs don't match they are not equal This looks
	 * at the payload of the record not the key
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
		whyFailedEqualMessage = "Records Are Equal";

		if (!(mappedGriidcInstNumber == gInstNumber)) {
			whyFailedEqualMessage = "\n\tMapped Inst: "
					+ mappedGriidcInstNumber + " not equal \n\tG Inst: "
					+ gInstNumber;
			return false;
		}
		if (!(rName.equals(gName))) {
			whyFailedEqualMessage = "\n\tR name: " + rName
					+ " not equal \n\tG name: " + gName;
			return false;
		}
		if (!areDeliveryPointsEqual(rDeliveryPoint, gDeliveryPoint)) {
			whyFailedEqualMessage = "\n\tR deliver point: " + rDeliveryPoint
					+ " not equal \n\tG deliver point: " + gDeliveryPoint;
			return false;
		}
		if (!(rPostalAreaNumber == gPostalAreaNumber)) {
			whyFailedEqualMessage = "\n\tR postal area : " + rPostalAreaNumber
					+ " not equal \n\tG postal area: " + gPostalAreaNumber;
			return false;
		}
		if (!(rUrl.equals(gUrl))) {
			whyFailedEqualMessage = "\n\tR url : " + rUrl
					+ " not equal \n\tG url: " + gUrl;
			return false;
		}
		return true;

	}

	private static String whyFailedEqualMessage = "Equal";

	private static String getWhyFailedEqual() {
		return whyFailedEqualMessage;
	}

	/**
	 * compare for equal if both are null return true; if neither are null
	 * compare trimmed values if one is null other not return false; both blank
	 * (empty not null) will return true
	 * 
	 * @param rdp
	 * @param gdp
	 * @return
	 */
	private boolean areDeliveryPointsEqual(String rdp, String gdp) {
		if (rdp == null && gdp == null)
			return true;
		if (rdp != null && gdp != null) {
			// both are empty but non null
			if (MiscUtils.isEmpty(rdp) && MiscUtils.isEmpty(gdp))
				return true;
			return rdp.trim().equals(gdp.trim());
		}
		return false;
	}

	private boolean isInstitutionOnRisErrorsList(int risDeptInstId) {
		return this.risInstitutionWithErrors.containsInstitution(risDeptInstId);
	}

	private DbColumnInfo[] getDbColumnInfo(int lgriidcDepartmentNum,
			int lgriidcDepartmentInstNum, int lgriidcPostalAreaNumber,
			String ldeliveryPoint, String lrisDeptName, int lrisDeptId, String lrisDeptURL,
			double lrisDeptLon, double lrisDeptLat) throws SQLException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);

		if(lgriidcDepartmentNum > 0) {
		tci.getDbColumnInfo("Department_Number").setColValue(
				String.valueOf(lgriidcDepartmentNum));
		}
		tci.getDbColumnInfo("Institution_Number").setColValue(
				String.valueOf(lgriidcDepartmentInstNum));
		tci.getDbColumnInfo("PostalArea_Number").setColValue(
				String.valueOf(lgriidcPostalAreaNumber));
		tci.getDbColumnInfo("Department_DeliveryPoint").setColValue(
				ldeliveryPoint);
		tci.getDbColumnInfo("Department_Name").setColValue(lrisDeptName);
		tci.getDbColumnInfo("Department_RIS_ID").setColValue(lrisDeptId);
		tci.getDbColumnInfo("Department_URL").setColValue(lrisDeptURL);
		tci.getDbColumnInfo("Department_GeoCoordinate").setColValue(
				RdbmsUtils.makeSqlGeometryPointString(lrisDeptLon, lrisDeptLat));
		return tci.getDbColumnInfo();
	}

	private String formatAddQuery(int lGriidcDeptInstNumber,
			int lGriidcPostalAreaNumber, String lDeliveryPoint,
			String lRisDeptName, int lRisDeptId, String lRisDeptURL, double lRisDeptLon,
			double lRisDeptLat) throws SQLException {

		DbColumnInfo[] info = getDbColumnInfo(-1, lGriidcDeptInstNumber,
				lGriidcPostalAreaNumber, lDeliveryPoint, lRisDeptName, lRisDeptId, lRisDeptURL,
				lRisDeptLon, lRisDeptLat);
		String query = RdbmsUtils.formatInsertStatement(GriidcTableName, info);
		return query;

	}

	private String formatModifyQuery(int lgriidcDepartmentNum,int lGriidcDeptInstNumber,
			int lGriidcPostalAreaNumber, String lDeliveryPoint,
			String lRisDeptName, int lRisDeptId, String lRisDeptURL, double lRisDeptLon,
			double lRisDeptLat) throws SQLException {

		DbColumnInfo[] info = getDbColumnInfo(lgriidcDepartmentNum,
				lGriidcDeptInstNumber, lGriidcPostalAreaNumber, lDeliveryPoint,
				lRisDeptName, lRisDeptId, lRisDeptURL, lRisDeptLon, lRisDeptLat);
		DbColumnInfo[] whereInfo = new DbColumnInfo[2];

		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);

		whereInfo[0] = tci.getDbColumnInfo("Department_Number");
		whereInfo[0].setColValue(lgriidcDepartmentNum);
		whereInfo[1] = tci.getDbColumnInfo("Institution_Number");
		whereInfo[1].setColValue(lGriidcDeptInstNumber);

		if (DepartmentSynchronizer.isDebug())
			this.messagePackage
					.add("formatModifyQuery() where clause whereInfo "
							+ DbColumnInfo.toString(whereInfo));
		String query = RdbmsUtils.formatUpdateStatement(
				DepartmentSynchronizer.GriidcTableName, info, whereInfo);

		if (DepartmentSynchronizer.isDebug())
			this.messagePackage.add("formatModifyQuery() " + query);
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
		return GriidcConfiguration.getPrimaryLogFileName();
	}

	public String getRisErrorLogFileName() throws PropertyNotFoundException,
			InvalidFileFormatException, IOException {
		return GriidcConfiguration.getRisErrorLogFileName();
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
