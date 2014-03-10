package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import javax.mail.internet.AddressException;

import edu.tamucc.hri.griidc.exception.DuplicateRecordException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.exception.TelephoneNumberException;
import edu.tamucc.hri.griidc.support.HeuristicMatching;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.griidc.support.RisInstDeptPeopleErrorCollection;
import edu.tamucc.hri.griidc.support.RisToGriidcConfiguration;
import edu.tamucc.hri.rdbms.utils.IntStringDbCache;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsConstants;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

/**
 * This class reads the RIS database for People records and adds/updates GRIIDC
 * Person records.
 * 
 * This validates the address and uses it if possible.
 * 
 * There is no explicit Country field in RIS.People so the Country found in the
 * referenced "Departments" record is used instead.
 * 
 * In the case of an invalid address the address of the "Departments" record is
 * used for the address of the "Person" record.
 * 
 * In the case of an invalid address in "People" and "Departments" the address
 * of the referenced "Instutions" records is used for the address of the
 * "Person" record
 * 
 * @author jvh
 * 
 */
public class PersonSynchronizer {
	// jvh jvh jvh work on this class
	// How to compare for updates
	// consider telephone, postal are,  delivery point

	private static final String RisTableName = "People";
	private static final String GriidcTableName = "Person";

	private RdbmsConnection risDbConnection = null;
	private RdbmsConnection griidcDbConnection = null;

	private int risRecordCount = 0;
	private int risRecordsSkipped = 0;
	private int risRecordErrors = 0;
	private int griidcRecordsAdded = 0;
	private int griidcRecordsModified = 0;
	private int griidcRecordDuplicates = 0;

	private int risPeople_Id = -1;
	private int risPeople_InstitutionId = -1;
	private int risPeople_DepartmentId = -1;
	private String risPeople_Title = null;
	private String risPeople_LastName = null;
	private String risPeople_FirstName = null;
	private String risPeople_MiddleName = null;
	private String risPeople_Suffix = null;
	private String risPeople_AdrStreet1 = null;
	private String risPeople_AdrStreet2 = null;
	private String risPeople_AdrCity = null;
	private String risPeople_AdrState = null;
	private String risPeople_AdrZip = null;
	private String risPeople_Email = null;
	private String risPeople_PhoneNum = null;
	private String risPeople_GulfBase = null;
	private String risPeople_Comment = null;
	/*****************************************
	 * Department_ID int Institution_ID int Department_Name varchar
	 * Department_Addr1 varchar Department_Addr2 varchar Department_City varchar
	 * Department_State varchar Department_Zip varchar Department_Country
	 * varchar Department_URL text Department_Lat decimal Department_Long
	 * decimal
	 ******************************************/

	// GRIIDC Department stuff
	private int griidcPerson_Number = -1;
	private int griidcPersonPostalArea_Number = -1;
	private String griidcPerson_DeliveryPoint = null;
	private String griidcPerson_FirstName = null;
	private String griidcPerson_LastName = null;
	private String griidcPerson_MiddleName = null;
	private String griidcPerson_HonorificTitle = null;
	private String griidcPerson_NameSuffix = null;

	/***************************************
	 * Department_Number integer Institution_Number integer PostalArea_Number
	 * integer Department_DeliveryPoint text Department_Name text Department_URL
	 * text Department_GeoCoordinate USER-DEFINED
	 ********************************************/

	// get all the values from the RIS Peoples table

	private ResultSet rset = null;
	private ResultSet griidcRset = null;

	private static boolean debug = false;
	private boolean initialized = false;
	private HeuristicMatching heuristics = new HeuristicMatching();
	private IntStringDbCache griidcInstitutionNumberCache = null;
	private RisInstDeptPeopleErrorCollection risErrorCollection = null;

	private String tempDeliveryPoint = null; // created from RIS info
	private int tempPostalAreaNumber = -1; // created from RIS info

	private EmailSynchronizer emailUpdater = new EmailSynchronizer();

