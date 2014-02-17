package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TelephoneNumberException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.rdbms.utils.DbColumnInfo;
import edu.tamucc.hri.rdbms.utils.DefaultValue;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;
import edu.tamucc.hri.rdbms.utils.TableColInfo;

public class PersonTelephoneSynchronizer {
	private static final String TableName = "Person-Telephone";
	private static final String PersonNumberColName = "Person_Number";
	private static final String TelephoneKeyColName = "Telephone_Key";
	private static final String TelephoneExtensionColName = "Telephone_Extension";
	private static final String TelephoneTypeColName = "Telephone_Type";

	private static final String[] ColNameArray = { PersonNumberColName,
			TelephoneKeyColName, TelephoneExtensionColName,
			TelephoneTypeColName };

	private static boolean Debug = false;
	private int griidcPersonTelephoneRecordsAdded = 0;
	private int griidcPersonTelephoneRecordsModified = 0;
	private int risPersonTelephoneRecords = 0;
	private int risPersonTelephoneErrors = 0;

	private static PersonTelephoneSynchronizer instance = null;

	private PersonTelephoneSynchronizer() {

	}

	public static PersonTelephoneSynchronizer getInstance() {
		if (PersonTelephoneSynchronizer.instance == null) {
			PersonTelephoneSynchronizer.instance = new PersonTelephoneSynchronizer();
		}
		return PersonTelephoneSynchronizer.instance;
	}

