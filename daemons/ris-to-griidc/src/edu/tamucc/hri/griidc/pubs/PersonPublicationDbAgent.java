package edu.tamucc.hri.griidc.pubs;

import java.io.ByteArrayOutputStream;
import java.io.PrintStream;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsPubsUtils;
import edu.tamucc.hri.griidc.rdbms.RisPeopleGriidcPersonMap;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public class PersonPublicationDbAgent {

	private RisPeopleGriidcPersonMap peoplePersonMap = RisPeopleGriidcPersonMap.getInstance();

	private static final String TableName = RdbmsConstants.GriidcPersonPublicationTableName;

	private static final String PersonNumberColName = RdbmsConstants.GriidcPersonPublication_PersonNumber_ColName;
	private static final String PublicationNumberColName = RdbmsConstants.GriidcPersonPublication_PublicationNumber_ColName;
	private RdbmsConnection dbCon = null;

	private int recordsRead = 0;
	private int recordsAdded = 0;
	private int duplicateRecords = 0;
	private int errors = 0;

	public static boolean DeBug = false;

	private String queryString = null;

	public PersonPublicationDbAgent() {
		// TODO Auto-generated constructor stub
	}

	private void initialize() throws SQLException {
		if (this.dbCon == null) {
			this.dbCon = RdbmsPubsUtils.getGriidcDbConnectionInstance();
		}
	}

	/**
	 * 
	 * risPeoplePubs is an array of all PeoplePublication records from the RIS
	 * database. for each record ... if it exists in GRIIDC and is equal (
	 * existence is equality since there are only two fields and they are the
	 * concatenated key) do nothing. if it does not exist in GRIIDC add it
	 * 
	 * @param risPeoplePubs
	 * @return
	 */
	public boolean updateGriidcPersonPublication(RisPeoplePub[] risPeoplePubs) {

		int griidcPersonNumber = -1;
		int pubNumber = -1;
		int risProgramId = -1;
		int risPeopleId = -1;
		for (RisPeoplePub rpp : risPeoplePubs) {
			recordsRead++;
			risPeopleId = rpp.getPeopleId();
			risProgramId = rpp.getProgramId();
			pubNumber = rpp.getPubSerial();
			try {
				this.debugMessage("\n*********** Processing pub # " + pubNumber + " people ID: " +  risPeopleId);

				griidcPersonNumber = peoplePersonMap
						.getPersonNumber(risPeopleId);
						
				if (findGriidcPersonPublication(pubNumber, griidcPersonNumber)) {
					debugMessage("Found "
							+ formatPayload(pubNumber, griidcPersonNumber));
					this.duplicateRecords++;
				} else {
					addGriidcPersonPublication(pubNumber, griidcPersonNumber);
					debugMessage("Added "
							+ formatPayload(pubNumber, griidcPersonNumber));
					this.recordsAdded++;
				}
			} catch (SQLException e) {
				String msg = "PersonPublicationDbAgent.updateGriidcPersonPublication() SQL error \n"
						+ "query: " + this.queryString;
				errorMessage(msg + "\n" + e.getMessage());
				this.errors++;
			} catch (NoRecordFoundException e) {
				String msg = "PersonPublicationDbAgent: There is no GRIIDC Person corresponding to RIS PeopleId "
						+ risPeopleId;
				this.errorMessage(msg);
				this.errors++;
			}
		}
		return false;
	}

	private boolean findGriidcPersonPublication(int pubNumber, int personNumber)
			throws SQLException {
		initialize();
		this.queryString = formatFindQuery(pubNumber, personNumber);
		this.debugMessage("PersonPublicationDbAgent.findGriidcPersonPublication() \n"
				+ this.formatPayload(pubNumber, personNumber)
				+ "\n"
				+ "query: " + this.queryString);
		ResultSet rs = dbCon.executeQueryResultSet(this.queryString);
		int count = 0;
		// int personN = -1;
		// int pubN = -1;
		while (rs.next()) {
			// personN = rs.getInt(PersonNumberColName);
			// pubN = rs.getInt(PublicationNumberColName);
			count++;
		}
		if (count == 0)
			return false;
		return true;
	}

	private String formatFindQuery(int pubNumber, int personNumber) {
		return "SELECT * FROM " + RdbmsConnection.wrapInDoubleQuotes(TableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(PersonNumberColName)
				+ RdbmsConstants.EqualSign + personNumber + RdbmsConstants.And
				+ RdbmsConnection.wrapInDoubleQuotes(PublicationNumberColName)
				+ RdbmsConstants.EqualSign + pubNumber;
	}

	private boolean addGriidcPersonPublication(int pubNumber,
			int griidcPersonNumber) throws SQLException {
		this.queryString = formatAddQuery(pubNumber, griidcPersonNumber);
		return dbCon.executeQueryBoolean(this.queryString);
	}

	private String formatAddQuery(int pubNumber, int griidcPersonNumber)
			throws SQLException {

		DbColumnInfo[] info = getDbColumnInfo(pubNumber, griidcPersonNumber);
		return RdbmsPubsUtils.formatInsertStatement(TableName, info);
	}

	private DbColumnInfo[] getDbColumnInfo(int pubNumber, int griidcPersonNumber)
			throws SQLException {
		TableColInfo tci = RdbmsPubsUtils.getMetaDataForTable(dbCon, TableName);

		tci.getDbColumnInfo(PersonNumberColName).setColValue(
				String.valueOf(griidcPersonNumber));
		tci.getDbColumnInfo(PublicationNumberColName).setColValue(
				String.valueOf(pubNumber));
		return tci.getDbColumnInfo();
	}

	public int getPersonPubsRead() {
		return recordsRead;
	}
	public int getRecordsAdded() {
		return recordsAdded;
	}

	public int getDuplicateRecords() {
		return duplicateRecords;
	}
	
	public int getErrors() {
		return this.errors;
	}

	private void errorMessage(String msg) {
		MiscUtils.writeToErrorLogFile(msg);
		debugMessage(msg);
	}

	private void debugMessage(String msg) {
		if (isDeBug())
			System.out.println(msg);
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	private String formatPayload(int pubNumber, int personNumber) {
		String format = "%nPerson-Publication: Publication Number %5d, Person Number: %5d";
		StringBuffer sb = new StringBuffer();
		ByteArrayOutputStream outStream = new ByteArrayOutputStream();
		PrintStream ps = new PrintStream(outStream);
		ps.printf(format, pubNumber, personNumber);
		return outStream.toString();
	}

}
