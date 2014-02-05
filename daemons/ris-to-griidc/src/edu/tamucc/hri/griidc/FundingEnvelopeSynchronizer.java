package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import org.postgresql.util.PSQLException;

import edu.tamucc.hri.griidc.exception.DuplicateRecordException;
import edu.tamucc.hri.griidc.exception.IllegalFundingSourceCodeException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.rdbms.utils.DbColumnInfo;
import edu.tamucc.hri.rdbms.utils.DefaultValue;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;
import edu.tamucc.hri.rdbms.utils.RisFundSrcProgramsStartEndCollection;
import edu.tamucc.hri.rdbms.utils.RisProgramStartEnd;
import edu.tamucc.hri.rdbms.utils.TableColInfo;
import edu.tamucc.hri.rdbms.utils.TableColInfoCollection;

public class FundingEnvelopeSynchronizer {

	public FundingEnvelopeSynchronizer() {
		// TODO Auto-generated constructor stub
	}

	private static final String RisTableName = "FundingSource";
	private static final String GriidcTableName = "FundingEnvelope";

	private RdbmsConnection risDbConnection = null;
	private RdbmsConnection griidcDbConnection = null;

	private int risRecordCount = 0;
	private int risRecordsSkipped = 0;
	private int risRecordErrors = 0;
	private int griidcRecordsAdded = 0;
	private int griidcRecordsModified = 0;
	private int griidcRecordDuplicates = 0;

	private String risFundSource = null;
	private int risFundId = -1;
	private String risFundName = null;

	private static final String GriidcFundingOrganizationTableName = "FundingOrganization";
	private TableColInfoCollection griidcFundingOrganizationColInfo = null;
	private String defaultFundingOrganizationNumber = null;
	private String defaultFundingOrganizationDescription = null;
	private String defaultFundingOrganizationName = null;

	// FundingOrganization table in GRIIDC column names
	private static String GriidcFundingOrganization_NumberColName = "FundingOrganization_Number";
	private static String GriidcFundingOrganization_DescriptionColName = "FundingOrganization_Description";
	private static String GriidcFundingOrganization_NameColName = "FundingOrganization_Name";

	// GRIIDC FundingEnvelope values
	private String griidcFundingEnvelopeCycle = null;
	private String griidcFundingEnvelopeName = null;
	// private int griidcFundingOrganization_Number =
	private String griidcFundingEnvelopeDescription = null;
	private java.sql.Date griidcFundingEnvelopeStartDate = null;
	private java.sql.Date griidcFundingEnvelopeEndDate = null;

	// in GRIIDC FundingEnvelope table column names
	private static String GriidcFundingEnvelope_CycleColName = "FundingEnvelope_Cycle";
	private static String GriidcFundingEnvelope_FundingOrganization_Number = GriidcFundingOrganization_NumberColName;
	private static String GriidcFundingEnvelope_NameColName = "FundingEnvelope_Name";
	private static String GriidcFundingEnvelope_DescriptionColName = "FundingEnvelope_Description";
	private static String GriidcFundingEnvelope_EndDateColName = "FundingEnvelope_EndDate";
	private static String GriidcFundingEnvelope_StartDateColName = "FundingEnvelope_StartDate";

	private ResultSet rset = null;
	private ResultSet griidcRset = null;

	private static boolean Debug = false;
	private boolean initialized = false;

	private RisFundSrcProgramsStartEndCollection startEndDatePrograms = null;

