package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Iterator;
import java.util.SortedSet;

import edu.tamucc.hri.griidc.exception.DuplicateRecordException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.HeuristicMatching;
import edu.tamucc.hri.griidc.support.InstitutionDepartmentRep;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.griidc.support.RisInstDeptPeopleErrorCollection;
import edu.tamucc.hri.rdbms.utils.IntStringDbCache;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

public class DepartmentSynchronizer {

	private static final String RisTableName = "Departments";
	private static final String GriidcTableName = "Department";

	private RdbmsConnection risDbConnection = null;
	private RdbmsConnection griidcDbConnection = null;

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
	private boolean initialized = false;
	private HeuristicMatching heuristics = new HeuristicMatching();
	private IntStringDbCache griidcInstitutionNumberCache = null;
	private RisInstDeptPeopleErrorCollection risInstitutionWithErrors = null;

	public DepartmentSynchronizer() {

	}

	public boolean isInitialized() {
		return initialized;
	}

	public void initializeStartUp() throws IOException,
			PropertyNotFoundException, SQLException, ClassNotFoundException,
			TableNotInDatabaseException {
		if (!isInitialized()) {
			MiscUtils.openPrimaryLogFile();
			MiscUtils.openRisErrorLogFile();
			MiscUtils.openDeveloperReportFile();
			this.risDbConnection = RdbmsUtils.getRisDbConnectionInstance();
			this.griidcDbConnection = RdbmsUtils
					.getGriidcDbConnectionInstance();
			// a set of all the GRIIDC institution numbers
			this.griidcInstitutionNumberCache = new IntStringDbCache(
					this.griidcDbConnection, "Institution",
					"Institution_Number", "Institution_Name");
			this.griidcInstitutionNumberCache.buildCacheFromDb();
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
	 * @throws DuplicateRecordException
	 */
	public RisInstDeptPeopleErrorCollection syncGriidcDepartmentFromRisDepartment(
			RisInstDeptPeopleErrorCollection risInstWithErr)
			throws ClassNotFoundException, PropertyNotFoundException,
			IOException, SQLException, TableNotInDatabaseException {
		if (isDebug())
			System.out.println(MiscUtils.BreakLine);
		this.risInstitutionWithErrors = risInstWithErr;

		this.initializeStartUp();

		String tempDeliveryPoint = null; // created from RIS info
		int tempPostalAreaNumber = -1; // created from RIS info

		// get all records from the RIS Department table
		try {
			rset = this.risDbConnection.selectAllValuesFromTable(RisTableName);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		try {

			while (rset.next()) { // continue statements branch back to here
				risRecordCount++;
				this.risDeptId = rset.getInt("Department_ID");
				this.risDeptInstId = rset.getInt("Institution_ID");
				this.risDeptName = rset.getString("Department_Name").trim();
				this.risDeptAddr1 = rset.getString("Department_Addr1").trim();
				this.risDeptAddr2 = rset.getString("Department_Addr2").trim();
				this.risDeptCity = rset.getString("Department_City").trim();
				this.risDeptState = rset.getString("Department_State").trim();
				this.risDeptZip = rset.getString("Department_Zip").trim();
				this.risDeptCountry = rset.getString("Department_Country")
						.trim();
				this.risDeptURL = rset.getString("Department_URL").trim();
				this.risDeptLat = rset.getDouble("Department_Lat");
				this.risDeptLong = rset.getDouble("Department_Long");

				String msg = "Read RIS: " + "Dept: " + risDeptId + ", "
						+ "DeptInstId: " + risDeptInstId + ", " + "Name : "
						+ risDeptName + ", " + "Addr 1: " + risDeptAddr1 + ", "
						+ "Addr 2: " + risDeptAddr2 + ", " + "City: "
						+ risDeptCity + ", " + "State: " + risDeptState + ", "
						+ "Zip: " + risDeptZip + ", " + "Country: "
						+ risDeptCountry + ", " + "URL: " + risDeptURL + ", "
						+ "Lat: " + risDeptLat + ", " + "lon: " + risDeptLong;
				if (isDebug())
					System.out.println(msg + "\n");
				int countryNumber = -1;
				if (MiscUtils.isStringEmpty(risDeptCountry)) {
					MiscUtils
							.writeToRisErrorLogFile("Error In RIS Departments - record id: "
									+ risDeptId
									+ " - Department_Country is "
									+ ((risDeptCountry == null) ? "null"
											: " length zero"));
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // skip to while (rset.next())
				}
				try {
					this.griidcInstitutionNumberCache
							.getValue(this.risDeptInstId);
				} catch (NoRecordFoundException e1) {

					msg = "Error in RIS Departments - record id: "
							+ this.risDeptId
							+ " references an Institution Number that does not exist: "
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
					msg = msg + "\n" + e1.getMessage();
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToRisErrorLogFile(msg);
					if (isDebug())
						System.err.println("__ Skip this one: " + msg);
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue;
				}
				try {
					countryNumber = RdbmsUtils
							.getCountryNumberFromName(risDeptCountry);
				} catch (DuplicateRecordException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					if (isDebug())
						System.err.println("AA Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					msg = "Error in RIS Departments - record id: "
							+ this.risDeptId + ": " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					if (isDebug())
						System.err.println("BB Skip this one: "
								+ e.getMessage());
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				}

				/****
				 * find and update the GRIIDC Department table with these values
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
				} catch (DuplicateRecordException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					if (isDebug())
						System.err.println("CC Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					msg = "Error in RIS Departments - record id: "
							+ this.risDeptId + ": " + e.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					if (isDebug())
						System.err.println("DD Skip this one: "
								+ e.getMessage());
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
					if (isDebug())
						System.err.println("EE Skip this one: "
								+ e.getMessage());

					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (MissingArgumentsException e) {
					MiscUtils
							.writeToRisErrorLogFile("Error In RIS Departments - record: "
									+ risDeptId + " - " + e.getMessage());
					if (isDebug())
						System.err.println("FF Skip this one: "
								+ e.getMessage());
					this.risRecordErrors++;
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				}
				/*
				 * if the data in RIS is unusable - skip this record - go to the
				 * next record *
				 */
				/**                                                            **/
				tempDeliveryPoint = MiscUtils.makeDeliveryPoint(
						this.risDeptAddr1, this.risDeptAddr2);
				String query = null;
				try {
					query = "SELECT * FROM "
							// + this.getWrappedGriidcShemaName() + "."
							+ RdbmsConnection
									.wrapInDoubleQuotes(GriidcTableName)
							+ " WHERE "
							+ RdbmsConnection
									.wrapInDoubleQuotes("Department_Number")
							+ RdbmsUtils.EqualSign
							+ risDeptId
							+ RdbmsUtils.And
							+ RdbmsConnection
									.wrapInDoubleQuotes("Institution_Number")
							+ RdbmsUtils.EqualSign + risDeptInstId;

					griidcRset = this.griidcDbConnection
							.executeQueryResultSet(query);

				} catch (SQLException e1) {
					System.err
							.println("SQL Error: Find Department in GRIIDC - Query: "
									+ query);
					e1.printStackTrace();
				}

				int count = 0;
				try {
					while (griidcRset.next()) {
						count++;
						this.griidcDeptNumber = griidcRset
								.getInt("Department_Number");
						this.griidcDeptInstNumber = griidcRset
								.getInt("Institution_Number");
						this.griidcDeptPostalAreaNumber = griidcRset
								.getInt("PostalArea_Number");
						this.griidcDeptDeliveryPoint = griidcRset
								.getString("Department_DeliveryPoint");
						this.griidcDeptName = griidcRset
								.getString("Department_Name");
						this.griidcDeptUrl = griidcRset
								.getString("Department_URL");
					}
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}

				if (count == 0) {
					// Add the Department
					this.griidcRecordsAdded++;
					if (DepartmentSynchronizer.isDebug()) {
						msg = "Add GRIIDC Department table record "
								+ "Department_Name: " + risDeptName
								+ ", PostalArea_Number: "
								+ griidcDeptPostalAreaNumber
								+ ", Department_DeliveryPoint: "
								+ tempDeliveryPoint;
						System.out.println(msg);
					}
					String addQuery = null;
					try {
						addQuery = this.formatAddDepartmentQuery(
								this.risDeptId, this.risDeptInstId,
								tempPostalAreaNumber, tempDeliveryPoint,
								this.risDeptName, risDeptURL, risDeptLong,
								risDeptLat);
						if (DepartmentSynchronizer.isDebug())
							System.out.println("Query: " + addQuery);
						this.griidcDbConnection.executeQueryBoolean(addQuery);
						msg = "Added GRIIDC Department: "
								+ griidcDepartmentToString(this.risDeptId,
										this.risDeptInstId,
										tempPostalAreaNumber,
										tempDeliveryPoint, this.risDeptName,
										risDeptURL, risDeptLong, risDeptLat);
						MiscUtils.writeToPrimaryLogFile(msg);
						if (DepartmentSynchronizer.isDebug())
							System.out.println(msg);
					} catch (SQLException e) {
						msg = "SQL Error: Adding Department in GRIIDC - \nQuery: "
								+ addQuery + "\n" + e.getMessage();
						if (DepartmentSynchronizer.isDebug()) System.err.println(msg);
						MiscUtils.writeToPrimaryLogFile(msg);
					}

				} else if (count == 1) {

					// Modify Department record
					if (isRisDeptEqualToGriidcDept(this.risDeptId,
							this.risDeptInstId, tempPostalAreaNumber,
							tempDeliveryPoint, this.risDeptName,
							this.risDeptURL, this.griidcDeptNumber,
							this.griidcDeptInstNumber,
							this.griidcDeptPostalAreaNumber,
							this.griidcDeptDeliveryPoint, this.griidcDeptName,
							this.griidcDeptUrl)) {
						continue; // branch back to while (rset.next())
					}
					this.griidcRecordsModified++;
					if (DepartmentSynchronizer.isDebug()) {
						msg = "Modify GRIIDC Department table matching "
								+ "griidcDeptNumber: " + risDeptId
								+ ", Department_Name: " + risDeptName
								+ ", PostalArea_Number: "
								+ griidcDeptPostalAreaNumber
								+ ", Department_DeliveryPoint: "
								+ tempDeliveryPoint;
						System.out.println(msg);
					}

					String modifyQuery = null;
					try {
						modifyQuery = this.formatModifyDepartmentQuery(
								this.risDeptId, this.risDeptInstId,
								tempPostalAreaNumber, tempDeliveryPoint,
								this.risDeptName, risDeptURL, risDeptLong,
								risDeptLat);

						System.out.println("Query: " + modifyQuery);
						this.griidcDbConnection
								.executeQueryBoolean(modifyQuery);
						msg = "Modified GRIIDC Department: "
								+ griidcDepartmentToString(this.risDeptId,
										this.risDeptInstId,
										tempPostalAreaNumber,
										tempDeliveryPoint, this.risDeptName,
										risDeptURL, risDeptLong, risDeptLat);
						MiscUtils.writeToPrimaryLogFile(msg);
						if (DepartmentSynchronizer.isDebug())
							System.out.println(msg);
					} catch (SQLException e) {
						System.err
								.println("SQL Error: Modify Department in GRIIDC - Query: "
										+ modifyQuery);
						e.printStackTrace();
					}

				} else if (count > 1) { // duplicates
					this.griidcRecordDuplicates++;

					msg = "There are "
							+ count
							+ " records in the  GRIIDC Department table matching "
							+ "Department_Name: " + risDeptName
							+ ", PostalArea_Number: "
							+ griidcDeptPostalAreaNumber
							+ ", Department_DeliveryPoint: "
							+ tempDeliveryPoint;
					if (DepartmentSynchronizer.isDebug())
						System.out.println(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
				}
			} // end of main while loop
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return this.risInstitutionWithErrors;
		// end of Department
	}

	private boolean isInstitutionOnRisErrorsList(int risDeptInstId) {
		return this.risInstitutionWithErrors.containsInstitution(risDeptInstId);
	}

	private String formatAddDepartmentQuery(int risDeptNumber,
			int risDeptInstNumber, int griidcPostalAreaNumber,
			String deliveryPoint, String risDeptName, String risDeptURL,
			double risDeptLon, double risDeptLat) throws SQLException,
			ClassNotFoundException {
		StringBuffer sb = new StringBuffer("INSERT INTO ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Department")
				+ RdbmsUtils.SPACE + "(");
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Department_Number"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Institution_Number"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("PostalArea_Number"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection
						.wrapInDoubleQuotes("Department_DeliveryPoint"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Department_Name"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Department_URL"));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection
						.wrapInDoubleQuotes("Department_GeoCoordinate"));
		sb.append(") VALUES (");
		// the values are here
		sb.append(risDeptNumber);
		sb.append(RdbmsUtils.CommaSpace + risDeptInstNumber);
		sb.append(RdbmsUtils.CommaSpace + griidcPostalAreaNumber);
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(deliveryPoint));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(risDeptName));
		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(risDeptURL));
		sb.append(RdbmsUtils.CommaSpace
				+ makeSqlGeometryPointString(risDeptLon, risDeptLat));
		sb.append(" )");
		return sb.toString();
	}

	private String makeSqlGeometryPointString(double lon, double lat) {
		return " " + "ST_SetSRID(ST_MakePoint(" + lon + "," + lat + "), 4326)";
	}

	private String formatModifyDepartmentQuery(int risDeptNumber,
			int risDeptInstNumber, int griidcPostalAreaNumber,
			String deliveryPoint, String risDeptName, String risDeptURL,
			double risDeptLon, double risDeptLat) throws SQLException,
			ClassNotFoundException {
		StringBuffer sb = new StringBuffer("UPDATE  ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Department")
				+ RdbmsUtils.SPACE + " SET ");

		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("PostalArea_Number")
				+ RdbmsUtils.EqualSign + griidcPostalAreaNumber);

		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection
						.wrapInDoubleQuotes("Department_DeliveryPoint")
				+ RdbmsUtils.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(deliveryPoint));

		sb.append(RdbmsConnection.wrapInDoubleQuotes("Department_Name")
				+ RdbmsUtils.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(risDeptName));

		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Department_URL")
				+ RdbmsUtils.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(risDeptURL));

		sb.append(RdbmsUtils.CommaSpace
				+ RdbmsConnection
						.wrapInDoubleQuotes("Department_GeoCoordinate")
				+ RdbmsUtils.EqualSign
				+ makeSqlGeometryPointString(risDeptLon, risDeptLat));

		sb.append(" WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes("Department_Number")
				+ RdbmsUtils.EqualSign + risDeptNumber);
		return sb.toString();
	}

	private String griidcDepartmentToString(int risDeptNumber,
			int risDeptInstNumber, int griidcPostalAreaNumber,
			String deliveryPoint, String risDeptName, String risDeptURL,
			double risDeptLon, double risDeptLat) {
		return "Dept Num: " + risDeptNumber + ", " + "Dept Inst: "
				+ risDeptInstNumber + ", " + "Dept postal area: "
				+ griidcPostalAreaNumber + ", " + "Dept delivery point: "
				+ deliveryPoint + ", " + "Dept name: " + risDeptName + ", "
				+ "Dept URL: " + risDeptURL + ", " + "Dept Lon " + risDeptLon
				+ ", " + "Dept Lat " + risDeptLat;
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
	private boolean isRisDeptEqualToGriidcDept(int rNumber, int rInstNumber,
			int rPostalAreaNumber, String rDeliveryPoint, String rName,
			String rUrl, int gNumber, int gInstNumber, int gPostalAreaNumber,
			String gDeliveryPoint, String gName, String gUrl) {// double
																// gLon,double
																// gLat)

		if (rNumber == gNumber && rInstNumber == gInstNumber
				&& rPostalAreaNumber == gPostalAreaNumber
				&& rName.equals(gName) && rDeliveryPoint.equals(gDeliveryPoint)
				&& rUrl.equals(gUrl))
			return true;
		// if(rLon != gLon) return false;
		// if(rLat != gLat) return false;
		return false;
	}

	public String getPrimaryLogFileName() throws FileNotFoundException,
			PropertyNotFoundException {
		return MiscUtils.getPrimaryLogFileName();
	}

	public String getRisErrorLogFileName() throws FileNotFoundException,
			PropertyNotFoundException {
		return MiscUtils.getRisErrorLogFileName();
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
