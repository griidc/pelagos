package edu.tamucc.hri.griidc.pubs;

import java.io.ByteArrayOutputStream;
import java.io.PrintStream;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Set;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.RisPeopleGriidcPersonMap;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public class PersonPublicationDbAgent {

	private RisPeopleGriidcPersonMap risPeopleIdGriidcPersonNumMap = RisPeopleGriidcPersonMap
			.getInstance();

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

	private Set<Object> pubNumbers = null;
	private static final String ErrorMsgPrefix = "Error PerPub-";

	public PersonPublicationDbAgent() {
		// TODO Auto-generated constructor stub
	}

	private void initialize() throws SQLException {
		if (this.dbCon == null) {
			this.dbCon = RdbmsUtils.getGriidcDbConnectionInstance();
		}
	}

	private Set<Object> getPubNumbers() {
		if (this.pubNumbers == null) {
			try {
				initialize();
				this.pubNumbers = RdbmsUtils.getAllUniqueValuesFromTable(
						RdbmsUtils.getGriidcDbConnectionInstance(),
						"Publication", "RIS_Publication_Number");
				return this.pubNumbers;
			} catch (SQLException e) {
				String msg = "PersonPublicationDbAgent.updateGriidcPersonPublication()";
				System.err.println(msg + " - " + e.getMessage());
				System.exit(-1);
			}
		}
		return this.pubNumbers;
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
	public void updateGriidcPersonPublication(RisPeoplePub[] risPeoplePubs) {

		int griidcPersonNumber = -1;
		int pubNumber = -1;
		int risPeopleId = -1;
		String ppMsg = null;
		for (RisPeoplePub rpp : risPeoplePubs) {
			recordsRead++;
			risPeopleId = rpp.getPeopleId();
			pubNumber = rpp.getPubSerial();
			ppMsg = "Person-Publication " + risPeopleId + "-" + pubNumber + " ";

			this.debugMessage("\n*********** Processing pub # " + pubNumber
					+ " people ID: " + risPeopleId);

			try {
				griidcPersonNumber = risPeopleIdGriidcPersonNumMap
						.getPersonNumber(risPeopleId);
			} catch (NoRecordFoundException e) {
				String msg = ppMsg
						+ ": There is no GRIIDC Person with RIS PeopleId "
						+ risPeopleId;
				this.errorMessage(ErrorMsgPrefix + "1: " + msg);
				this.errors++;
				continue; // skip the rest - get the next one
			}

			if (isPubInGriidc(pubNumber)) {// does the publication
															// exist in the DB
				try {
					findGriidcPersonPublication(pubNumber, griidcPersonNumber);
					debugMessage("Found "
							+ formatPayload(pubNumber, griidcPersonNumber));
					String msg = ppMsg
							+ ": Duplicate Person-Publication record";
					this.errorMessage(ErrorMsgPrefix + "2: " + msg);
					this.duplicateRecords++;
					continue;
				} catch (NoRecordFoundException e) { // record not found - add
														// it
					try {
						addGriidcPersonPublication(pubNumber,
								griidcPersonNumber);
						debugMessage("Added "
								+ formatPayload(pubNumber, griidcPersonNumber));
						this.recordsAdded++;
					} catch (SQLException e1) {
						String msg = "PersonPublicationDbAgent.updateGriidcPersonPublication() Trying to add "
								+ ppMsg;
						System.err.println(msg);
						e1.printStackTrace();
						System.exit(-1);
						;
					}
				}
			} else { // the publication is NOT in the DB
				String msg = ppMsg
						+ "Did NOT find publication. Can't add Person-Publication if pub does not exist ";
				debugMessage(msg);
				this.errorMessage(ErrorMsgPrefix + "3: " + msg);
				continue;
			}
		}
		return;
	}

	private boolean isPubInGriidc(Integer risPubNumber) {
		this.getPubNumbers();
		return pubNumbers.contains(risPubNumber);
	}

	private boolean findGriidcPersonPublication(int pubNumber, int personNumber)
			throws NoRecordFoundException {
		this.queryString = formatFindQuery(pubNumber, personNumber);
		this.debugMessage("PersonPublicationDbAgent.findGriidcPersonPublication() \n"
				+ this.formatPayload(pubNumber, personNumber)
				+ "\n"
				+ "query: " + this.queryString);
		try {
			ResultSet rs = dbCon.executeQueryResultSet(this.queryString);
			int count = 0;
			// int personN = -1;
			// int pubN = -1;
			while (rs.next()) {
				// personN = rs.getInt(PersonNumberColName);
				// pubN = rs.getInt(PublicationNumberColName);
				count++;
			}
			if (count == 0) {
				throw new NoRecordFoundException(
						"No Person-Publication record found with person: "
								+ personNumber + ", Publication: " + pubNumber);
			}
		} catch (SQLException e) {
			throw new NoRecordFoundException(
					"No Person-Publication record found with person: "
							+ personNumber + ", Publication: " + pubNumber);
		}

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
		return RdbmsUtils.formatInsertStatement(TableName, info);
	}

	private DbColumnInfo[] getDbColumnInfo(int pubNumber, int griidcPersonNumber)
			throws SQLException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(dbCon, TableName);

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
		MiscUtils.writeToPubsErrorLogFile(msg);
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