	/**
	 * this.risFundId this.risFundSource this.risFundName cycle
	 * defaultFundingOrganizationNumber Name
	 */

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
			this.initializeFundingOrganizationData();
			this.startEndDatePrograms = RdbmsUtils
					.getRisFundSrcProgramsStartEndCollection();
			initialized = true;
		}
	}

	// get the FundingOrganization info - should be only one - GOMRI

	private void initializeFundingOrganizationData()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			TableNotInDatabaseException, PropertyNotFoundException {
		if (this.defaultFundingOrganizationNumber == null
				|| this.defaultFundingOrganizationDescription == null
				|| this.defaultFundingOrganizationName == null) {

			this.griidcFundingOrganizationColInfo = RdbmsUtils
					.getAllDataFromTable(this.griidcDbConnection,
							GriidcFundingOrganizationTableName);
			TableColInfo[] tciArray = this.griidcFundingOrganizationColInfo
					.getTableColInfoArray();

			for (TableColInfo tci : tciArray) {
				this.defaultFundingOrganizationNumber = tci.getDbColumnInfo(
						GriidcFundingOrganization_NumberColName).getColValue();
				this.defaultFundingOrganizationDescription = tci
						.getDbColumnInfo(
								GriidcFundingOrganization_DescriptionColName)
						.getColValue();
				this.defaultFundingOrganizationName = tci.getDbColumnInfo(
						GriidcFundingOrganization_NameColName).getColValue();
			}
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
	public void syncGriidcFundingEnvelopeFromRisFundingSource()
			throws ClassNotFoundException, PropertyNotFoundException,
			IOException, SQLException, TableNotInDatabaseException {
		if (FundingEnvelopeSynchronizer.isDebug())
			System.out.println(MiscUtils.BreakLine);

		this.initializeStartUp();

		// get all records from the RIS FundingEnvelope table
		try {
			rset = this.risDbConnection.selectAllValuesFromTable(RisTableName);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		try {
			/**
			 * funEnvCycle = -1; funEnvFundingOrg = -1; funEnvName = null;
			 * funEnvStartDate = null; funEnvEndDate
			 */
			while (rset.next()) { // continue statements branch back to here
				this.risRecordCount++;
				this.risFundId = rset.getInt("Fund_ID");
				this.risFundSource = rset.getString("Fund_Source").trim();
				this.risFundName = rset.getString("Fund_Name").trim();

				String msg = "\nRead RIS table: " + RisTableName
						+ ", Fund_ID: " + this.risFundId + ", Fund_Source: "
						+ this.risFundSource + ", Fund_Name : "
						+ this.risFundName;
				if (FundingEnvelopeSynchronizer.isDebug())
					System.out.println(msg);
				try {
					this.griidcFundingEnvelopeCycle = RdbmsUtils
							.convertRisFundingSourceToGriidcFormat(this.risFundSource);
					MiscUtils.getProjectNumberFundingCycleCache().setValue(
							this.risFundId, this.griidcFundingEnvelopeCycle);
					if (FundingEnvelopeSynchronizer.isDebug())
						System.out
								.println(MiscUtils
										.getProjectNumberFundingCycleCache()
										.toString());
				} catch (IllegalFundingSourceCodeException e2) {
					msg = "In RIS table " + RisTableName + " - "
							+ e2.getMessage();
					MiscUtils.writeToRisErrorLogFile(msg);
					if (FundingEnvelopeSynchronizer.isDebug())
						System.err.println(msg);
					this.risRecordErrors++;
					continue; // back to next RIS record from resultSet
				}

				String query = formatGriidcFindQuery();
				if (FundingEnvelopeSynchronizer.isDebug())
					System.out.println("formatGriidcFindQuery() " + query);
				try {
					griidcRset = this.griidcDbConnection
							.executeQueryResultSet(query);

				} catch (SQLException e1) {
					System.err
							.println("SQL Error: Find FundingEnvelope in GRIIDC - Query: "
									+ query);
					e1.printStackTrace();
				}

				int count = 0;

				// find the corresponding GRIIDC record(s)
				try {
					while (griidcRset.next()) {
						count++;
						this.griidcFundingEnvelopeCycle = griidcRset
								.getString(GriidcFundingEnvelope_CycleColName);

						this.griidcFundingEnvelopeName = griidcRset
								.getString(GriidcFundingOrganization_NumberColName);
						if (FundingEnvelopeSynchronizer.isDebug())
							System.out.println("Found " + count + " GRIIDC "
									+ GriidcFundingEnvelope_CycleColName + ": "
									+ this.griidcFundingEnvelopeCycle + ", "
									+ GriidcFundingOrganization_NumberColName
									+ ": " + this.griidcFundingEnvelopeName);
					}

				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}

				// are there matching GRIIDC records?
				// zero records found means ADD this record
				// one record found means UPDATE
				// more than ONE record found.. maybe an error???
				if (count == 0) { // Add the FundingEnvelope
					try {
						this.griidcFundingEnvelopeName = this.risFundName;
						this.griidcFundingEnvelopeCycle = RdbmsUtils
								.convertRisFundingSourceToGriidcFormat(this.risFundSource);
						RisProgramStartEnd rfspsec = this.startEndDatePrograms
								.getFundSourceStartEndDate(this.risFundId);
						this.griidcFundingEnvelopeStartDate = rfspsec
								.getStartDate();
						this.griidcFundingEnvelopeEndDate = rfspsec
								.getEndDate();
						this.addGriidcFundingEnvelopeRecord();
						this.griidcRecordsAdded++;
					} catch (SQLException e) {
						// TODO Auto-generated catch block
						msg = "Error adding GRIIDC FundingEnvelope record : "
								+ e.getMessage();
						if (FundingEnvelopeSynchronizer.isDebug())
							System.err.println(msg);
						MiscUtils.writeToPrimaryLogFile(msg);
						MiscUtils.writeToRisErrorLogFile(msg);
						this.risRecordErrors++;
						continue; // back to next RIS record from resultSet
					} catch (IllegalFundingSourceCodeException e) {
						msg = "In RIS FundingSource Table - Fund_ID "
								+ this.risFundId + " - " + e.getMessage();
						MiscUtils.writeToRisErrorLogFile(msg);
						if (FundingEnvelopeSynchronizer.isDebug())
							System.err.println(msg);
						this.risRecordErrors++;
						continue; // back to next RIS record from resultSet
					}

				} else if (count == 1) {
					try {
						if (!isCurrentRecordEqual()) {
							this.griidcFundingEnvelopeName = this.risFundName;
							this.griidcFundingEnvelopeCycle = RdbmsUtils
									.convertRisFundingSourceToGriidcFormat(this.risFundSource);
							RisProgramStartEnd rfspsec = this.startEndDatePrograms
									.getFundSourceStartEndDate(this.risFundId);
							this.griidcFundingEnvelopeStartDate = rfspsec
									.getStartDate();
							this.griidcFundingEnvelopeEndDate = rfspsec
									.getEndDate();
							this.modifyGriidcFundingEnvelopeRecord();

							this.griidcRecordsModified++;
						}
					} catch (IllegalFundingSourceCodeException e) {
						msg = "In RIS FundingSource Table - Fund_ID "
								+ this.risFundId + " - " + e.getMessage();
						MiscUtils.writeToRisErrorLogFile(msg);
						if (FundingEnvelopeSynchronizer.isDebug())
							System.err.println(msg);
						this.risRecordErrors++;
						continue; // back to next RIS record from resultSet
					}

				} else if (count > 1) { // duplicates
					this.griidcRecordDuplicates++;

					msg = "There are " + count + " records in the  GRIIDC "
							+ GriidcTableName + " table matching "
							+ GriidcFundingEnvelope_CycleColName + ": "
							+ this.risFundSource + ", FundingEnvelope_Name: "
							+ this.risFundName;
					if (FundingEnvelopeSynchronizer.isDebug())
						System.out.println(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
				}

			} // end of main while loop
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return;
		// end of FundingEnvelope
	}

	/**
	 * compare the current RIS record with the current GRIIDC record
	 *If all possible updateable values are the same return true. (NO update needed)
	 *else return false.
	 * @return
	 */
	private boolean isCurrentRecordEqual() {
		
	}
	private String griidcFundingEnvelopeToString() {

		return GriidcFundingEnvelope_CycleColName + ": "
				+ this.griidcFundingEnvelopeCycle + ", "
				+ GriidcFundingOrganization_NumberColName + ": "
				+ this.defaultFundingOrganizationNumber + ", "
				+ GriidcFundingEnvelope_NameColName + ": " + this.risFundName;
	}

	private DbColumnInfo[] getWhereColumnInfo() {
		DbColumnInfo dbci = new DbColumnInfo(
				GriidcFundingEnvelope_CycleColName, DbColumnInfo.DbCharacter,
				this.griidcFundingEnvelopeCycle);

		DbColumnInfo[] whereColInfo = new DbColumnInfo[1];
		whereColInfo[0] = dbci;
		return whereColInfo;
	}

	private String formatGriidcFindQuery() {
		return RdbmsUtils.formatSelectStatement(GriidcTableName,
				this.getWhereColumnInfo());
	}

	private void addGriidcFundingEnvelopeRecord() throws SQLException,
			ClassNotFoundException, IOException, PropertyNotFoundException {
		String msg = null;

		String addQuery = this.formatAddQuery();
		if (FundingEnvelopeSynchronizer.isDebug())
			System.out.println("Query: " + addQuery);
		this.griidcDbConnection.executeQueryBoolean(addQuery);
		msg = "Added GRIIDC " + GriidcTableName + ": "
				+ griidcFundingEnvelopeToString();
		MiscUtils.writeToPrimaryLogFile(msg);
		if (FundingEnvelopeSynchronizer.isDebug())
			System.out.println(msg);
		return;
	}

	private String formatAddQuery() {
		DbColumnInfo[] cdColInfo = getDbColumnInfo();
		return RdbmsUtils.formatInsertStatement(GriidcTableName, cdColInfo);

	}

	private void modifyGriidcFundingEnvelopeRecord()
			throws ClassNotFoundException, IOException,
			PropertyNotFoundException, SQLException {
		String msg = null;
		String modifyQuery = null;
		if (FundingEnvelopeSynchronizer.isDebug())
			System.out
					.println("FundingEnvelopeSynchronizer.modifyGriidcFundingEnvelopeRecord()");

		modifyQuery = RdbmsUtils.formatUpdateQuery(GriidcTableName,
				this.getDbColumnInfo(), this.getWhereColumnInfo());

		if (FundingEnvelopeSynchronizer.isDebug())
			System.out.println("Modify Query: " + modifyQuery);
		this.griidcDbConnection.executeQueryBoolean(modifyQuery);
		msg = "Modified GRIIDC " + GriidcTableName + ": "
				+ griidcFundingEnvelopeToString();
		MiscUtils.writeToPrimaryLogFile(msg);
		if (FundingEnvelopeSynchronizer.isDebug())
			System.out.println(msg);
		return;

	}

	private DbColumnInfo[] getDbColumnInfo() {

		String[] colName = {
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_CycleColName,
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_FundingOrganization_Number,
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_NameColName,
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_DescriptionColName,
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_StartDateColName,
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_EndDateColName, };

		String[] colType = { RdbmsUtils.DbCharacter, RdbmsUtils.DbInteger,
				RdbmsUtils.DbCharacter, RdbmsUtils.DbCharacter,
				RdbmsUtils.DbCharacter, RdbmsUtils.DbCharacter };

		String[] colValue = new String[colName.length];

		int ndx = 0;
		colValue[ndx++] = this.griidcFundingEnvelopeCycle;
		colValue[ndx++] = this.defaultFundingOrganizationNumber; // there is
																	// only one
																	// funding
																	// organization
																	// 2/4/2014
		colValue[ndx++] = this.griidcFundingEnvelopeName;
		colValue[ndx++] = this.griidcFundingEnvelopeDescription;
		colValue[ndx++] = this.griidcFundingEnvelopeStartDate.toString();
		colValue[ndx++] = this.griidcFundingEnvelopeEndDate.toString();

		DbColumnInfo[] info = new DbColumnInfo[colName.length];
		for (int i = 0; i < colName.length; i++) {
			info[i] = new DbColumnInfo(colName[i], colType[i], colValue[i]);
		}
		/***
		 * if(FundingEnvelopeSynchronizer.isDebug()) {
		 * System.out.println("FundingEnvelopeSynchronizer.getDbColumnInfo()");
		 * for(DbColumnInfo dbci : info) { System.out.println(dbci.toString());
		 * } }
		 ***/
		return info;
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
		return FundingEnvelopeSynchronizer.Debug;
	}

	public static void setDebug(boolean debug) {
		FundingEnvelopeSynchronizer.Debug = debug;
	}

	public void reportFundingEnvelopeTable() throws IOException,
			PropertyNotFoundException, SQLException, ClassNotFoundException,
			TableNotInDatabaseException {
		RdbmsUtils.reportTables(RisTableName, GriidcTableName);
		return;
	}

	public int getRisRecordCount() {
		return risRecordCount;
	}

	public int getRisRecordsSkipped() {
		return risRecordsSkipped;
	}

	public int getRisRecordErrors() {
		return risRecordErrors;
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
}