	public PersonSynchronizer() {
		// TODO Auto-generated constructor stub
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
			this.emailUpdater.initializeStartUp();
			this.initialized = true;
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

	public RisInstDeptPeopleErrorCollection syncGriidcPersonFromRisPeople(
			RisInstDeptPeopleErrorCollection risErrorSet)
			throws ClassNotFoundException, PropertyNotFoundException,
			IOException, SQLException, TableNotInDatabaseException {
		int countryNumber = -1;
		if (isDebug())
			System.out.println(MiscUtils.BreakLine);
		this.risErrorCollection = risErrorSet;

		this.initializeStartUp();

		String msg = null;
		// get all records from the RIS People table
		try {
			rset = this.risDbConnection.selectAllValuesFromTable(RisTableName);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		try {

			while (rset.next()) { // continue statements branch back to here
				risRecordCount++;
				this.risPeople_Id = rset.getInt("People_ID");
				this.risPeople_InstitutionId = rset
						.getInt("People_Institution");
				this.risPeople_DepartmentId = rset.getInt("People_Department");
				this.risPeople_Title = rset.getString("People_Title");
				this.risPeople_LastName = rset.getString("People_LastName");
				this.risPeople_FirstName = rset.getString("People_FirstName");
				this.risPeople_MiddleName = rset.getString("People_MiddleName");
				this.risPeople_Suffix = rset.getString("People_Suffix");
				this.risPeople_AdrStreet1 = rset.getString("People_AdrStreet1");
				this.risPeople_AdrStreet2 = rset.getString("People_AdrStreet2");
				this.risPeople_AdrCity = rset.getString("People_AdrCity");
				this.risPeople_AdrState = rset.getString("People_AdrState");
				this.risPeople_AdrZip = rset.getString("People_AdrZip");
				this.risPeople_Email = rset.getString("People_Email");
				this.risPeople_PhoneNum = rset.getString("People_PhoneNum");
				this.risPeople_GulfBase = rset.getString("People_GulfBase");
				this.risPeople_Comment = rset.getString("People_Comment");

				if (isDebug())
					System.out.println("\n" + this.getFormatedRisPeople());

				/****
				 * find and update the GRIIDC Person table with these values
				 */
				this.tempPostalAreaNumber = -1;
				/**
				 * we must have a valid department and institution in the
				 * database with which to associate this Person record
				 */

				try {
					RdbmsUtils.doesGriidcDepartmentExist(
							risPeople_InstitutionId, risPeople_DepartmentId);

				} catch (NoRecordFoundException e2) {
					// no Department/Institution found in the database
					// add the people record to the error collection
					msg = "Error in RIS People - record id: "
							+ risPeople_Id
							+ ", institution: "
							+ risPeople_InstitutionId
							+ ", department: "
							+ risPeople_DepartmentId
							+ "\nThe referenced Institution and/or Department was not found in the database.\n"
							+ e2.getMessage();
					if (isDebug())
						System.err.println("AA Skip this one: " + msg);

					this.risErrorCollection.addPerson(
							this.risPeople_InstitutionId,
							this.risPeople_DepartmentId, this.risPeople_Id);
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToRisErrorLogFile(msg);
					this.risRecordsSkipped++;
					this.risRecordErrors++;
					continue; // branch back to while (rset.next())

				} catch (DuplicateRecordException e2) {

					msg = "Error in RIS People - record id: " + risPeople_Id
							+ ", institution: " + risPeople_InstitutionId
							+ ", department: " + risPeople_DepartmentId + ".\n"
							+ e2.getMessage();
					if (isDebug())
						System.err.println("BB Skip this one: " + msg);
					e2.printStackTrace();
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToRisErrorLogFile(msg);
					this.risRecordsSkipped++;
					this.risRecordErrors++;
					continue; // branch back to while (rset.next())
				}

				try {
					// try and get a postal area number from the country, state,
					// city, zip
					// but don't reject if not possible
					// The Person record can be created without it

					countryNumber = RdbmsUtils
							.getGriidcDepartmentCountryNumber(this.risPeople_DepartmentId);
					this.tempPostalAreaNumber = RdbmsUtils
							.getGriidcDepartmentPostalNumber(countryNumber,
									this.risPeople_AdrState,
									this.risPeople_AdrCity,
									this.risPeople_AdrZip);

				} catch (DuplicateRecordException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					if (isDebug())
						System.err.println("CC Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
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
					// do nothing - Person is not required to have Postal Area
					// Number
				} catch (NoRecordFoundException e) {
					// failed to find one of this.tempPostalAreaNumber or
					// country code
					// fall through and try with just the department number
				}
				try {
					// try and get a postal area number from the department id
					// but don't reject if not possible
					// The Person record can be created without it
					this.tempPostalAreaNumber = -1;
					this.tempPostalAreaNumber = RdbmsUtils
							.getGriidcDepartmentPostalNumber(this.risPeople_DepartmentId);
				} catch (DuplicateRecordException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					if (isDebug())
						System.err.println("CC Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
					if (isDebug())
						System.err.println("EE Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					// did not find tempPostalAreaNumber
				}

				/*
				 * if the data in RIS is unusable - skip this record - go to the
				 * next record *
				 */

				this.tempDeliveryPoint = MiscUtils.makeDeliveryPoint(
						this.risPeople_AdrStreet1, this.risPeople_AdrStreet2);
				String query = null;
				try {
					query = "SELECT * FROM "
							// + this.getWrappedGriidcShemaName() + "."
							+ RdbmsConnection
									.wrapInDoubleQuotes(GriidcTableName)
							+ " WHERE "
							+ RdbmsConnection
									.wrapInDoubleQuotes("Person_Number")
							+ RdbmsConstants.EqualSign + this.risPeople_Id;
					/***
					 * + RdbmsUtils.And + RdbmsConnection
					 * .wrapInDoubleQuotes("Person_FirstName") +
					 * RdbmsUtils.EqualSign + this.risPeople_FirstName +
					 * RdbmsUtils.And + RdbmsConnection
					 * .wrapInDoubleQuotes("Person_LastName") +
					 * RdbmsUtils.EqualSign + this.risPeople_LastName +
					 * RdbmsUtils.And + RdbmsConnection
					 * .wrapInDoubleQuotes("Person_MiddleName") +
					 * RdbmsUtils.EqualSign + this.risPeople_MiddleName;
					 ***/

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
						this.griidcPersonPostalArea_Number = griidcRset
								.getInt("PostalArea_Number");
						this.griidcPerson_DeliveryPoint = griidcRset
								.getString("Person_DeliveryPoint");
						this.griidcPerson_FirstName = griidcRset
								.getString("Person_FirstName");
						this.griidcPerson_LastName = griidcRset
								.getString("Person_LastName");
						this.griidcPerson_MiddleName = griidcRset
								.getString("Person_MiddleName");
						this.griidcPerson_HonorificTitle = griidcRset
								.getString("Person_HonorificTitle");
						this.griidcPerson_NameSuffix = griidcRset
								.getString("Person_NameSuffix");
					}
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}

				if (count == 0) {
					// Add the Person
					/*************************************
					 * if (PersonSynchronizer.isDebug()) {
					 * 
					 * System.out.println(msg); } // end if(debug)
					 *******************************/
					String addQuery = null;
					try {
						addQuery = this.formatAddPersonQuery(this.risPeople_Id,
								this.tempPostalAreaNumber,
								this.tempDeliveryPoint,
								this.risPeople_LastName,
								this.risPeople_FirstName,
								this.risPeople_MiddleName,
								this.risPeople_Title, this.risPeople_Suffix);

						if (PersonSynchronizer.isDebug())
							System.out.println("Query: " + addQuery);
						this.griidcDbConnection.executeQueryBoolean(addQuery);
						this.updateEmailTable(this.risPeople_Id,
								this.risPeople_Email, true);
						this.griidcRecordsAdded++;
						try {
							this.updateTelephoneTable(this.risPeople_Id,countryNumber,
									this.risPeople_PhoneNum);
						} catch (TelephoneNumberException e1) {
							String pInfo = "" + this.risPeople_Id + " "
									+ this.risPeople_LastName + ", "
									+ this.risPeople_FirstName + " "
									+ this.risPeople_MiddleName;
							msg = "TelephoneNumberException when adding telephone number for Person: "
									+ pInfo + e1.getMessage();
							if (PersonSynchronizer.isDebug())
								System.out.println(msg);
							MiscUtils.writeToPrimaryLogFile(msg);
							MiscUtils.writeToRisErrorLogFile(msg);
						}

						//
						// if this fails it could be because the department or
						// institution failed
						// load previously in InstitutionSynchronizer and
						// DepartmentSynchronizer
						//
						msg = "Added Person: "
								+ griidcPersonToString(this.risPeople_Id,
										this.risPeople_InstitutionId,
										this.risPeople_DepartmentId,
										this.tempPostalAreaNumber,
										this.tempDeliveryPoint,
										this.risPeople_LastName,
										this.risPeople_FirstName,
										this.risPeople_MiddleName);
						MiscUtils.writeToPrimaryLogFile(msg);
						if (PersonSynchronizer.isDebug())
							System.out.println(msg);
					} catch (SQLException e) {
						msg = "SQL Error: Add Person in GRIIDC - Query: "
								+ addQuery;
						msg = msg + "\n" + e.getMessage();
						System.err.println(msg);
						MiscUtils.writeToPrimaryLogFile(msg);

						// check here to see if the department and institution
						// or are
						// on the error set
						this.risErrorCollection.addPerson(
								this.risPeople_InstitutionId,
								this.risPeople_DepartmentId, this.risPeople_Id);

					}

				} else if (count == 1) {

					// Modify Person record
					if (isCurrentRecordEqual()) {
						continue; // branch back to while (rset.next())
					}
					this.griidcRecordsModified++;
					if (PersonSynchronizer.isDebug()) {
						msg = "Modify GRIIDC Person table matching "
								+ ", Person_Name: " + risPeople_LastName + ", "
								+ risPeople_FirstName + " "
								+ risPeople_MiddleName
								+ ", PostalArea_Number: "
								+ this.tempPostalAreaNumber
								+ ", Person_DeliveryPoint: "
								+ this.tempDeliveryPoint;
						System.out.println(msg);
					}

					String modifyQuery = null;
					try {
						modifyQuery = this.formatModifyPersonQuery(
								this.risPeople_Id, this.tempPostalAreaNumber,
								this.tempDeliveryPoint,
								this.risPeople_LastName,
								this.risPeople_FirstName,
								this.risPeople_MiddleName,
								this.risPeople_Title, this.risPeople_Suffix);

						this.griidcDbConnection
								.executeQueryBoolean(modifyQuery);
						this.updateEmailTable(this.risPeople_Id,
								this.risPeople_Email, true);
						try {
							this.updateTelephoneTable(this.risPeople_Id,countryNumber,
									this.risPeople_PhoneNum);
						} catch (TelephoneNumberException e1) {
							String pInfo = "" + this.risPeople_Id + " "
									+ this.risPeople_LastName + ", "
									+ this.risPeople_FirstName + " "
									+ this.risPeople_MiddleName;
							msg = "When modifying telephone number for Person: "
									+ pInfo + "\n" + e1.getMessage();
							MiscUtils.writeToPrimaryLogFile(msg);
							MiscUtils.writeToRisErrorLogFile(msg);
						}
						msg = "Modified GRIIDC Person: "
								+ griidcPersonToString(this.risPeople_Id,
										this.risPeople_InstitutionId,
										this.risPeople_DepartmentId,
										this.tempPostalAreaNumber,
										this.tempDeliveryPoint,
										this.risPeople_LastName,
										this.risPeople_FirstName,
										this.risPeople_MiddleName);
						MiscUtils.writeToPrimaryLogFile(msg);
						if (PersonSynchronizer.isDebug())
							System.out.println(msg);
					} catch (SQLException e) {
						System.err
								.println("SQL Error: Modify Person in GRIIDC - Query: "
										+ modifyQuery);
						e.printStackTrace();
					}

				} else if (count > 1) { // duplicates
					this.griidcRecordDuplicates++;

					msg = "There are " + count
							+ " records in the  GRIIDC Person table matching "
							+ ", Person_Name: " + risPeople_LastName + ", "
							+ risPeople_FirstName + " " + risPeople_MiddleName
							+ ", PostalArea_Number: "
							+ this.tempPostalAreaNumber
							+ ", Person_DeliveryPoint: "
							+ this.tempDeliveryPoint;
					if (PersonSynchronizer.isDebug())
						System.out.println(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
				}
			} // end of main while loop
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return this.risErrorCollection;
		// end of Person
	}

	/**
	 * modify or add an email address for the person Id provided. validate the
	 * email and handle the exception
	 * 
	 * @param pepId
	 * @param emailAddr
	 * @param primary
	 * @return
	 * @throws ClassNotFoundException
	 * @throws IOException
	 * @throws PropertyNotFoundException
	 */
	private boolean updateEmailTable(int pepId, String emailAddr,
			boolean primary) throws ClassNotFoundException, IOException,
			PropertyNotFoundException {
		String msg = null;
		String emailInfo = EmailSynchronizer.formatData(pepId, emailAddr,
				primary);

		try {
			this.emailUpdater.update(pepId, emailAddr, primary);
			return true;
		} catch (DuplicateRecordException e) {
			msg = e.getMessage();
			MiscUtils.writeToRisErrorLogFile(msg);
			MiscUtils.writeToPrimaryLogFile(msg);
		} catch (SQLException e) {
			msg = "SQL Error: EmailInfo: " + e.getMessage();
		} catch (AddressException e) {
			msg = "Email Address Exception for : " + emailInfo + " "
					+ e.getMessage();
			MiscUtils.writeToRisErrorLogFile(msg);
			MiscUtils.writeToPrimaryLogFile(msg);
		}
		return false;
	}

	/**
	 * @param countryNumber
	 * @param telephoneNumber
	 * @return
	 * @throws TelephoneNumberException
	 * @throws TableNotInDatabaseException
	 * @throws SQLException 
	 * @throws ClassNotFoundException 
	 * @throws PropertyNotFoundException 
	 * @throws FileNotFoundException 
	 */
	private boolean updateTelephoneTable(int personNumber, int countryNumber,
			String telephoneNumber) 
					throws TableNotInDatabaseException, SQLException, TelephoneNumberException,
					FileNotFoundException, PropertyNotFoundException, ClassNotFoundException {
		if(PersonSynchronizer.isDebug()) System.out.println("PersonSynchronizer.updateTelephoneTable(" + personNumber + ", " + countryNumber +
			", " + telephoneNumber + ")");
		
       if(PersonSynchronizer.isDebug()) System.out.println("PersonSynchronizer.updateTelephoneTable() actions: \n\t1. createTelephoneStruct" + 
		                         "\n\t2. TelephoneSynchronizer.getInstance().updateTelephoneTable()" + 
		                         "\n\t3. PersonSynchronizer.updatePersonTelephoneTable()" + 
		                         "\n\t4. PersonTelephoneSynchronizer.getInstance().updatePersonTelephoneTable");
		

		TelephoneStruct ts = TelephoneStruct.createTelephoneStruct(countryNumber,telephoneNumber);
		if(PersonSynchronizer.isDebug()) System.out.println("PersonSynchronizer.updateTelephoneTable() - after  1. createTelephoneStruct");
		
		int telephoneKey = TelephoneSynchronizer.getInstance().updateTelephoneTable(countryNumber,
				telephoneNumber);
		if(PersonSynchronizer.isDebug()) System.out.println("PersonSynchronizer.updateTelephoneTable() - after 2. TelephoneSynchronizer.getInstance().updateTelephoneTable()");
		
		this.updatePersonTelephoneTable(personNumber, telephoneKey, ts.getExtension());
		if(PersonSynchronizer.isDebug()) System.out.println("PersonSynchronizer.updateTelephoneTable() - after 3. PersonSynchronizer.updatePersonTelephoneTable()");
		
		
	    PersonTelephoneSynchronizer.getInstance().updatePersonTelephoneTable(personNumber, telephoneKey, ts.getExtension(), null);
	    if(PersonSynchronizer.isDebug()) System.out.println("PersonSynchronizer.updateTelephoneTable() - after  4. PersonTelephoneSynchronizer.getInstance().updatePersonTelephoneTable");
		
		return false;
	}

	private boolean updatePersonTelephoneTable(int personNumber,
			int telephoneTableRecordKey, String telephoneNumberExtension) throws TelephoneNumberException {
		return true;
	}

	/**
	 * this builds the Insert code to put the person in the GRIIDC database.
	 * Since much of the data could be missing, only insert the fields for which
	 * there is data.
	 * 
	 * @param personNumber
	 * @param postalAreaNumber
	 * @param deliveryPoint
	 * @param lastName
	 * @param firstName
	 * @param middleName
	 * @param honorificTitle
	 * @param nameSuffix
	 * @return
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 */
	private String formatAddPersonQuery(int personNumber, int postalAreaNumber,
			String deliveryPoint, String lastName, String firstName,
			String middleName, String honorificTitle, String nameSuffix)
			throws SQLException, ClassNotFoundException {
		StringBuffer sb = new StringBuffer("INSERT INTO ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ RdbmsConstants.SPACE + "(");
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Person_Number"));
		if (postalAreaNumber > -1) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInDoubleQuotes("PostalArea_Number"));
		}
		if (!MiscUtils.isStringEmpty(deliveryPoint)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection
							.wrapInDoubleQuotes("Person_DeliveryPoint"));
		}
		sb.append(RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Person_LastName"));
		sb.append(RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Person_FirstName"));
		if (!MiscUtils.isStringEmpty(middleName)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInDoubleQuotes("Person_MiddleName"));
		}

		if (!MiscUtils.isStringEmpty(honorificTitle)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection
							.wrapInDoubleQuotes("Person_HonorificTitle"));
		}
		if (!MiscUtils.isStringEmpty(nameSuffix)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInDoubleQuotes("Person_NameSuffix"));
		}
		sb.append(") VALUES (");
		// the values are here
		sb.append(personNumber);
		if (postalAreaNumber > -1) {
			sb.append(RdbmsConstants.CommaSpace + postalAreaNumber);
		}
		if (!MiscUtils.isStringEmpty(deliveryPoint)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInSingleQuotes(deliveryPoint));
		}
		sb.append(RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(lastName));
		sb.append(RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(firstName));
		if (!MiscUtils.isStringEmpty(middleName)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInSingleQuotes(middleName));
		}
		if (!MiscUtils.isStringEmpty(honorificTitle)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInSingleQuotes(honorificTitle));
		}
		if (!MiscUtils.isStringEmpty(nameSuffix)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInSingleQuotes(nameSuffix));
		}
		sb.append(" )");
		return sb.toString();
	}

