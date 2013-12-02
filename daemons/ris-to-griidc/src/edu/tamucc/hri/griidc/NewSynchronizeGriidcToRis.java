package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.DuplicateRecordException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.rdbms.utils.MiscUtils;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;

public class NewSynchronizeGriidcToRis {

	private RdbmsConnection risDbConnection = null;
	private RdbmsConnection griidcDbConnection = null;
	private RdbmsConnection griidcShortTermDbConnection = null;

	public static boolean Noisy = false;
	public static String And = " AND ";
	public static String SPACE = " ";
	public static String CommaSpace = ", ";
	public static String EqualSign = " = ";

	private int exceptionCount = 0;
	private int risDataErrorCount = 0;

	public NewSynchronizeGriidcToRis() {
		// TODO Auto-generated constructor stub
	}
	
	public void initializeStartUp() throws IOException, PropertyNotFoundException, SQLException, ClassNotFoundException {
		MiscUtils.openLogFile();
		MiscUtils.openRisDataErrorFile();
		MiscUtils.openDeveloperReportFile();

		this.getRisDbConnection();
		this.getGriidcDbConnection();
	}
	public RdbmsConnection getRisDbConnection() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		if (this.risDbConnection == null)
			this.risDbConnection = MiscUtils.getRisDbConnection();
		return this.risDbConnection;
	}

	public RdbmsConnection getGriidcDbConnection()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		if (this.griidcDbConnection == null)
			this.griidcDbConnection = MiscUtils.getGriidcDbConnection();
		
		return this.griidcDbConnection;
	}

	public RdbmsConnection getGriidcShortTermDbConnection()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		if (this.griidcShortTermDbConnection == null) {
			this.griidcShortTermDbConnection = MiscUtils
					.getGriidcDbConnection();
		}
		
		return this.griidcShortTermDbConnection;
	}

	/*****
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws IOException
	 * @throws NoRecordFoundException
	 * @throws DuplicateRecordException
	 */
	private void syncGriidcInstitutionFromRisInstitution() throws ClassNotFoundException,
			PropertyNotFoundException, IOException {
		String risTableName = "Institutions";
		String griidcTableName = "Institution";

		int risInstitutionCount = 0;
		int risInstId = -1;
		String risInstName = null;
		String risInstAddr1 = null;
		String risInstAddr2 = null;
		String risInstCity = null;
		String risInstState = null;
		String risInstZip = null;
		String risInstCountry = null;
		String risInstURL = null;
		double risInstLat = 0.0;
		double risInstLong = 0.0;
		// String risInstKeywords = null;
		// String risInstVerified = null;

		// GRIIDC Institution stuff
		int griidcInstPostalAreaNumber = -1;
		String griidcInstName = null;
		int griidcInstNumber = -1;
		String query = null;

		// get all the values from the RIS Departments table

		ResultSet rset = null;
		ResultSet griidcRset = null;
		try {
			rset = this.getRisDbConnection().selectAllValuesFromTable(
					risTableName);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		try {
			
			while (rset.next()) { // continue statments branch back to here
				risInstitutionCount++;
				risInstId = rset.getInt("Institution_ID");
				risInstName = rset.getString("Institution_Name");
				risInstAddr1 = rset.getString("Institution_Addr1");
				risInstAddr2 = rset.getString("Institution_Addr2");
				risInstCity = rset.getString("Institution_City");
				risInstState = rset.getString("Institution_State");
				risInstZip = rset.getString("Institution_Zip");
				risInstCountry = rset.getString("Institution_Country");
				risInstURL = rset.getString("Institution_URL");
				risInstLat = rset.getDouble("Institution_Lat");
				risInstLong = rset.getDouble("Institution_Long");
				// risInstKeywords = rset.getString("Institution_Keywords");
				// risInstVerified = rset.getString("Institution_Verified");

				risInstName = risInstName.trim();
				risInstAddr1 = risInstAddr1.trim();
				risInstAddr2 = risInstAddr2.trim();
				risInstCity = risInstCity.trim();
				risInstState = risInstState.trim();
				risInstZip = risInstZip.trim();
				risInstCountry = risInstCountry.trim();
				risInstURL = risInstURL.trim();

				// ??????? the following should come out for production
				// change code to use the 3 character abbreviation when new
				// schema is available
				String correctedCountry = MiscUtils
						.getRisCountryCorrection(risInstCountry);
				int countryNumber = -1;
				try {
					countryNumber = this
							.findCountryNumberFromName(correctedCountry);
				} catch (DuplicateRecordException e2) {
					this.writeToErrorLog(e2.getMessage());
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e2) {
					this.writeToErrorLog(e2.getMessage());
					continue; // branch back to while (rset.next())
				}

				/****
				 * find and update the GRIIDC Institution table with these
				 * values
				 */
				griidcInstPostalAreaNumber = -1;

				try {
					griidcInstPostalAreaNumber = this
							.findGriidcPostalAreaNumber(countryNumber,
									risInstState, risInstCity, risInstZip);
				} catch (DuplicateRecordException e) {
					this.writeToErrorLog(e.getMessage());
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					this.writeToErrorLog(e.getMessage());
					continue; // branch back to while (rset.next())
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				} catch (MissingArgumentsException e) {
					this.writeToRisDataErrorLog("Error In RIS Institutions record: "
							+ risInstId + "\n\t" + e.getMessage());
					continue; // branch back to while (rset.next())
				}
				/*
				 * if the data in RIS is unusable - skip this record - go to the
				 * next record *
				 */
				/**                                                            **/
				String deliveryPoint = this.makeDeliveryPoint(risInstAddr1,
						risInstAddr2);

				try {
					query = "SELECT * FROM "
							// + this.getWrappedGriidcShemaName() + "."
							+ doubleQuote(griidcTableName) + " WHERE "
							+ doubleQuote("Institution_Name") + EqualSign
							+ singleQuote(risInstName);
					/*********************************************
					 * + And + doubleQuote("PostalArea_Number") + EqualSign +
					 * griidcInstPostalAreaNumber;
					 * 
					 * + And + doubleQuote("Institution_DeliveryPoint") +
					 * EqualSign + singleQuote(deliveryPoint);
					 *********************************************/
					griidcRset = this.getGriidcDbConnection()
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
						// this.compareDeliveryPoint(griidcInstName,
						// griidcRset.getString("Institution_DeliveryPoint"),deliveryPoint);
					}
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
				if (count == 0) {
					// We don't add Institution Records

					String msg = "\nMissing GRIIDC Institution table record "
							+ "Institution_Name: " + risInstName
							+ ", PostalArea_Number: "
							+ griidcInstPostalAreaNumber
							+ ", Institution_DeliveryPoint: " + deliveryPoint;
					System.out.println(msg);
					this.writeToErrorLog(msg);

				} else if (count == 1) {
					// cache the GRIIDC Institution number with the RIS
					// institution number as key
					Integer oldValue = this.institutionIdCache.cacheValue(
							risInstId, griidcInstNumber);
					if (oldValue != null)
						System.err.println("Previous cache value for key "
								+ risInstId + " was " + oldValue);

					//
					// We don't modify Institution records !!!
					//
					/***
					 * String msg = "Modify GRIIDC Institution table matching "
					 * + "\n\tgriidcInstNumber: " + griidcInstNumber +
					 * ", Institution_Name: " + risInstName +
					 * ", PostalArea_Number: " + griidcInstPostalAreaNumber +
					 * ", Institution_DeliveryPoint: " + deliveryPoint;
					 * System.out.println(msg);
					 * 
					 * String modifyQuery = null; try { modifyQuery =
					 * this.getModifyInstitutionQuery( griidcInstNumber,
					 * risInstName, griidcInstPostalAreaNumber, deliveryPoint,
					 * risInstURL, risInstLong, risInstLat);
					 * this.griidcDbConnection
					 * .executeQueryBoolean(modifyQuery); } catch (SQLException
					 * e) { System.err .println(
					 * "SQL Error: Modify Institution in GRIIDC - Query: " +
					 * modifyQuery); e.printStackTrace(); }
					 ****************/
				} else if (count > 1) { // duplicates
					String msg = "There are "
							+ count
							+ " records in the  GRIIDC Institution table matching "
							+ "Institution_Name: " + risInstName
							+ ", PostalArea_Number: "
							+ griidcInstPostalAreaNumber
							+ ", Institution_DeliveryPoint: " + deliveryPoint;

					this.writeToErrorLog(msg);
				}
			}
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		if (this.getExceptionCount() == 0) {
			this.writeToErrorLog("No errors found ");
			this.resetExceptionCount();
		}

		System.out.println("Read " + risInstitutionCount
				+ " RIS Institutions records");
		System.out.println("RIS records with data errors: "
				+ this.getRisDataErrorCount());
	}
}
