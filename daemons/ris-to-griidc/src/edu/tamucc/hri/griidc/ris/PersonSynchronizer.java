package edu.tamucc.hri.griidc.ris;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import javax.mail.internet.AddressException;

import edu.tamucc.hri.griidc.exception.MultipleRecordsFoundException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.exception.TelephoneNumberException;
import edu.tamucc.hri.griidc.rdbms.IntStringDbCache;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.SynchronizerBase;
import edu.tamucc.hri.griidc.utils.GriidcRisDepartmentMap;
import edu.tamucc.hri.griidc.utils.GriidcRisInstitutionMap;
import edu.tamucc.hri.griidc.utils.HeuristicMatching;
import edu.tamucc.hri.griidc.utils.MessageContainer;
import edu.tamucc.hri.griidc.utils.MiscUtils;
import edu.tamucc.hri.griidc.utils.PeoplePersonMap;
import edu.tamucc.hri.griidc.utils.RisInstDeptPeopleErrorCollection;
import edu.tamucc.hri.griidc.utils.GriidcConfiguration;

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
 * of the referenced "Institutions" records is used for the address of the
 * "Person" record
 * 
 * @author jvh
 * 
 */
public class PersonSynchronizer extends SynchronizerBase {

	private static final String RisTableName = RdbmsConstants.RisPeopleTableName;
	private static final String GriidcPersonTableName = RdbmsConstants.GriidcPersonTableName;

	private int risRecordCount = 0;
	private int risRecordsSkipped = 0;
	private int risRecordErrors = 0;
	private int griidcPersonRecordsAdded = 0;
	private int griidcPersonRecordsModified = 0;
	private int griidcPersonRecordDuplicates = 0;

	private static int lastRisPeople_Id = RdbmsConstants.NotFound;
	private static int PeopleIdThreshold = 10000;
	private int risPeople_Id = RdbmsConstants.NotFound;
	private int risPeople_InstitutionId = RdbmsConstants.NotFound;
	private int risPeople_DepartmentId = RdbmsConstants.NotFound;
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
	private int griidcPerson_Number = RdbmsConstants.NotFound;
	private int griidcPersonPostalArea_Number = RdbmsConstants.NotFound;
	private String griidcPerson_DeliveryPoint = null;
	private String griidcPerson_FirstName = null;
	private String griidcPerson_LastName = null;
	private String griidcPerson_MiddleName = null;
	private String griidcPerson_HonorificTitle = null;
	private String griidcPerson_NameSuffix = null;

	// GRIIDC GoMRIPerson-Department-RIS_ID table

	private int griidcPersonConnectorPersonNumber = RdbmsConstants.NotFound;

	/***************************************
	 * Department_Number integer Institution_Number integer PostalArea_Number
	 * integer Department_DeliveryPoint text Department_Name text Department_URL
	 * text Department_GeoCoordinate USER-DEFINED
	 ********************************************/

	// get all the values from the RIS Peoples table

	private ResultSet rset = null;
	private ResultSet griidcRset = null;

	private static boolean debug = false;
	private static boolean AncillaryReport = false;
	private boolean initialized = false;
	private HeuristicMatching heuristics = new HeuristicMatching();
	private IntStringDbCache griidcInstitutionNumberCache = null;
	private RisInstDeptPeopleErrorCollection risErrorCollection = null;

	private GriidcRisDepartmentMap griidcRisDepartmentMap = null;
	private GriidcRisInstitutionMap griidcRisInstitutionMap = null;
	private GomriPersonAgent gomriPersonAgent = new GomriPersonAgent();
	private GomriPersonDepartmentRisIdAgent gomriPersonDepartmentRisIdAgent = null;
	private EmailSynchronizer emailSynchronizer = null;
	private TelephoneSynchronizer telephoneSynchronizer = null;
	private PersonTelephoneSynchronizer personTelephoneSynchronizer = null;
	
	private MessageContainer messageContainer = new MessageContainer();
	