	private String formatModifyPersonQuery(int personNumber,
			int postalAreaNumber, String deliveryPoint, String lastName,
			String firstName, String middleName, String honorificTitle,
			String nameSuffix) throws SQLException, ClassNotFoundException {
		boolean firstValue = true;
		StringBuffer sb = new StringBuffer("UPDATE  ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ RdbmsConstants.SPACE + " SET ");

		if (postalAreaNumber > -1) {
			if (!firstValue)
				sb.append(RdbmsConstants.CommaSpace);
			else
				sb.append(RdbmsConstants.SPACE);
			sb.append(RdbmsConnection.wrapInDoubleQuotes("PostalArea_Number")
					+ RdbmsConstants.EqualSign + postalAreaNumber);
			firstValue = false;
		}
		if (!MiscUtils.isStringEmpty(deliveryPoint)) {
			if (!firstValue)
				sb.append(RdbmsConstants.CommaSpace);
			else
				sb.append(RdbmsConstants.SPACE);
			sb.append(RdbmsConnection
					.wrapInDoubleQuotes("Person_DeliveryPoint")
					+ RdbmsConstants.EqualSign
					+ RdbmsConnection.wrapInSingleQuotes(deliveryPoint));

			firstValue = false;
		}

		if (!firstValue)
			sb.append(RdbmsConstants.CommaSpace);
		else
			sb.append(RdbmsConstants.SPACE);
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Person_LastName")
				+ RdbmsConstants.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(lastName));
		firstValue = false;

		sb.append(RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Person_FirstName")
				+ RdbmsConstants.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(firstName));

		if (!MiscUtils.isStringEmpty(middleName)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInDoubleQuotes("Person_MiddleName")
					+ RdbmsConstants.EqualSign
					+ RdbmsConnection.wrapInSingleQuotes(middleName));
		}
		if (!MiscUtils.isStringEmpty(honorificTitle)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection
							.wrapInDoubleQuotes("Person_HonorificTitle")
					+ RdbmsConstants.EqualSign
					+ RdbmsConnection.wrapInSingleQuotes(honorificTitle));
		}
		if (!MiscUtils.isStringEmpty(nameSuffix)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInDoubleQuotes("Person_NameSuffix")
					+ RdbmsConstants.EqualSign
					+ RdbmsConnection.wrapInSingleQuotes(nameSuffix));
		}

		sb.append(" WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes("Person_Number")
				+ RdbmsConstants.EqualSign + personNumber);
		return sb.toString();
	}

	private String griidcPersonToString(int peopleId, int peopleInstId,
			int peopleDeptId, int postalAreaNumber, String deliveryPoint,
			String lastName, String firstName, String middleName) {
		String msg = "People: " + peopleId + ", " + "InstId: " + peopleInstId
				+ ", " + "DeptId : " + peopleDeptId + ", " + " postal area: "
				+ postalAreaNumber + ", " + " delivery point: " + deliveryPoint
				+ ", " + "Last name: " + lastName + ", " + "Firs: " + firstName
				+ ", " + "Middle : " + middleName;
		return msg;
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
	private boolean isCurrentRecordEqual() {
		
		if (!(this.tempPostalAreaNumber == this.griidcPersonPostalArea_Number))
			return false;
		if (!(this.tempDeliveryPoint == this.griidcPerson_DeliveryPoint))
			return false;
		if (!(areStringsEqual(this.risPeople_LastName,
				this.griidcPerson_LastName)))
			return false;
		if (!(areStringsEqual(this.risPeople_MiddleName,
				this.griidcPerson_MiddleName)))
			return false;
		if (!(areStringsEqual(this.risPeople_FirstName,
				this.griidcPerson_FirstName)))
			return false;
		if (!(areStringsEqual(this.risPeople_Suffix,
				this.griidcPerson_NameSuffix)))
			return false;
		if (!(areStringsEqual(this.risPeople_Title,
				this.griidcPerson_HonorificTitle)))
			return false;
		return true;
	}

	/**
	 * compare two strings either of which could be null or empty
	 * 
	 * @param s1
	 * @param s2
	 * @return true if they are equal
	 */
	private boolean areStringsEqual(String s1, String s2) {
		if (s1 == null && s2 == null)
			return true; // both are null return true
		if (s1 == null || s2 == null) { // one is null but not both
			return false;
		}

		// both are non null
		if (s1.trim().length() != s2.trim().length())
			return false; // different lengths, can't be equal return false
		if (s1.trim().equals(s2.trim()))
			return true;
		return false;
	}

	public String getPrimaryLogFileName() throws FileNotFoundException,
			PropertyNotFoundException {
		return RisToGriidcConfiguration.getPrimaryLogFileName();
	}

	public String getRisErrorLogFileName() throws FileNotFoundException,
			PropertyNotFoundException {
		return RisToGriidcConfiguration.getRisErrorLogFileName();
	}

	public static boolean isDebug() {
		return PersonSynchronizer.debug;
	}

	public static void setDebug(boolean db) {
		PersonSynchronizer.debug = db;
	}

	public void reportTables() throws IOException, PropertyNotFoundException,
			SQLException, ClassNotFoundException, TableNotInDatabaseException {
		RdbmsUtils.reportTables(RisTableName, GriidcTableName);
		return;
	}

	private int getPostalAreaNumber(int departmentId, String state,
			String city, String zip) throws FileNotFoundException,
			SQLException, NoRecordFoundException, DuplicateRecordException,
			ClassNotFoundException, PropertyNotFoundException,
			MissingArgumentsException {
		int countryNumber = RdbmsUtils
				.getGriidcDepartmentCountryNumber(departmentId);
		return RdbmsUtils.getGriidcDepartmentPostalNumber(countryNumber, state,
				city, zip);
		//
		// why not call RdbmsConstants.getGriidcDepartmentPostalNumber(dptNumber)
		// ?????
	}

	public int getTelephoneRecordsAdded() {
		return TelephoneSynchronizer.getInstance().getGriidcRecordsAdded();
	}

	public int getRisTelephoneErrors() {
		return TelephoneSynchronizer.getInstance().getRisTelephoneErrors();
	}
	
	public int getRisTelephoneRecordsRead() {
		return TelephoneSynchronizer.getInstance().getRisTelephoneRecords();
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

	private String getFormatedRisPeople() {
		String msg = "Add GRIIDC Person table record " + "Person Number: "
				+ this.risPeople_Id + ", Delivery Point: "
				+ griidcPerson_DeliveryPoint + "InstId: "
				+ this.risPeople_InstitutionId + ", " + "DeptId : "
				+ this.risPeople_DepartmentId + ", " + "Title: "
				+ this.risPeople_Title + ", " + "Last name: "
				+ this.risPeople_LastName + ", " + "Firs: "
				+ this.risPeople_FirstName + ", " + "Middle : "
				+ this.risPeople_MiddleName + ", " + "Suffix: "
				+ this.risPeople_Suffix + ", " + "Addr1: "
				+ this.risPeople_AdrStreet1 + ", " + "Addr2: "
				+ this.risPeople_AdrStreet2 + ", " + "City: "
				+ this.risPeople_AdrCity + ", " + "State: "
				+ this.risPeople_AdrState + ", " + "Zip: "
				+ this.risPeople_AdrZip;
		return msg;
	}
}
