package edu.tamucc.hri.griidc.ris;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.MultipleRecordsFoundException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.SynchronizerBase;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.utils.GriidcRisInstitutionMap;
import edu.tamucc.hri.griidc.utils.MiscUtils;
import edu.tamucc.hri.griidc.utils.RisInstDeptPeopleErrorCollection;
import edu.tamucc.hri.griidc.utils.GriidcConfiguration;

/**
 * reads RIS Institutions records and converts to GRIIDC Institution. Store the
 * RIS id and use it for future updates from RIS.
 * 
 * @author jvh
 * 
 */
public class InstitutionSynchronizer extends SynchronizerBase {

	public static final String RisTableName = RdbmsConstants.RisInstTableName;
	public static final String GriidcTableName = RdbmsConstants.GriidcInstTableName;

	private int risRecordCount = 0;
	//private int risRecordsSkipped = 0;
	private int risRecordErrors = 0;
	private int risRecordWarnings = 0;
	private int griidcRecordsAdded = 0;
	private int griidcRecordsModified = 0;
	private int griidcRecordDuplicates = 0;

	private int risInstId = -1;
	private String risInstName = null;
	private String risInstAddr1 = null;
	private String risInstAddr2 = null;
	private String risInstCity = null;
	private String risInstState = null;
	private String risInstZip = null;
	private String risInstCountry = null;
	private String risInstURL = null;
	private double risInstLat = 0.0;
	private double risInstLong = 0.0;
	// String risInstKeywords = null;
	// String risInstVerified = null;

	// GRIIDC Institution stuff
	private int griidcInstNumber = -1;
	private int griidcInstRisId = -1;
	private int griidcInstPostalAreaNumber = -1;
	private String griidcInstAbbr = null;
	private String griidcInstDeliveryPoint = null;
	private String griidcInstName = null;
	private String griidcInstUrl = null;
	private String griidcInstGeoCoordinate = null;

	// get all the values from the RIS Departments table

	private ResultSet rset = null;
	private ResultSet griidcRset = null;

	private static boolean debug = false;

	private static boolean FuzzyPostalCode = false;

	private boolean initialized = false;

	private boolean warningsOn = false; // true is more tolerant of missing data
										// in RIS
	private static final String Error = "Error ";
	private static final String Warning = "Warning ";

	private RisInstDeptPeopleErrorCollection risInstitutionWithErrors = new RisInstDeptPeopleErrorCollection();

	public InstitutionSynchronizer() {

	}

	public void initialize() {
		if (!isInitialized()) {
			super.commonInitialize();
			if (GriidcConfiguration.isFuzzyPostalCodeTrue())
				InstitutionSynchronizer.setFuzzyPostalCode(true);
			initialized = true;
		}
	}