	private PeoplePersonMap peoplePersonMap = PeoplePersonMap.getInstance();

	public PersonSynchronizer() {
		// TODO Auto-generated constructor stub
	}

	public boolean isInitialized() {
		return initialized;
	}

	public void initialize() {
		super.commonInitialize();
		if (!isInitialized()) {
			// a set of all the GRIIDC institution numbers
			this.griidcInstitutionNumberCache = new IntStringDbCache(
					this.griidcDbConnection, "Institution",
					"Institution_Number", "Institution_Name");
			this.griidcInstitutionNumberCache.buildCacheFromDb();
			this.griidcRisDepartmentMap = RdbmsUtils
					.getGriidcRisDepartmentMap();
			this.griidcRisInstitutionMap = RdbmsUtils
					.getGriidcRisInstitutionMap();
			this.emailSynchronizer = EmailSynchronizer.getInstance();
			this.telephoneSynchronizer = TelephoneSynchronizer.getInstance();
			this.personTelephoneSynchronizer = PersonTelephoneSynchronizer
					.getInstance();
			this.gomriPersonDepartmentRisIdAgent = new GomriPersonDepartmentRisIdAgent();
			this.initialized = true;
		}
	}

	/*****
	 * @throws SQLException
	 * @throws IOException
	 * @throws TableNotInDatabaseException
	 * @throws NoRecordFoundException
	 * @throws MultipleRecordsFoundException
	 */

