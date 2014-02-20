package edu.tamucc.hri.griidc;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.DuplicateRecordException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.griidc.support.RisInstDeptPeopleErrorCollection;
import edu.tamucc.hri.griidc.support.RisToGriidcConfiguration;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

public class InstitutionSynchronizer {

	private static final String RisTableName = "Institutions";
	private static final String GriidcTableName = "Institution";

	private RdbmsConnection risDbConnection = null;
	private RdbmsConnection griidcDbConnection = null;
	private RdbmsConnection griidcTempDbConnection = null;

	private int risRecordCount = 0;
	private int risRecordsSkipped = 0;
	private int risRecordErrors = 0;
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
	private int griidcInstPostalAreaNumber = -1;
	private String griidcInstAbbr = null;
	private String griidcInstDeliveryPoint = null;
	private String griidcInstName = null;
	private String griidcInstUrl = null;
	private String griidcInstGeoCoordinate = null;
	private double griidcInstLongitude = 0.0;
	private double griidcInstLatitude = 0.0;
	private String query = null;

	// get all the values from the RIS Departments table

	private ResultSet rset = null;
	private ResultSet griidcRset = null;

	private static boolean debug = false;

	private static boolean FuzzyPostalCode = false;

	private RisInstDeptPeopleErrorCollection risInstitutionWithErrors = new RisInstDeptPeopleErrorCollection();

	public InstitutionSynchronizer() {

	}

	public void initializeStartUp() throws IOException,
			PropertyNotFoundException, SQLException, ClassNotFoundException {
		MiscUtils.openPrimaryLogFile();
		MiscUtils.openRisErrorLogFile();
		MiscUtils.openDeveloperReportFile();
		this.risDbConnection = RdbmsUtils.getRisDbConnectionInstance();
		this.griidcDbConnection = RdbmsUtils.getGriidcDbConnectionInstance();
		if (RisToGriidcConfiguration.isFuzzyPostalCodeTrue())
			InstitutionSynchronizer.setFuzzyPostalCode(true);
	}