	/**
	 * @throws TelephoneNumberException
	 * 
	 */
	public boolean updatePersonTelephoneTable(int personNumber,
			int telephoneTableRecordKey, String telephoneNumberExtension,
			String phoneType) throws TelephoneNumberException {
		this.risPersonTelephoneRecords++;
		boolean status = false;
		String tempPhoneType = phoneType;
		if(phoneType == null || phoneType.length() == 0)
			tempPhoneType = PersonTelephoneSynchronizer.getDefaultValueForTelephoneTypeColName();
		try {
			boolean found = false;
			found = this.findPersonTelephoneTableRecord(personNumber,
					telephoneTableRecordKey, telephoneNumberExtension,
					tempPhoneType);
			if (found) { // modify

				status = this.modifyPersonTelephoneTableRecord(personNumber,
						telephoneTableRecordKey, telephoneNumberExtension,
						tempPhoneType);
				return status;
			} // else add the record
			status = this.addPersonTelephoneTableRecord(personNumber,
					telephoneTableRecordKey, telephoneNumberExtension,
					tempPhoneType);
			return status;

		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		this.risPersonTelephoneErrors++;
		
		return false;
	}

	private boolean modifyPersonTelephoneTableRecord(int personNumber,
			int telephoneTableRecordKey, String telephoneNumberExtension,
			String phoneType) throws FileNotFoundException, SQLException,
			ClassNotFoundException, PropertyNotFoundException,
			TelephoneNumberException {

		DbColumnInfo[] updateClauseInfo = getUpdateClauseInfo(
				telephoneNumberExtension, phoneType);

		DbColumnInfo[] whereClauseInfo = getWhereClauseInfo(personNumber,
				telephoneTableRecordKey);

		String query = RdbmsUtils.formatUpdateStatement(TableName,
				updateClauseInfo, whereClauseInfo);
		if (PersonTelephoneSynchronizer.isDebug())
			System.out
					.println("PersonTelephoneSynchronizer.modifyPersonTelephoneTableRecord() query: "
							+ query);
		boolean status = RdbmsUtils.getGriidcDbConnectionInstance()
				.executeQueryBoolean(query);
		this.griidcPersonTelephoneRecordsModified++;
		return status;
	}

	private boolean addPersonTelephoneTableRecord(int personNumber,
			int telephoneTableRecordKey, String telephoneNumberExtension,
			String phoneType) throws FileNotFoundException, SQLException,
			ClassNotFoundException, PropertyNotFoundException,
			TelephoneNumberException {
		boolean status = false;

		DbColumnInfo[] insertClauseInfo = getInsertClauseInfo(personNumber,
				telephoneTableRecordKey, telephoneNumberExtension, phoneType);
		String query = RdbmsUtils.formatInsertStatement(TableName,
				insertClauseInfo);
		if (PersonTelephoneSynchronizer.isDebug())
			System.out
					.println("PersonTelephoneSynchronizer.addPersonTelephoneTableRecord() query: "
							+ query);
		status = RdbmsUtils.getGriidcDbConnectionInstance()
				.executeQueryBoolean(query);
		this.griidcPersonTelephoneRecordsAdded++;
		return status;
	}

	private DbColumnInfo[] getInsertClauseInfo(int personNumber,
			int telephoneTableRecordKey, String telephoneNumberExtension,
			String phoneType) throws FileNotFoundException, SQLException,
			ClassNotFoundException, PropertyNotFoundException {

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
			String telephoneType) throws FileNotFoundException, SQLException,
			ClassNotFoundException, PropertyNotFoundException {
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
			int telephoneTableRecordKey) throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), TableName);

		DbColumnInfo dbciTemp1 = tci.getDbColumnInfo(PersonNumberColName);
		dbciTemp1.setColValue(String.valueOf(personNumber));

		DbColumnInfo dbciTemp2 = tci.getDbColumnInfo(TelephoneKeyColName);
		dbciTemp2.setColValue(String.valueOf(telephoneTableRecordKey));

		DbColumnInfo[] info = { dbciTemp1, dbciTemp2 };
		return info;
	}

	private boolean findPersonTelephoneTableRecord(int targetPersonNumber,
			int targetTelephoneTableRecordKey,
			String targetTelephoneNumberExtension, String targetPhoneType)
			throws PropertyNotFoundException, SQLException {
		ResultSet rs = null;
		
		String query = null;
		String msg = "Fatal error in PersonTelephoneSynchronizer - table name "
				+ TableName + " ";
		String tempExtension = null;
		String tempType = null;
		boolean fatalError = false;
		try {
			DbColumnInfo[] whereColInfo = getWhereClauseInfo(targetPersonNumber,
					targetTelephoneTableRecordKey);
			query = RdbmsUtils.formatSelectStatement(TableName, whereColInfo);

			rs = RdbmsUtils.getGriidcDbConnectionInstance()
					.executeQueryResultSet(query);
			while (rs.next()) {
				tempExtension = rs.getString(TelephoneExtensionColName);
				tempType = rs.getString(TelephoneTypeColName);
				if (targetTelephoneNumberExtension.equals(tempExtension)
						&& targetPhoneType.equals(tempType)) {
					if (TelephoneSynchronizer.isDebug()) {
						System.out.println("Found matching " + TableName
								+ " record: Person# " + targetPersonNumber
								+ ", Telephone# "
								+ targetTelephoneTableRecordKey);
					}
					return true;
				}
			}
		} catch (FileNotFoundException e) {
			fatalError = true;
			msg = msg + e.getMessage();
		} catch (ClassNotFoundException e) {
			fatalError = true;
			msg = msg + e.getMessage();
		}

		if (fatalError) {
			System.err.println(msg);
			try {
				MiscUtils.writeToPrimaryLogFile(msg);
				System.exit(-1);
			} catch (IOException e1) {
				e1.printStackTrace();
				System.exit(-1);
			}
		}
		return false;
	}
	public static String getDefaultValueForTelephoneTypeColName() {
		DefaultValue dv = null;
		String msg = null;
		try {
			TableColInfo tci = RdbmsUtils.createTableColInfo(RdbmsUtils.getGriidcDbConnectionInstance(), TableName);
			dv = tci.getDbColumnInfo(TelephoneTypeColName).getDefaultValue();
			return dv.getPrettyStringValue();
		} catch (Exception e) {
			msg = "PersonTelephoneSynchronizer.getDefaultValueForTelephoneTypeColName() fatal error: " + e.getMessage();
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