	public boolean isInitialized() {
		return initialized;
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
	public RisInstDeptPeopleErrorCollection syncGriidcInstitutionFromRisInstitution()
			throws ClassNotFoundException, IOException, SQLException,
			TableNotInDatabaseException, PropertyNotFoundException {
		if (isDebug())
			System.out.println(MiscUtils.BreakLine);

		this.initialize();

		String tempDeliveryPoint = null; // created from RIS info
		int tempPostalAreaNumber = -1; // created from RIS info

		String errorOrWarning = Error;
		try {
			rset = this.risDbConnection.selectAllValuesFromTable(RisTableName);

			while (rset.next()) { // continue statements branch back to here
				this.readRisRecord();

				int countryNumber = -1;
				if (MiscUtils.isStringEmpty(risInstCountry)) {
					MiscUtils
							.writeToRisErrorLogFile("Error I-A In RIS Institutions record: "
									+ risInstId
									+ " - Institution_Country is "
									+ ((risInstCountry == null) ? "null"
											: " lenght zero"));
					this.risRecordErrors++;
					//this.risRecordsSkipped++;
					this.risInstitutionWithErrors.addInstitution(risInstId);
					continue; // skip to while (rset.next())
				}
				try {
					countryNumber = RdbmsUtils
							.getCountryNumberFromName(risInstCountry);
				} catch (MultipleRecordsFoundException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					MiscUtils
							.writeToRisErrorLogFile("Error I-B In RIS Institutions record: "
									+ risInstId + e.getMessage());
					//this.risRecordsSkipped++;
					this.risRecordErrors++;
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					errorOrWarning = Error;
					if (this.isWarningsOn())
						errorOrWarning = Warning;
					String msg = errorOrWarning
							+ " I-C in RIS Institutions record id: "
							+ risInstId + ": " + e.getMessage();
					MiscUtils.writeToPrimaryLogFile(msg);

					if (this.isWarningsOn()) {
						this.risRecordWarnings++;
						MiscUtils.writeToWarningLogFile(msg);
					} else {
						this.risRecordErrors++;
						MiscUtils.writeToRisErrorLogFile(msg);
						this.risInstitutionWithErrors.addInstitution(risInstId);
						continue; // branch back to while (rset.next())
					}
				}

				/****
				 * find and update the GRIIDC Institution table with these
				 * values
				 */
				tempPostalAreaNumber = -1;

				try {
					tempPostalAreaNumber = RdbmsUtils
							.getGriidcDepartmentPostalNumber(countryNumber,
									risInstState, risInstCity, risInstZip);
				} catch (MultipleRecordsFoundException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					String msg = "Error I-D in RIS Institutions record id: "
							+ risInstId + ": " + e.getMessage();
					//this.risRecordsSkipped++;
					this.risRecordErrors++;
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					errorOrWarning = Error;
					if (this.isWarningsOn())
						errorOrWarning = Warning;
					String msg = errorOrWarning
							+ " I-E in RIS Institutions record id: "
							+ risInstId + ": " + e.getMessage();
					MiscUtils.writeToPrimaryLogFile(msg);
					if (this.isWarningsOn()) {
						MiscUtils.writeToWarningLogFile(msg);
						this.risRecordWarnings++;
					} else {
						MiscUtils.writeToRisErrorLogFile(msg);
						this.risRecordErrors++;
						this.risInstitutionWithErrors.addInstitution(risInstId);
						continue; // branch back to while (rset.next())
					}
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
					String msg = "Error I-F in RIS Institutions record id: "
							+ risInstId + ": " + e.getMessage();
					//this.risRecordsSkipped++;
					this.risRecordErrors++;
					continue; // branch back to while (rset.next())
				} catch (MissingArgumentsException e) {
					if (this.isWarningsOn())
						errorOrWarning = Warning;
					String msg = errorOrWarning
							+ "I-G In RIS Institutions record: "
									+ risInstId + " - " + e.getMessage();
					if (this.isWarningsOn()) {
						MiscUtils.writeToWarningLogFile(msg);
						this.risRecordWarnings++;
					} else {
						this.risRecordErrors++;
						MiscUtils.writeToRisErrorLogFile(msg);
						this.risInstitutionWithErrors.addInstitution(risInstId);
						continue; // branch back to while (rset.next())
					}
				}
				/*
				 * if the data in RIS is unusable - skip this record - go to the
				 * next record *
				 */
				/**                                                            **/
				tempDeliveryPoint = MiscUtils.makeDeliveryPoint(risInstAddr1,
						risInstAddr2);
				if(tempDeliveryPoint == null) {
					String msg = errorOrWarning
							+ "I-H In RIS Institutions record: "
									+ risInstId + " - " + " Address 1 AND Address 2 are null or blank";
					MiscUtils.writeToRisErrorLogFile(msg);
					this.risRecordErrors++;
					this.risInstitutionWithErrors.addInstitution(risInstId);
					continue; // branch back to while (rset.next())
				}

				//
				// find matching GRIIDC record
				//

				int count = this.findMatchingGriidcInstitution();

				// if a matching record is not found - add a record
				if (count == 0) {
					// Add the Institution
					if (InstitutionSynchronizer.isDebug()) {
						String msg = "Add GRIIDC Institution table record "
								+ "RIS Institution ID: " + risInstId
								+ ", Institution_Name: " + risInstName
								+ ", PostalArea_Number: "
								+ griidcInstPostalAreaNumber
								+ ", Institution_DeliveryPoint: "
								+ tempDeliveryPoint;
						System.out.println(msg);
					}
					String addQuery = null;
					try {
						addQuery = this.formatAddQuery(risInstId, risInstName,
								tempPostalAreaNumber, tempDeliveryPoint,
								risInstURL, risInstLong, risInstLat);
						if (InstitutionSynchronizer.isDebug())
							System.out.println("Query: " + addQuery);
						this.griidcDbConnection.executeQueryBoolean(addQuery);
						String msg = "Added Institution record: "
								+ griidcInstitutionToString(risInstId,
										risInstName, tempPostalAreaNumber,
										tempDeliveryPoint, risInstURL,
										risInstLong, risInstLat);
						MiscUtils.writeToPrimaryLogFile(msg);
						if (isDebug())
							System.out.println(msg);
						// read again to get the assigned key
						// (Institution_Number)
						this.findMatchingGriidcInstitution();
						this.griidcRecordsAdded++;
					} catch (SQLException e) {
						System.err
								.println("SQL Error: Add Institution in GRIIDC - Query: "
										+ addQuery);
						e.printStackTrace();
					}

				} else if (count == 1) { // found a match - modify the record if
											// needed

					// Modify Institution record
					if (isCurrentRecordEqual(risInstId,
							risInstName,
							tempPostalAreaNumber,
							tempDeliveryPoint,
							risInstURL, // risInstLong, risInstLat,
							griidcInstRisId, griidcInstName,
							griidcInstPostalAreaNumber,
							griidcInstDeliveryPoint, griidcInstUrl)) {
						this.griidcRecordDuplicates++;
					} else { // the data has changed - must modify
						if (InstitutionSynchronizer.isDebug()) {
							String msg = "Modify GRIIDC Institution table matching "
									+ "Institution_RIS_ID: "
									+ risInstId
									+ ", Institution_Name: "
									+ risInstName
									+ ", PostalArea_Number: "
									+ griidcInstPostalAreaNumber
									+ ", Institution_DeliveryPoint: "
									+ tempDeliveryPoint;
							System.out.println(msg);
						}

						String modifyQuery = null;
						try {
							modifyQuery = this.formatModifyQuery(risInstId,
									risInstName, griidcInstPostalAreaNumber,
									tempDeliveryPoint, risInstURL, risInstLong,
									risInstLat);

							System.out.println("Query: " + modifyQuery);
							this.griidcDbConnection
									.executeQueryBoolean(modifyQuery);
							String msg = "Modified Institution record: "
									+ griidcInstitutionToString(risInstId,
											risInstName, tempPostalAreaNumber,
											tempDeliveryPoint, risInstURL,
											risInstLong, risInstLat);
							MiscUtils.writeToPrimaryLogFile(msg);
							if (isDebug())
								System.out.println(msg);
						} catch (SQLException e) {
							System.err
									.println("SQL Error: Modify Institution in GRIIDC - Query: "
											+ modifyQuery);
							e.printStackTrace();
						}
						this.griidcRecordsModified++;
					}
				} else if (count > 1) { // duplicates in the database - should
										// not happen

					String msg = "There are "
							+ count
							+ " records in the  GRIIDC Institution table matching "
							+ "Institution_Name: " + risInstName
							+ ", PostalArea_Number: "
							+ griidcInstPostalAreaNumber
							+ ", Institution_DeliveryPoint: "
							+ tempDeliveryPoint;
					if (InstitutionSynchronizer.isDebug())
						System.out.println(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
				}
			} // end of main while loop
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		// end of Institution
		return this.risInstitutionWithErrors;
	}

	private void readRisRecord() throws SQLException {
		this.risRecordCount++;
		this.risInstId = rset.getInt("Institution_ID");
		this.risInstName = rset.getString("Institution_Name").trim();
		this.risInstAddr1 = rset.getString("Institution_Addr1").trim();
		this.risInstAddr2 = rset.getString("Institution_Addr2").trim();
		this.risInstCity = rset.getString("Institution_City").trim();
		this.risInstState = rset.getString("Institution_State").trim();
		this.risInstZip = rset.getString("Institution_Zip").trim();
		this.risInstCountry = rset.getString("Institution_Country").trim();
		this.risInstURL = rset.getString("Institution_URL").trim();
		this.risInstLat = rset.getDouble("Institution_Lat");
		this.risInstLong = rset.getDouble("Institution_Long");
		// risInstKeywords = rset.getString("Institution_Keywords");
		// risInstVerified = rset.getString("Institution_Verified");
	}

	private DbColumnInfo[] getDbColumnInfo(int risInstId, String risInstName,
			int griidcPostalAreaNumber, String deliveryPoint,
			String risInstURL, double risInstLon, double risInstLat)
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);

		tci.getDbColumnInfo("Institution_RIS_ID").setColValue(
				String.valueOf(risInstId));
		tci.getDbColumnInfo("Institution_Name").setColValue(risInstName);
		tci.getDbColumnInfo("PostalArea_Number").setColValue(
				String.valueOf(griidcPostalAreaNumber));
		tci.getDbColumnInfo("Institution_DeliveryPoint").setColValue(
				deliveryPoint);
		tci.getDbColumnInfo("Institution_URL").setColValue(risInstURL);
		tci.getDbColumnInfo("Institution_GeoCoordinate").setColValue(
				String.valueOf(RdbmsUtils.makeSqlGeometryPointString(
						risInstLon, risInstLat)));
		return tci.getDbColumnInfo();
	}

	/**
	 * read the database for the Griidc Institution that corresponds to the RIS
	 * Institutions record. The RIS Institution ID is stored in the Griidc
	 * Institution record
	 * 
	 * @return
	 */
	private int findMatchingGriidcInstitution() {
		//
		// find matching GRIIDC record
		//
		int count = 0;
		String query = null;
		try {
			query = this.formatFindQuery();

			this.griidcRset = this.griidcDbConnection
					.executeQueryResultSet(query);

			while (griidcRset.next()) {
				count++;
				griidcInstRisId = griidcRset.getInt("Institution_RIS_ID");
				griidcInstName = griidcRset.getString("Institution_Name");
				griidcInstNumber = griidcRset.getInt("Institution_Number");
				griidcInstPostalAreaNumber = griidcRset
						.getInt("PostalArea_Number");
				griidcInstAbbr = griidcRset.getString("Institution_Abbr");
				griidcInstDeliveryPoint = griidcRset
						.getString("Institution_DeliveryPoint");
				griidcInstUrl = griidcRset.getString("Institution_URL");
				griidcInstGeoCoordinate = griidcRset
						.getString("Institution_GeoCoordinate");
			}
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return count;
	}

	private String formatFindQuery() {
		String query = "SELECT * FROM "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes("Institution_RIS_ID")
				+ RdbmsConstants.EqualSign + risInstId;
		return query;
	}

	private String formatAddQuery(int risInstId, String risInstName,
			int griidcPostalAreaNumber, String deliveryPoint,
			String risInstURL, double risInstLon, double risInstLat)
			throws SQLException, ClassNotFoundException, FileNotFoundException,
			PropertyNotFoundException {

		DbColumnInfo[] info = getDbColumnInfo(risInstId, risInstName,
				griidcPostalAreaNumber, deliveryPoint, risInstURL, risInstLon,
				risInstLat);
		String query = RdbmsUtils.formatInsertStatement(
				InstitutionSynchronizer.GriidcTableName, info);
		if (InstitutionSynchronizer.isDebug())
			System.out.println("formatAddQuery() " + query);
		return query;
	}

	private String formatModifyQuery(int risInstId, String risInstName,
			int postalAreaNumber, String deliveryPoint, String instURL,
			double instLon, double instLat) throws SQLException,
			ClassNotFoundException, FileNotFoundException,
			PropertyNotFoundException {

		DbColumnInfo[] info = getDbColumnInfo(risInstId, risInstName,
				postalAreaNumber, deliveryPoint, instURL, instLon, instLat);

		DbColumnInfo[] whereInfo = new DbColumnInfo[1];

		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);

		tci.getDbColumnInfo("Institution_RIS_ID").setColValue(
				String.valueOf(risInstId));

		whereInfo[0] = tci.getDbColumnInfo("Institution_RIS_ID");

		String query = RdbmsUtils.formatUpdateStatement(
				InstitutionSynchronizer.GriidcTableName, info, whereInfo);

		if (InstitutionSynchronizer.isDebug())
			System.out.println("formatModifyQuery() " + query);
		return query;
	}

	/**
	 * if any of the parameter pairs don't match they are not equal
	 * 
	 * @param rNumber
	 * @param rName
	 * @param rPostalAreaNumber
	 * @param rDeliveryPoint
	 * @param rUrl
	 * @param rLon
	 * @param rLat
	 * @param gNumber
	 * @param gName
	 * @param gPostalAreaNumber
	 * @param gDeliveryPoint
	 * @param gUrl
	 * @param gLon
	 * @param gLat
	 * @return
	 */
	private boolean isCurrentRecordEqual(int risInstId, String risInstName,
			int risPostalAreaNumber, String risDeliveryPoint,
			String risUrl, // , double rLon,double rLat,

			int gInstRisId, String gInstName, int gInstPostalAreaNumber,
			String gInstDeliveryPoint, String gInstUrl) { // , double
															// gLon,double gLat)
															// {

		if (risInstId != gInstRisId)
			return false;
		if (!risInstName.equals(gInstName))
			return false;
		if (risPostalAreaNumber != gInstPostalAreaNumber)
			return false;
		if (!risDeliveryPoint.equals(gInstDeliveryPoint))
			return false;
		if (!risUrl.equals(gInstUrl))
			return false;
		// if(rLon != gLon) return false;
		// if(rLat != gLat) return false;
		return true;
	}

	private String griidcInstitutionToString(int instNumber, String instName,
			int postalAreaNumber, String deliveryPoint, String instURL,
			double instLon, double instLat) {
		return "Inst Num: " + instNumber + ", " + "Inst Name: " + instName
				+ ", " + "Inst postal area: " + postalAreaNumber + ", "
				+ "Inst delivery point: " + deliveryPoint + ", " + "Inst URL: "
				+ instURL + ", " + "Inst Lon: " + instLon + ", " + "Inst Lat "
				+ instLat;
	}

	public void reportDepartmentTable() throws IOException,
			PropertyNotFoundException, SQLException, ClassNotFoundException,
			TableNotInDatabaseException {
		RdbmsUtils.reportTables(RisTableName, GriidcTableName);
		return;
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

	public int getRisRecordErrors() {
		return risRecordErrors;
	}

	public int getRisRecordWarnings() {
		return risRecordWarnings;
	}
	public int getRisRecordCount() {
		return risRecordCount;
	}

	public static boolean isDebug() {
		return InstitutionSynchronizer.debug;
	}

	public static void setDebug(boolean debug) {
		InstitutionSynchronizer.debug = debug;
	}

	public static boolean isFuzzyPostalCode() {
		return FuzzyPostalCode;
	}

	public static void setFuzzyPostalCode(boolean fuzzyPostalCode) {
		FuzzyPostalCode = fuzzyPostalCode;
	}

	public boolean isWarningsOn() {
		return warningsOn;
	}

	public void setWarningsOn(boolean warningsOn) {
		this.warningsOn = warningsOn;
	}

}
