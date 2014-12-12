package edu.tamucc.hri.griidc.ris;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.SynchronizerBase;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.rdbms.DefaultValue;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public class PersonTelephoneSynchronizer extends SynchronizerBase {
	private static final String TableName = "Person-Telephone";
	private static final String PersonNumberColName = "Person_Number";
	private static final String TelephoneKeyColName = "Telephone_Key";
	private static final String TelephoneExtensionColName = "Telephone_Extension";
	private static final String TelephoneTypeColName = "Telephone_Type";
	
	private int griidcPersonNumber = -1;
	private int griidcTelephoneKey = -1;
	private String griidcTelephoneExt = null;
	private String griidcTelephoneType = null;

	private static final String[] ColNameArray = { PersonNumberColName,
			TelephoneKeyColName, TelephoneExtensionColName,
			TelephoneTypeColName };

	private static boolean Debug = false;
	private int griidcPersonTelephoneRecordsAdded = 0;
	private int griidcPersonTelephoneRecordsModified = 0;
	private int griidcPersonTelephoneRecordDuplicates = 0;
	private int risPersonTelephoneRecords = 0;
	private int risPersonTelephoneErrors = 0;

	private static PersonTelephoneSynchronizer instance = null;

	private PersonTelephoneSynchronizer() {

	}

	public void initialize() {
		super.commonInitialize();

	}

	public static PersonTelephoneSynchronizer getInstance() {
		if (PersonTelephoneSynchronizer.instance == null) {
			PersonTelephoneSynchronizer.instance = new PersonTelephoneSynchronizer();
		}
		return PersonTelephoneSynchronizer.instance;
	}

	/**
	 * @throws
	 * @throws TelephoneNumberException
	 * 
	 */
	public void updatePersonTelephoneTable(int personNumber,
			int telephoneTableRecordKey, String telephoneNumberExtension,
			String phoneType) {
		this.initialize();
		this.risPersonTelephoneRecords++;
		String format = "%nPersonTelephoneSync.update() person Num: %3d, telKey: %4d, ext: %-6s type: %s";
		if (PersonTelephoneSynchronizer.isDebug())
			System.out.printf(format,  personNumber,telephoneTableRecordKey, telephoneNumberExtension,phoneType);
		
		String tempPhoneType = phoneType;
		if (phoneType == null || phoneType.length() == 0)
			tempPhoneType = PersonTelephoneSynchronizer
					.getDefaultValueForTelephoneTypeColName();
		try {
			boolean found = false;
			found = this.findPersonTelephoneTableRecord(personNumber,
					telephoneTableRecordKey);
			if (found) { 
                if(isExactMatch(telephoneNumberExtension,tempPhoneType)) { // duplicate
                	this.griidcPersonTelephoneRecordDuplicates++;
                	return;
                } else { // modify
				this.modifyPersonTelephoneTableRecord(personNumber,
						telephoneTableRecordKey, telephoneNumberExtension,
						tempPhoneType);
                }
				return;
			} else {     // else add the record
				this.addPersonTelephoneTableRecord(personNumber,
						telephoneTableRecordKey, 
						telephoneNumberExtension,
						tempPhoneType);
				this.griidcPersonTelephoneRecordsAdded++;
				return;
			}

		} catch (SQLException e) {
			if (PersonTelephoneSynchronizer.isDebug())
				System.out
						.println("\nPersonTelephoneSynchronizer.updatePersonTelephoneTable() Sql error: "
								+ e.getMessage());
			this.risPersonTelephoneErrors++;
		}
		return;
	}

	private void modifyPersonTelephoneTableRecord(int personNumber,
			int telephoneTableRecordKey, String telephoneNumberExtension,
			String phoneType) throws SQLException {

		DbColumnInfo[] updateClauseInfo = getUpdateClauseInfo(
				telephoneNumberExtension, phoneType);

		DbColumnInfo[] whereClauseInfo = getWhereClauseInfo(personNumber,
				telephoneTableRecordKey);

		String query = RdbmsUtils.formatUpdateStatement(TableName,
				updateClauseInfo, whereClauseInfo);
		if (PersonTelephoneSynchronizer.isDebug())
			System.out
					.println("\nPersonTelephoneSynchronizer.modifyPersonTelephoneTableRecord() query: "
							+ query);
		boolean status = RdbmsUtils.getGriidcDbConnectionInstance()
				.executeQueryBoolean(query);
		return ;
	}

	private void addPersonTelephoneTableRecord(int personNumber,
			int telephoneTableRecordKey, String telephoneNumberExtension,
			String phoneType) throws SQLException {

		DbColumnInfo[] insertClauseInfo = getInsertClauseInfo(personNumber,
				telephoneTableRecordKey, telephoneNumberExtension, phoneType);
		String query = RdbmsUtils.formatInsertStatement(TableName,
				insertClauseInfo);
		if (PersonTelephoneSynchronizer.isDebug())
			System.out
					.println("\nPersonTelephoneSynchronizer.addPersonTelephoneTableRecord() query: "
							+ query);
		RdbmsUtils.getGriidcDbConnectionInstance()
				.executeQueryBoolean(query);
		return;
	}

	private DbColumnInfo[] getInsertClauseInfo(int personNumber,
			int telephoneTableRecordKey, String telephoneNumberExtension,
			String phoneType) throws SQLException {

		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), TableName);

		tci.getDbColumnInfo(PersonNumberColName).setColValue(
				String.valueOf(personNumber));
		tci.getDbColumnInfo(TelephoneKeyColName).setColValue(
				String.valueOf(telephoneTableRecordKey));
		tci.getDbColumnInfo(TelephoneExtensionColName).setColValue(
				telephoneNumberExtension);
		tci.getDbColumnInfo(TelephoneTypeColName).setColValue(phoneType);

		return tci.getDbColumnInfo();
	}

	private DbColumnInfo[] getUpdateClauseInfo(String extension,
			String telephoneType) throws SQLException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), TableName);

		DbColumnInfo dbciTemp1 = tci.getDbColumnInfo(TelephoneExtensionColName);
		dbciTemp1.setColValue(String.valueOf(extension));

		DbColumnInfo dbciTemp2 = tci.getDbColumnInfo(TelephoneTypeColName);
		dbciTemp2.setColValue(String.valueOf(telephoneType));

		DbColumnInfo[] info = { dbciTemp1, dbciTemp2 };
		return info;
	}

	private DbColumnInfo[] getWhereClauseInfo(int personNumber,
			int telephoneTableRecordKey) throws SQLException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), TableName);

		DbColumnInfo dbciTemp1 = tci.getDbColumnInfo(PersonNumberColName);
		dbciTemp1.setColValue(String.valueOf(personNumber));

		DbColumnInfo dbciTemp2 = tci.getDbColumnInfo(TelephoneKeyColName);
		dbciTemp2.setColValue(String.valueOf(telephoneTableRecordKey));

		DbColumnInfo[] info = { dbciTemp1, dbciTemp2 };
		return info;
	}

	private boolean findPersonTelephoneTableRecord(int targetPersonNumber,int targetTelephoneTableRecordKey)
			throws SQLException {
		int count = 0;
		ResultSet rs = null;

		String query = null;
		String msg = "Fatal error in PersonTelephoneSynchronizer - table name "
				+ TableName + " ";

		
		DbColumnInfo[] whereColInfo = getWhereClauseInfo(targetPersonNumber,
				targetTelephoneTableRecordKey);
		query = RdbmsUtils.formatSelectStatement(TableName, whereColInfo);

		rs = RdbmsUtils.getGriidcDbConnectionInstance().executeQueryResultSet(
				query);
		while (rs.next()) {
			count++;
			this.griidcPersonNumber = rs.getInt(PersonNumberColName);
			this.griidcTelephoneKey = rs.getInt(TelephoneKeyColName);
			this.griidcTelephoneExt = rs.getString(TelephoneExtensionColName);
			this.griidcTelephoneType = rs.getString(TelephoneTypeColName);
		}
		if(count > 0) return true;
		return false;
	}

	/**
	 * At this point The Person Number and Telephone Table Record Key match a 
	 * record found in the database. Do the details match?
	 * If the type is null or blank then we presume it is the default type of primary
	 * @param personNumber
	 * @param telephoneTableRecordKey
	 * @param telephoneNumberExtension
	 * @param phoneType
	 * @return
	 */
	private boolean isExactMatch(
			String telephoneNumberExtension,
			String phoneType) {
		// presume that person number and telephone key are equal
		return (extensionsMatch(telephoneNumberExtension) && phoneTypesMatch(phoneType));
	}
	public boolean extensionsMatch(String ext) {
		if(MiscUtils.isStringEmpty(ext) && MiscUtils.isStringEmpty(this.griidcTelephoneExt)) return true;
		if(MiscUtils.logicalXOR(MiscUtils.isStringEmpty(ext), MiscUtils.isStringEmpty(this.griidcTelephoneExt))) {
			// only one is empty
			return false;
		}
		return ext.equals(this.griidcTelephoneExt);
	}
	
	public boolean phoneTypesMatch(String type) {
		if(MiscUtils.isStringEmpty(type) && MiscUtils.isStringEmpty(this.griidcTelephoneType)) return true;
		if(MiscUtils.logicalXOR(MiscUtils.isStringEmpty(type), MiscUtils.isStringEmpty(this.griidcTelephoneType))) {
			// only one is empty
			return false;
		}
		return type.equals(this.griidcTelephoneType);
	}
	public static String getDefaultValueForTelephoneTypeColName() {
		DefaultValue dv = null;
		String msg = null;
		try {
			TableColInfo tci = RdbmsUtils.createTableColInfo(
					RdbmsUtils.getGriidcDbConnectionInstance(), TableName);
			dv = tci.getDbColumnInfo(TelephoneTypeColName).getDefaultValue();
			return dv.getPrettyStringValue();
		} catch (Exception e) {
			msg = "PersonTelephoneSynchronizer.getDefaultValueForTelephoneTypeColName() fatal error: "
					+ e.getMessage();
			e.printStackTrace();
			System.err.println(msg);
			try {
				MiscUtils.writeToPrimaryLogFile(msg);
				System.exit(-1);
			} catch (Exception e1) {
				e1.printStackTrace();
				System.exit(-1);
			}
		}
		return null;
	}

	public int getGriidcPersonTelephoneRecordsAdded() {
		return griidcPersonTelephoneRecordsAdded;
	}

	public int getGriidcPersonTelephoneRecordsModified() {
		return griidcPersonTelephoneRecordsModified;
	}

	public int getGriidcPersonTelephoneRecordDuplicates() {
		return griidcPersonTelephoneRecordDuplicates;
	}

	public int getRisPersonTelephoneRecords() {
		return risPersonTelephoneRecords;
	}

	public int getRisPersonTelephoneErrors() {
		return risPersonTelephoneErrors;
	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean deBug) {
		Debug = deBug;
	}
}