	public RisInstDeptPeopleErrorCollection syncGriidcPersonFromRisPeople(
			RisInstDeptPeopleErrorCollection risErrorSet) throws IOException,
			SQLException, TableNotInDatabaseException {
		int countryNumber = RdbmsConstants.NotFound;
		int tempPostalAreaNumber = RdbmsConstants.NotFound;
		String tempDeliveryPoint = null;

		int griidcDepartmentNumber = RdbmsConstants.NotFound;
		int griidcInstitutionNumber = RdbmsConstants.NotFound;
		if (isDebug())
			System.out.println(MiscUtils.BreakLine);
		this.risErrorCollection = risErrorSet;

		this.initialize();

		String msg = null;
		// get all records from the RIS People table
		try {
			rset = this.risDbConnection.selectAllValuesFromTable(RisTableName);

			while (rset.next()) { // continue statements branch back to here for
									// next RIS People record
				risRecordCount++;
				readRisPeopleRecord();
				PersonSynchronizer.lastRisPeople_Id = this.risPeople_Id;
				this.peoplePersonMap.put(this.risPeople_Id);
				if (isDebug()) {
					this.messageContainer.toOut();
					this.messageContainer.initialize();
					this.messageContainer.add("\n" + this.formatedRisPeople());
				}
				/****
				 * find and update the GRIIDC Person table with these values
				 */
				tempPostalAreaNumber = RdbmsConstants.NotFound;
				/**
				 * we must have a valid department and institution in the
				 * database with which to associate this Person record
				 */

				try {
					griidcInstitutionNumber = this
							.getGriidcInstitutionNumber(this.risPeople_InstitutionId);

					griidcDepartmentNumber = getGriidcDepartmentNumber(this.risPeople_DepartmentId);

					RdbmsUtils.doesGriidcDepartmentExist(
							griidcInstitutionNumber, griidcDepartmentNumber);

				} catch (NoRecordFoundException e2) {
					// no Department/Institution found in the database
					// add the people record to the error collection
					msg = "Error P-1 in RIS People - RIS People_ID: "
							+ risPeople_Id
							+ ", RIS People_InstitutionId: "
							+ risPeople_InstitutionId
							+ " - GRIIDC Institution: "
							+ griidcInstitutionNumber
							+ ", RIS People_DepartmentId: "
							+ risPeople_DepartmentId
							+ " - GRIIDC Department: "
							+ griidcDepartmentNumber
							+ "\nThe referenced Institution and/or Department was not found in the database.\n"
							+ e2.getMessage();
					if (isDebug())
						this.messageContainer.add(">>P-1 Skip this one: " + msg);

					this.risErrorCollection.addPerson(
							this.risPeople_InstitutionId,
							this.risPeople_DepartmentId, this.risPeople_Id);
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToRisErrorLogFile(msg);
					this.risRecordsSkipped++;
					this.risRecordErrors++;
					//
					continue; // branch back to while (rset.next())
					//

				} catch (MultipleRecordsFoundException e2) {

					msg = "Error P-2 in RIS People - record id: "
							+ risPeople_Id + ", institution: "
							+ risPeople_InstitutionId + ", department: "
							+ risPeople_DepartmentId + ".\n" + e2.getMessage();
					if (isDebug())
						this.messageContainer.add(">>P-2 Skip this one: " + msg);
					e2.printStackTrace();
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToRisErrorLogFile(msg);
					this.risRecordsSkipped++;
					this.risRecordErrors++;
					continue; // branch back to while (rset.next())
				}

				// try to get a postal area number from the country, state,
				// city, zip
				// but don't reject if not possible
				// The Person record can be created without it
				try {
					countryNumber = RdbmsUtils
							.getGriidcDepartmentCountryNumber(this.risPeople_DepartmentId);
					tempPostalAreaNumber = RdbmsUtils
							.getGriidcDepartmentPostalNumber(countryNumber,
									this.risPeople_AdrState,
									this.risPeople_AdrCity,
									this.risPeople_AdrZip);

				} catch (MultipleRecordsFoundException e) {
					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					if (isDebug())
						this.messageContainer.add(">>CC Skip this one: "
								+ e.getMessage());
					this.risRecordsSkipped++;
					//
					continue; // branch back to while (rset.next())
					//
				} catch (SQLException e) {
					e.printStackTrace();
					this.risRecordsSkipped++;
					//
					continue; // branch back to while (rset.next())
					//
				} catch (MissingArgumentsException e) {
					// do nothing - Person is not required to have Postal Area
					// Number
				} catch (NoRecordFoundException e) {
					// failed to find one of tempPostalAreaNumber or
					// country code
					// fall through and try with just the department number
				}
				try {
					// try and get a postal area number from the department id
					// but don't reject if not possible
					// The Person record can be created without it
					tempPostalAreaNumber = RdbmsConstants.NotFound;
					tempPostalAreaNumber = RdbmsUtils
							.getGriidcDepartmentPostalNumber(this.risPeople_DepartmentId);
				} catch (NoRecordFoundException e) {
					// did not find tempPostalAreaNumber
					tempPostalAreaNumber = RdbmsConstants.NotFound;

				} catch (MultipleRecordsFoundException e) {

					MiscUtils.writeToPrimaryLogFile(e.getMessage());
					this.risRecordsSkipped++;
					//
					continue; // branch back to while (rset.next())
					//

				} catch (SQLException e) {
					e.printStackTrace();
					this.risRecordsSkipped++;
					//
					continue; // branch back to while (rset.next())
					//

				}
				/*
				 * if the data in RIS is unusable - skip this record - go to the
				 * next record *
				 */

				tempDeliveryPoint = MiscUtils.makeDeliveryPoint(
						this.risPeople_AdrStreet1, this.risPeople_AdrStreet2);

				//
				// a good RIS record has been read.
				// search GRIIDC for a matching Person
				// If a Person is NOT FOUND add it
				// If a Person Number (indicating a match found) is returned
				// update may be required
				// Multiple records can't (should not) happen because of DBMS
				// key
				// constraints

				int correspondingGriidcPersonNum = RdbmsConstants.NotFound;

				try {
					// All GRIIDC Person records have a representation in the
					// GoMRI Person-Department-RisId table
					// if found there then a Person record exists (DBMS
					// referential integrity)
					correspondingGriidcPersonNum = this.gomriPersonDepartmentRisIdAgent
							.readGomriPersonDepartmentRisId(this.risPeople_Id,
									griidcDepartmentNumber);
					// matching record found Modify Person record
					// read the GRIIDC Person record
					readGriidcPersonRecord(correspondingGriidcPersonNum);
					if (isDebug())
						this.messageContainer.add("Read GRIIDC Person: "
								+ this.griidcPersonToString());
					if (isCurrentRecordEqual(tempPostalAreaNumber,
							tempDeliveryPoint)) {
						if (isDebug())
							this.messageContainer.add("Person/People rcords are equal");
						this.griidcPersonRecordDuplicates++;
						continue;
					} else { // matching key but not complete data match
								// modify the record
						try {

							sendModifyMessage(tempPostalAreaNumber,
									tempDeliveryPoint);
							modifyGriidcPerson(correspondingGriidcPersonNum,
									tempPostalAreaNumber, tempDeliveryPoint);
						} catch (SQLException e) {
							System.err
									.println("SQL Error: Modify Person in GRIIDC "
											+ e.getMessage());
							e.printStackTrace();
						}
					}
				} catch (NoRecordFoundException e) { // add the Person record
					try {
						correspondingGriidcPersonNum = addGriidcPerson(
								tempPostalAreaNumber, tempDeliveryPoint);
						this.peoplePersonMap.put(this.risPeople_Id,correspondingGriidcPersonNum);
					} catch (SQLException e1) {
						msg = "Error P-6 SQL Error: Add Person in GRIIDC "
								+ e1.getMessage();
						System.out.println(">>" + msg);
						MiscUtils.writeToPrimaryLogFile(msg);

						// check here to see if the department and
						// institution are
						// on the error set
						this.risErrorCollection.addPerson(
								this.risPeople_InstitutionId,
								this.risPeople_DepartmentId, this.risPeople_Id);
						continue;
					}
				}

				/**
				 * Add a GoMRIPerson-Department-RIS_ID for every unique
				 * concatenated key of Griidc Person_Number, RIS People_ID and
				 * GRIIDC Department_Number. The above codes is only concerned
				 * with the existence of the Person Record and a matching
				 * GoMRIPerson-Department-RIS_ID record with both the Person
				 * Number and the People_ID.
				 */

				this.gomriPersonAgent.updateGoMRIPerson(
						correspondingGriidcPersonNum, this.risPeople_FirstName,
						this.risPeople_MiddleName, this.risPeople_LastName, "",
						this.risPeople_Title);

				this.gomriPersonDepartmentRisIdAgent
						.updateGomriPersonDepartmentRisId(risPeople_Id,
								griidcDepartmentNumber,
								correspondingGriidcPersonNum,
								this.risPeople_Comment);

				//
				// duplicate, added or modified the RIS record might have
				// modified email or telephone information
				try {
					updateEmailAndTelephoneInformation(
							correspondingGriidcPersonNum, this.risPeople_Email,
							true, countryNumber, this.risPeople_PhoneNum);
				} catch (TelephoneNumberException e1) {
					String pInfo = "" + correspondingGriidcPersonNum + " "
							+ this.risPeople_LastName + ", "
							+ this.risPeople_FirstName + " "
							+ this.risPeople_MiddleName;
					msg = "Error P-3 When modifying telephone number for Person: "
							+ pInfo + "\n" + e1.getMessage();
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToRisErrorLogFile(msg);
				}

			} // end of main while loop
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		if(PersonSynchronizer.isDebug()) {
			System.out.println(this.peoplePersonMap.columnerToString());
			String path = MiscUtils.getWorkingDirectory();
			String fileName = "PeopleToPersonMap.txt";
			MiscUtils.writeStringToFile(path, fileName, this.peoplePersonMap.columnerToString());
		}
		return this.risErrorCollection;
		// end of Person
	}

	private void sendModifyMessage(int tempPostalAreaNumber,
			String tempDeliveryPoint) {
		if (PersonSynchronizer.isDebug()) {
			String msg = "\nModify GRIIDC Person table matching "
					+ ", Person_Name: " + risPeople_LastName + ", "
					+ risPeople_FirstName + " " + risPeople_MiddleName
					+ ", PostalArea_Number: " + tempPostalAreaNumber
					+ ", Person_DeliveryPoint: " + tempDeliveryPoint;
			this.messageContainer.add(msg);
		}
	}

	private void readRisPeopleRecord() throws SQLException {
		this.risPeople_Id = rset.getInt("People_ID");
		this.risPeople_InstitutionId = rset.getInt("People_Institution");
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
		return;
	}

	/**
	 * read the GRIIDC Person table and the GoMRIPerson-Department-RIS_ID record
	 * that ties the RIS People to GRIIDC Person
	 * 
	 * @param risPeople_Id
	 * @return griidcPersonNumber
	 * @throws SQLException
	 */
	private int readGriidcPersonRecord(int personNumKey) throws SQLException {

		String query = "SELECT * FROM "
				// + this.getWrappedGriidcShemaName() + "."
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcPersonTableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes("Person_Number")
				+ RdbmsConstants.EqualSign + personNumKey;

		griidcRset = this.griidcDbConnection.executeQueryResultSet(query);
		this.griidcPerson_Number = RdbmsConstants.NotFound;
		while (this.griidcRset.next()) {

			this.griidcPerson_Number = griidcRset.getInt("Person_Number");
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
		return this.griidcPerson_Number;
	}

	private boolean updateEmailAndTelephoneInformation(int personNumber,
			String emailAddr, boolean primaryEmail, int countryNumber,
			String telephoneNumber) throws TelephoneNumberException {
		updateEmailTable(personNumber, emailAddr, primaryEmail);
		updateTelephoneTable(personNumber, countryNumber, telephoneNumber);
		return true;
	}

	private int getGriidcDepartmentNumber(int risDepartmentId)
			throws NoRecordFoundException {
		return this.griidcRisDepartmentMap
				.getGriidcDepartmentNumber(risDepartmentId);
	}

	private int getGriidcInstitutionNumber(int risInstitutionId)
			throws NoRecordFoundException {
		return this.griidcRisInstitutionMap
				.getGriidcInstitutionNumber(risInstitutionId);
	}

	/**
	 * add the GRIIDC Person record and add the GoMRIPerson-Department-RIS_ID
	 * record that ties the RIS People to GRIIDC Person Using INSERT ... RETURN
	 * return the last Person_Number added
	 * 
	 * @returns the key (Person_Number) of the last Person added
	 * @throws SQLException
	 */
	private int addGriidcPerson(int tempPostalAreaNumber,
			String tempDeliveryPoint) throws SQLException {
		String addQuery = null;
		if (isDebug())
			this.messageContainer.add("PersonSynchronizer.addGriidcPerson(postal area: "
							+ tempPostalAreaNumber + ", deliery point: "
							+ tempDeliveryPoint + ")");
		addQuery = this.formatAddPersonQuery(tempPostalAreaNumber,
				tempDeliveryPoint, this.risPeople_LastName,
				this.risPeople_FirstName, this.risPeople_MiddleName,
				this.risPeople_Title, this.risPeople_Suffix);
		if (isDebug())
			this.messageContainer.add("PersonSynchronizer.addGriidcPerson() query: "
					+ addQuery);
		ResultSet personRs = this.griidcDbConnection
				.executeQueryResultSet(addQuery);

		int lastKey = RdbmsConstants.NotFound;
		while (personRs.next()) {
			lastKey = personRs.getInt("Person_Number");
			if (isDebug())
				this.messageContainer.add("PersonSynchronizer.addGriidcPerson() lastKey: "
								+ lastKey);
		}
		this.griidcPersonRecordsAdded++;
		//
		// if this fails it could be because the department or
		// institution failed on creation
		// previously in InstitutionSynchronizer and
		// DepartmentSynchronizer
		//
		String msg = "Added Person: "
				+ griidcPersonToString(lastKey, tempPostalAreaNumber,
						tempDeliveryPoint, this.risPeople_LastName,
						this.risPeople_FirstName, this.risPeople_MiddleName,
						this.risPeople_Title, this.risPeople_Suffix);
		MiscUtils.writeToPrimaryLogFile(msg);
		if (PersonSynchronizer.isDebug())
			this.messageContainer.add(msg);
		return lastKey;
	}

	private void modifyGriidcPerson(int gPersonNum, int tempPostalAreaNumber,
			String tempDeliveryPoint) throws SQLException {
		String modifyQuery = null;
		modifyQuery = this.formatModifyPersonQuery(gPersonNum,
				tempPostalAreaNumber, tempDeliveryPoint,
				this.risPeople_LastName, this.risPeople_FirstName,
				this.risPeople_MiddleName, this.risPeople_Title,
				this.risPeople_Suffix);

		this.griidcDbConnection.executeQueryBoolean(modifyQuery);
		this.griidcPersonRecordsModified++;

		String msg = "Modified GRIIDC Person: "
				+ griidcPersonToString(gPersonNum, tempPostalAreaNumber,
						tempDeliveryPoint, this.risPeople_LastName,
						this.risPeople_FirstName, this.risPeople_MiddleName,
						this.risPeople_Title, this.risPeople_Suffix);

		MiscUtils.writeToPrimaryLogFile(msg);
		if (PersonSynchronizer.isDebug())
			this.messageContainer.add(msg);
	}

	/**
	 * modify or add an email address for the person Id provided. validate the
	 * email and handle the exception
	 * 
	 * @param pepId
	 * @param emailAddr
	 * @param primary
	 * @return
	 * @throws IOException
	 */
	private boolean updateEmailTable(int pepId, String emailAddr,
			boolean primary) {
		String msg = null;

		try {
			emailSynchronizer.update(pepId, emailAddr, primary);
			return true;
		} catch (MultipleRecordsFoundException e) {
			msg = "Error P-4 " + e.getMessage();
			MiscUtils.writeToRisErrorLogFile(msg);
			MiscUtils.writeToPrimaryLogFile(msg);
		} catch (SQLException e) {
			msg = "SQL Error: EmailInfo: " + e.getMessage();
		} catch (AddressException e) {
			String emailInfo = EmailSynchronizer.formatData(pepId, emailAddr,
					primary);
			msg = "Error P-5 Email Address Exception for : " + emailInfo + " "
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
	 * @throws FileNotFoundException
	 */
	private boolean updateTelephoneTable(int personNumber, int countryNumber,
			String telephoneNumber) throws TelephoneNumberException {

		TelephoneStruct ts = TelephoneStruct.createTelephoneStruct(
				countryNumber, telephoneNumber);

		int telephoneKey = telephoneSynchronizer.updateTelephoneTable(
				countryNumber, telephoneNumber);
		this.personTelephoneSynchronizer.updatePersonTelephoneTable(
				personNumber, telephoneKey, ts.getExtension(), null);
		return false;
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
	 */
	private String formatAddPersonQuery(int postalAreaNumber,
			String deliveryPoint, String lastName, String firstName,
			String middleName, String honorificTitle, String nameSuffix)
			throws SQLException {

		StringBuffer sb = new StringBuffer("INSERT INTO ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes(GriidcPersonTableName)
				+ RdbmsConstants.SPACE + "(");
		sb.append(RdbmsConnection.wrapInDoubleQuotes("Person_LastName"));
		sb.append(RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes("Person_FirstName"));

		if (!MiscUtils.isStringEmpty(middleName)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInDoubleQuotes("Person_MiddleName"));
		}

		if (!MiscUtils.isStringEmpty(nameSuffix)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInDoubleQuotes("Person_NameSuffix"));
		}

		if (!MiscUtils.isStringEmpty(honorificTitle)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection
							.wrapInDoubleQuotes("Person_HonorificTitle"));
		}

		if (!(postalAreaNumber == RdbmsConstants.NotFound)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInDoubleQuotes("PostalArea_Number"));
		}
		if (!MiscUtils.isStringEmpty(deliveryPoint)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection
							.wrapInDoubleQuotes("Person_DeliveryPoint"));
		}

		sb.append(") VALUES (");
		// the values are here
		sb.append(RdbmsConnection.wrapInSingleQuotes(lastName.trim()));

		sb.append(RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(firstName.trim()));
		if (!MiscUtils.isStringEmpty(middleName)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInSingleQuotes(middleName.trim()));
		}
		if (!MiscUtils.isStringEmpty(nameSuffix)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInSingleQuotes(nameSuffix.trim()));
		}
		if (!MiscUtils.isStringEmpty(honorificTitle)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInSingleQuotes(honorificTitle.trim()));
		}

		if (!(postalAreaNumber == RdbmsConstants.NotFound)) {
			sb.append(RdbmsConstants.CommaSpace + postalAreaNumber);
		}
		if (!MiscUtils.isStringEmpty(deliveryPoint)) {
			sb.append(RdbmsConstants.CommaSpace
					+ RdbmsConnection.wrapInSingleQuotes(deliveryPoint.trim()));
		}

		sb.append(" )");
		sb.append(" RETURNING "
				+ RdbmsConnection.wrapInDoubleQuotes("Person_Number"));
		return sb.toString();
	}

	private String formatModifyPersonQuery(int personNumber,
			int postalAreaNumber, String deliveryPoint, String lastName,
			String firstName, String middleName, String honorificTitle,
			String nameSuffix) throws SQLException {
		boolean firstValue = true;
		StringBuffer sb = new StringBuffer("UPDATE  ");
		sb.append(RdbmsConnection.wrapInDoubleQuotes(GriidcPersonTableName)
				+ RdbmsConstants.SPACE + " SET ");

		if (!(postalAreaNumber == RdbmsConstants.NotFound)) { // a "valid"
																// looking
																// postal Area
																// Number
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

	private String griidcPersonToString(int gPersonNum, int postalAreaNumber,
			String deliveryPoint, String lastName, String firstName,
			String middleName, String title, String suffix) {
		String msg = "Num: " + gPersonNum + ", " + " postal area: "
				+ postalAreaNumber + ", " + " delivery point: " + deliveryPoint
				+ ", Last name: " + lastName + ", First: " + firstName
				+ ", Middle : " + middleName + ", Title: " + title
				+ ", Suffix: " + suffix;
		return msg;
	}

	private String griidcPersonToString() {
		return "Number: " + this.griidcPerson_Number + ", " + " postal area: "
				+ this.griidcPersonPostalArea_Number + ",  delivery point: "
				+ this.griidcPerson_DeliveryPoint + ", Last name: "
				+ this.griidcPerson_LastName + ", First: "
				+ this.griidcPerson_FirstName + ", Middle: "
				+ this.griidcPerson_MiddleName + ", Title: "
				+ this.griidcPerson_HonorificTitle + ", Suffix: "
				+ this.griidcPerson_NameSuffix;
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
	private boolean isCurrentRecordEqual(int tempPostalAreaNumber,
			String tempDeliveryPoint) {

		printComparrison(tempPostalAreaNumber, tempDeliveryPoint);

		if (!(tempPostalAreaNumber == RdbmsConstants.NotFound) && // if NotFound
																	// ignore
																	// this
																	// field
				!(tempPostalAreaNumber == this.griidcPersonPostalArea_Number)) {
			return false; // not equal tempPostalArea is different that what is
							// in the griidc record
		}
		return ((MiscUtils.areStringsEqual(tempDeliveryPoint,
				this.griidcPerson_DeliveryPoint))
				&& (MiscUtils.areStringsEqual(this.risPeople_LastName,
						this.griidcPerson_LastName))
				&& (MiscUtils.areStringsEqual(this.risPeople_MiddleName,
						this.griidcPerson_MiddleName))
				&& (MiscUtils.areStringsEqual(this.risPeople_FirstName,
						this.griidcPerson_FirstName))
				&& (MiscUtils.areStringsEqual(this.risPeople_Suffix,
						this.griidcPerson_NameSuffix)) && (MiscUtils
					.areStringsEqual(this.risPeople_Title,
							this.griidcPerson_HonorificTitle)));
	}

	private void printComparrison(int tempPostalAreaNumber,
			String tempDeliveryPoint) {
		if (!isDebug())
			return;
		String recType = "RIS ID:";
		String format = "%15s %6d %3d %10s %10s %10s %10s %10s%n";

		System.out.println("Compare");

		System.out.printf(format, recType, this.risPeople_Id,
				tempPostalAreaNumber, tempDeliveryPoint,
				this.risPeople_LastName, this.risPeople_MiddleName,
				this.risPeople_FirstName, this.risPeople_Suffix,
				this.risPeople_Title);
		recType = "GRIIDC Num:";
		System.out.printf(format, recType, this.griidcPerson_Number,
				this.griidcPersonPostalArea_Number,
				this.griidcPerson_DeliveryPoint, this.griidcPerson_LastName,
				this.griidcPerson_MiddleName, this.griidcPerson_FirstName,
				this.griidcPerson_NameSuffix, this.griidcPerson_HonorificTitle);
	}

	public String getPrimaryLogFileName() throws FileNotFoundException,
			PropertyNotFoundException {
		return GriidcConfiguration.getPrimaryLogFileName();
	}

	public String getRisErrorLogFileName() throws FileNotFoundException,
			PropertyNotFoundException {
		return GriidcConfiguration.getRisErrorLogFileName();
	}

	public static boolean isDebug() {
		return ((PersonSynchronizer.lastRisPeople_Id >= PersonSynchronizer.PeopleIdThreshold) && 
		        PersonSynchronizer.debug);
	}

	public static void setDebug(boolean db) {
		PersonSynchronizer.debug = db;
	}

	public void reportTables() throws IOException, PropertyNotFoundException,
			SQLException, TableNotInDatabaseException {
		RdbmsUtils.reportTables(RisTableName, GriidcPersonTableName);
		return;
	}

	public int getTelephoneRecordsAdded() {
		return telephoneSynchronizer.getGriidcRecordsAdded();
	}

	public int getRisTelephoneErrors() {
		return telephoneSynchronizer.getRisTelephoneErrors();
	}

	public int getRisTelephoneRecordsRead() {
		return telephoneSynchronizer.getRisTelephoneRecords();
	}

	public int getRisRecordCount() {
		return risRecordCount;
	}

	public int getGriidcPersonRecordsAdded() {
		return griidcPersonRecordsAdded;
	}

	public int getGriidcPersonRecordsModified() {
		return griidcPersonRecordsModified;
	}

	public int getGriidcPersonRecordDuplicates() {
		return griidcPersonRecordDuplicates;
	}

	public int getGriidcPersonDepartmentPeopleRecordsAdded() {
		return this.gomriPersonDepartmentRisIdAgent.getRecordsAdded();
	}

	public int getGriidcPersonDepartmentPeopleRecordsModified() {
		return this.gomriPersonDepartmentRisIdAgent.getRecordsModified();
	}

	public int getGomriPersonRecordsAdded() {
		return this.gomriPersonAgent.getRecordsAdded();
	}

	public int getRisRecordsSkipped() {
		return risRecordsSkipped;
	}

	public int getRisRecordErrors() {
		return risRecordErrors;
	}

	public EmailSynchronizer getEmailUpdater() {
		return emailSynchronizer;
	}

	private String formatedRisPeople() {
		String msg = "RIS People table record " + "People ID: "
				+ this.risPeople_Id + ", InstId: "
				+ this.risPeople_InstitutionId + ", " + "DeptId : "
				+ this.risPeople_DepartmentId + ", " + "Title: "
				+ this.risPeople_Title + ", " + "Last name: "
				+ this.risPeople_LastName + ", " + "First: "
				+ this.risPeople_FirstName + ", " + "Middle: "
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