	/*****
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws IOException
	 * @throws TableNotInDatabaseException 
	 * @throws NoRecordFoundException
	 * @throws DuplicateRecordException
	 */
	public RisInstDeptPeopleErrorCollection syncGriidcInstitutionFromRisInstitution()
			throws ClassNotFoundException, PropertyNotFoundException,
			IOException, SQLException, TableNotInDatabaseException {
		if (isDebug()) System.out.println(MiscUtils.BreakLine);
		
		this.initializeStartUp();

		String tempDeliveryPoint = null; // created from RIS info
		int tempPostalAreaNumber = -1; // created from RIS info

		try {
			rset = this.risDbConnection.selectAllValuesFromTable(RisTableName);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		try {

			while (rset.next()) { // continue statements branch back to here
				risRecordCount++;
				risInstId = rset.getInt("Institution_ID");
				risInstName = rset.getString("Institution_Name").trim();
				risInstAddr1 = rset.getString("Institution_Addr1").trim();
				risInstAddr2 = rset.getString("Institution_Addr2").trim();
				risInstCity = rset.getString("Institution_City").trim();
				risInstState = rset.getString("Institution_State").trim();
				risInstZip = rset.getString("Institution_Zip").trim();
				risInstCountry = rset.getString("Institution_Country").trim();
				risInstURL = rset.getString("Institution_URL").trim();
				risInstLat = rset.getDouble("Institution_Lat");
				risInstLong = rset.getDouble("Institution_Long");
				// risInstKeywords = rset.getString("Institution_Keywords");
				// risInstVerified = rset.getString("Institution_Verified");

				int countryNumber = -1;
				if (MiscUtils.isStringEmpty(risInstCountry)) {
					MiscUtils
							.writeToRisErrorLogFile("Error In RIS Institutions record: "
									+ risInstId
									+ " - Institution_Country is "
									+ ((risInstCountry == null) ? "null"
											: " lenght zero"));
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					this.risInstitutionWithErrors.addInstitution(risInstId);
					continue; // skip to while (rset.next())
				}
				try {
					countryNumber = RdbmsUtils
							.getCountryNumberFromName(risInstCountry);
				} catch (DuplicateRecordException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					if (isDebug())
						System.err.println("AA Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					String msg = "Error in RIS Institutions record id: " + risInstId + ": " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					if (isDebug())
						System.err.println("BB Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					this.risRecordErrors++;
					this.risInstitutionWithErrors.addInstitution(risInstId);
					continue; // branch back to while (rset.next())
				}

				/****
				 * find and update the GRIIDC Institution table with these
				 * values
				 */
				tempPostalAreaNumber = -1;

				try {
					tempPostalAreaNumber = RdbmsUtils.getGriidcDepartmentPostalNumber(
							countryNumber, risInstState, risInstCity,
							risInstZip);
				} catch (DuplicateRecordException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					if (isDebug())
						System.err.println("CC Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					String msg = "Error in RIS Institutions record id: " + risInstId + ": " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					if (isDebug())
						System.err.println("DD Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					this.risRecordErrors++;
					this.risInstitutionWithErrors.addInstitution(risInstId);
					continue; // branch back to while (rset.next())
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
					if (isDebug())
						System.err.println("EE Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (MissingArgumentsException e) {
					MiscUtils
							.writeToRisErrorLogFile("Error In RIS Institutions record: "
									+ risInstId + " - " + e.getMessage());
					if (isDebug())
						System.err.println("FF Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					this.risRecordErrors++;
					this.risInstitutionWithErrors.addInstitution(risInstId);
					continue; // branch back to while (rset.next())
				}
				/*
				 * if the data in RIS is unusable - skip this record - go to the
				 * next record *
				 */
				/**                                                            **/
				tempDeliveryPoint = MiscUtils.makeDeliveryPoint(risInstAddr1,
						risInstAddr2);

				try {
					query = "SELECT * FROM "
							// + this.getWrappedGriidcShemaName() + "."
							+ RdbmsConnection
									.wrapInDoubleQuotes(GriidcTableName)
							+ " WHERE "
							+ RdbmsConnection
									.wrapInDoubleQuotes("Institution_Number")
							+ RdbmsUtils.EqualSign + risInstId;

					griidcRset = this.griidcDbConnection
							.executeQueryResultSet(query);

				} catch (SQLException e1) {
					System.err
							.println("SQL Error: Find Institution in GRIIDC - Query: "
									+ query);
					e1.printStackTrace();
				}

				int count = 0;
				try {
					while (griidcRset.next()) {
						count++;
						griidcInstName = griidcRset
								.getString("Institution_Name");
						griidcInstNumber = griidcRset
								.getInt("Institution_Number");
						griidcInstPostalAreaNumber = griidcRset
								.getInt("PostalArea_Number");
						griidcInstAbbr = griidcRset
								.getString("Institution_Abbr");
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

				if (count == 0) {
					// Add the Institution
					this.griidcRecordsAdded++;
					if (this.isDebug()) {
						String msg = "Add GRIIDC Institution table record "
								+ "Institution_Name: " + risInstName
								+ ", PostalArea_Number: "
								+ griidcInstPostalAreaNumber
								+ ", Institution_DeliveryPoint: "
								+ tempDeliveryPoint;
						System.out.println(msg);
					}
					String addQuery = null;
					try {
						addQuery = this.formatAddInstitutionQuery(risInstId,
								risInstName, tempPostalAreaNumber,
								tempDeliveryPoint, risInstURL, risInstLong,
								risInstLat);
						if (this.isDebug())
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
					} catch (SQLException e) {
						System.err
								.println("SQL Error: Add Institution in GRIIDC - Query: "
										+ addQuery);
						e.printStackTrace();
					}

				} else if (count == 1) {
					
					// Modify Institution record
					if (isCurrentRecordEqual(risInstId,
							risInstName,
							tempPostalAreaNumber,
							tempDeliveryPoint,
							risInstURL, // risInstLong, risInstLat,
							griidcInstNumber, griidcInstName,
							griidcInstPostalAreaNumber,
							griidcInstDeliveryPoint, griidcInstUrl)) { // ,
																		// griidcInstLongitude,
																		// griidcInstLatitude))
																		// {
						continue; // branch back to while (rset.next())
					}
					this.griidcRecordsModified++;
					if (this.isDebug()) {
						String msg = "Modify GRIIDC Institution table matching "
								+ "griidcInstNumber: "
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
						modifyQuery = this.formatModifyInstitutionQuery(
								risInstId, risInstName,
								griidcInstPostalAreaNumber, tempDeliveryPoint,
								risInstURL, risInstLong, risInstLat);

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

				} else if (count > 1) { // duplicates
					this.griidcRecordDuplicates++;

					String msg = "There are "
							+ count
							+ " records in the  GRIIDC Institution table matching "
							+ "Institution_Name: " + risInstName
							+ ", PostalArea_Number: "
							+ griidcInstPostalAreaNumber
							+ ", Institution_DeliveryPoint: "
							+ tempDeliveryPoint;
					if (this.isDebug())
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

	private String formatAddInstitutionQuery(int risInstNumber,
			String risInstName, int griidcPostalAreaNumber,
			String deliveryPoint, String risInstURL, double risInstLon,
			double risInstLat) throws SQLException, ClassNotFoundException {
		StringBuffer sb = new StringBuffer("INSERT INTO ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Institution")
				+ RdbmsUtils.SPACE + "(");
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Institution_Number"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Institution_Name"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("PostalArea_Number"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection
						.wrapInDoubleQuotes("Institution_DeliveryPoint"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Institution_URL"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection
						.wrapInDoubleQuotes("Institution_GeoCoordinate"));
		sb.append(") VALUES (");
		// the values are here
		sb.append(risInstNumber);
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(risInstName));
		sb.append(RdbmsUtils.CommaSpace + griidcPostalAreaNumber);
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(deliveryPoint));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(risInstURL));
		sb.append(RdbmsUtils.CommaSpace
				+ makeSqlGeometryPointString(risInstLon, risInstLat));
		sb.append(" )");
		return sb.toString();
	}

	private String makeSqlGeometryPointString(double lon, double lat) {
		return " " + "ST_SetSRID(ST_MakePoint(" + lon + "," + lat + "), 4326)";
	}

	private String formatModifyInstitutionQuery(int instNumber,
			String risInstName, int postalAreaNumber, String deliveryPoint,
			String instURL, double instLon, double instLat)
			throws SQLException, ClassNotFoundException {
		StringBuffer sb = new StringBuffer("UPDATE  ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Institution")
				+ RdbmsUtils.SPACE + " SET ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Institution_Name")
				+ RdbmsUtils.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(risInstName));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("PostalArea_Number")
				+ RdbmsUtils.EqualSign + postalAreaNumber);
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection
						.wrapInDoubleQuotes("Institution_DeliveryPoint")
				+ RdbmsUtils.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(deliveryPoint));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Institution_URL")
				+ RdbmsUtils.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(instURL));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection
						.wrapInDoubleQuotes("Institution_GeoCoordinate")
				+ RdbmsUtils.EqualSign
				+ makeSqlGeometryPointString(instLon, instLat));
		sb.append(" WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes("Institution_Number")
				+ RdbmsUtils.EqualSign + instNumber);
		return sb.toString();
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
	private boolean isCurrentRecordEqual(int rNumber, String rName,
			int rPostalAreaNumber, String rDeliveryPoint,
			String rUrl, // double rLon,double rLat,

			int gNumber, String gName, int gPostalAreaNumber,
			String gDeliveryPoint, String gUrl) {// double gLon,double gLat)

		if (rNumber != gNumber)
			return false;
		if (!rName.equals(gName))
			return false;
		if (rPostalAreaNumber != gPostalAreaNumber)
			return false;
		if (!rDeliveryPoint.equals(gDeliveryPoint))
			return false;
		if (!rUrl.equals(gUrl))
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
			PropertyNotFoundException, SQLException, ClassNotFoundException, TableNotInDatabaseException {
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

	public int getRisRecordsSkipped() {
		return risRecordsSkipped;
	}

	public int getRisRecordCount() {
		return risRecordCount;
	}

	public static  boolean isDebug() {
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

}
