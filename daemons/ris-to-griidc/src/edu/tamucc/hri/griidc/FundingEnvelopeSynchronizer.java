package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.IllegalFundingSourceCodeException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.griidc.support.RisToGriidcConfiguration;
import edu.tamucc.hri.rdbms.utils.DbColumnInfo;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;
import edu.tamucc.hri.rdbms.utils.RisFundSrcProgramsStartEndCollection;
import edu.tamucc.hri.rdbms.utils.RisProgramStartEnd;
import edu.tamucc.hri.rdbms.utils.TableColInfo;
import edu.tamucc.hri.rdbms.utils.TableColInfoCollection;


public class FundingEnvelopeSynchronizer extends SynchronizerBase {

	public FundingEnvelopeSynchronizer() {
		// TODO Auto-generated constructor stub
	}

	private static final String RisTableName = "FundingSource";
	private static final String GriidcTableName = "FundingEnvelope";

	private int risRecordCount = 0;
	private int risRecordsSkipped = 0;
	private int risRecordErrors = 0;
	private int griidcRecordsAdded = 0;
	private int griidcRecordsModified = 0;
	private int griidcRecordDuplicates = 0;

	private static String RisFundSourceColName = "Fund_Source";
	private static String RisFundIdColName = "Fund_ID";
	private static String RisFundNameColName = "Fund_Name";

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
	private int griidcFundingEnvelopeOrganizationNumber = -1;
	private String griidcFundingEnvelopeDescription = null;
	private java.sql.Date griidcFundingEnvelopeStartDate = null;
	private java.sql.Date griidcFundingEnvelopeEndDate = null;

	// in GRIIDC FundingEnvelope table column names
	private static String GriidcFundingEnvelope_CycleColName = "FundingEnvelope_Cycle";
	private static String GriidcFundingEnvelope_FundingOrganization_NumberColName = GriidcFundingOrganization_NumberColName;
	private static String GriidcFundingEnvelope_NameColName = "FundingEnvelope_Name";
	private static String GriidcFundingEnvelope_DescriptionColName = "FundingEnvelope_Description";
	private static String GriidcFundingEnvelope_EndDateColName = "FundingEnvelope_EndDate";
	private static String GriidcFundingEnvelope_StartDateColName = "FundingEnvelope_StartDate";

	private ResultSet rset = null;
	private ResultSet griidcRset = null;

	private static boolean Debug = false;
	private boolean initialized = false;

	private RisFundSrcProgramsStartEndCollection programStartEndDateCollection = null;

	/**
	 * this.risFundId this.risFundSource this.risFundName cycle
	 * defaultFundingOrganizationNumber Name
	 */

	public boolean isInitialized() {
		return initialized;
	}

	public void initialize() {
		super.commonInitialize();
		if (!isInitialized()) {
			this.initializeFundingOrganizationData();
			try {
				this.programStartEndDateCollection = RdbmsUtils
						.getRisFundSrcProgramsStartEndCollection();
			} catch (SQLException e) {
				MiscUtils.fatalError(this.getClass().getName(), "initialize",
						e.getMessage());
			}
			initialized = true;
		}
	}

	// get the FundingOrganization info - should be only one - GOMRI

	private void initializeFundingOrganizationData() {
		if (this.defaultFundingOrganizationNumber == null
				|| this.defaultFundingOrganizationDescription == null
				|| this.defaultFundingOrganizationName == null) {

			try {
				this.griidcFundingOrganizationColInfo = RdbmsUtils
						.getAllDataFromTable(this.griidcDbConnection,
								GriidcFundingOrganizationTableName);
			} catch (SQLException e) {
				MiscUtils.fatalError("FundingEnvelopeSynchronizer",
						"initializeFundingOrganizationData", e.getMessage());
			} catch (TableNotInDatabaseException e) {
				MiscUtils.fatalError("FundingEnvelopeSynchronizer",
						"initializeFundingOrganizationData", e.getMessage());
			}
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
	 * @throws MultipleRecordsFoundException
	 */
	public void syncGriidcFundingEnvelopeFromRisFundingSource()
			throws ClassNotFoundException, PropertyNotFoundException,
			IOException, SQLException, TableNotInDatabaseException {
		if (FundingEnvelopeSynchronizer.isDebug())
			System.out.println(MiscUtils.BreakLine);

		this.initialize();

		// get all records from the RIS FundingEnvelope table
		try {
			rset = this.risDbConnection.selectAllValuesFromTable(RisTableName);

			/**
			 * funEnvCycle = -1; funEnvFundingOrg = -1; funEnvName = null;
			 * funEnvStartDate = null; funEnvEndDate
			 */
			while (rset.next()) {
				this.risRecordCount++;
				this.risFundId = rset.getInt("Fund_ID");
				this.risFundSource = rset.getString("Fund_Source").trim();
				this.risFundName = rset.getString("Fund_Name").trim();

				String msg = "Read RIS Fund record - " + this.risFundToString();

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

				String query = RdbmsUtils.formatSelectStatement(
						GriidcTableName, this.getWhereColumnInfo());
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
								.getString(GriidcFundingEnvelope_NameColName);
						this.griidcFundingEnvelopeOrganizationNumber = griidcRset
								.getInt(GriidcFundingEnvelope_FundingOrganization_NumberColName);
						this.griidcFundingEnvelopeDescription = griidcRset
								.getString(GriidcFundingEnvelope_DescriptionColName);
						this.griidcFundingEnvelopeStartDate = griidcRset
								.getDate(GriidcFundingEnvelope_StartDateColName);
						this.griidcFundingEnvelopeEndDate = griidcRset
								.getDate(GriidcFundingEnvelope_EndDateColName);
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
						convertRisToGriidcData();
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
					} catch (IllegalFundingSourceCodeException e) {
						msg = "In RIS FundingSource Table - Fund_ID "
								+ this.risFundId + " - " + e.getMessage();
						MiscUtils.writeToRisErrorLogFile(msg);
						if (FundingEnvelopeSynchronizer.isDebug())
							System.err.println(msg);
						this.risRecordErrors++;
					}

				} else if (count == 1) {

					try {
						if (isCurrentRecordEqual()) {
							this.griidcRecordDuplicates++;
						} else {
							// if not equal then modify the record to match info
							// in RIS
							convertRisToGriidcData();
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
					}

				} else if (count > 1) { // duplicates

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

	private void convertRisToGriidcData()
			throws IllegalFundingSourceCodeException {
		this.griidcFundingEnvelopeName = this.risFundName;
		this.griidcFundingEnvelopeCycle = RdbmsUtils
				.convertRisFundingSourceToGriidcFormat(this.risFundSource);
		RisProgramStartEnd rfspsec = this.programStartEndDateCollection
				.getFundSourceStartEndDate(this.risFundId);
		this.griidcFundingEnvelopeStartDate = rfspsec.getStartDate();
		this.griidcFundingEnvelopeEndDate = rfspsec.getEndDate();
	}

	/**
	 * compare the current RIS record with the current GRIIDC record If all
	 * possible updateable values are the same return true. (NO update needed)
	 * else return false.
	 * 
	 * @return
	 * @throws IllegalFundingSourceCodeException
	 */
	String format = "%nCompare - %6s name: %-10s cycle: %-5s, start: %-10s, end: %-10s";

	private boolean isCurrentRecordEqual() {

		try {
			String tempRisDerrivedFundingEnvelopeCycle  = RdbmsUtils.convertRisFundingSourceToGriidcFormat(this.risFundSource);
			RisProgramStartEnd rfspsec = this.programStartEndDateCollection.getFundSourceStartEndDate(this.risFundId);


			boolean status =  (this.griidcFundingEnvelopeName.equals(this.risFundName)
					&& this.griidcFundingEnvelopeCycle.equals(tempRisDerrivedFundingEnvelopeCycle)
					&& this.griidcFundingEnvelopeStartDate.equals(rfspsec.getStartDate()) 
					&& this.griidcFundingEnvelopeEndDate.equals(rfspsec.getEndDate()));

			if (FundingEnvelopeSynchronizer.isDebug()) {
				System.out.printf(format, "RIS",this.risFundName,
						tempRisDerrivedFundingEnvelopeCycle,
						rfspsec.getStartDate(), rfspsec.getEndDate());
				System.out.printf(format, "GRIIDC",this.griidcFundingEnvelopeName,
						this.griidcFundingEnvelopeCycle,
						this.griidcFundingEnvelopeStartDate,
						this.griidcFundingEnvelopeEndDate);
				System.out.printf("%nEqual???  - %b", status);
				System.out.println("\n");
			}
			return status;
		} catch (IllegalFundingSourceCodeException e) {
			return false;
		}
	}

	private String griidcFundingEnvelopeToString() {

		return GriidcFundingEnvelope_CycleColName + ": "
				+ this.griidcFundingEnvelopeCycle + ", "
				+ GriidcFundingOrganization_NumberColName + ": "
				+ this.defaultFundingOrganizationNumber + ", "
				+ GriidcFundingEnvelope_NameColName + ": " + this.risFundName;
	}

	private String risFundToString() {

		return RisFundIdColName + ": " + this.risFundId + ", "
				+ RisFundSourceColName + ": " + this.risFundSource + ", "
				+ RisFundNameColName + ": " + this.risFundName;
	}

	private DbColumnInfo[] getWhereColumnInfo() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);
		tci.getDbColumnInfo(GriidcFundingEnvelope_CycleColName).setColValue(
				this.griidcFundingEnvelopeCycle);

		DbColumnInfo[] whereColInfo = new DbColumnInfo[1];
		whereColInfo[0] = tci
				.getDbColumnInfo(GriidcFundingEnvelope_CycleColName);
		return whereColInfo;
	}

	private void addGriidcFundingEnvelopeRecord() throws SQLException,
			ClassNotFoundException, IOException, PropertyNotFoundException {
		String msg = null;

		String addQuery = RdbmsUtils.formatInsertStatement(GriidcTableName,
				this.getDbColumnInfo());
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

	private void modifyGriidcFundingEnvelopeRecord()
			throws ClassNotFoundException, IOException,
			PropertyNotFoundException, SQLException {
		String msg = null;
		String modifyQuery = null;
		if (FundingEnvelopeSynchronizer.isDebug())
			System.out
					.println("FundingEnvelopeSynchronizer.modifyGriidcFundingEnvelopeRecord()");

		modifyQuery = RdbmsUtils.formatUpdateStatement(GriidcTableName,
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

	private DbColumnInfo[] getDbColumnInfo() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);

		tci.getDbColumnInfo(
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_CycleColName)
				.setColValue(this.griidcFundingEnvelopeCycle);
		tci.getDbColumnInfo(
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_FundingOrganization_NumberColName)
				.setColValue(
						String.valueOf(this.defaultFundingOrganizationNumber));
		tci.getDbColumnInfo(
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_NameColName)
				.setColValue(this.griidcFundingEnvelopeName);
		tci.getDbColumnInfo(
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_DescriptionColName)
				.setColValue(this.griidcFundingEnvelopeDescription);
		tci.getDbColumnInfo(
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_StartDateColName)
				.setColValue(this.griidcFundingEnvelopeStartDate.toString());
		tci.getDbColumnInfo(
				FundingEnvelopeSynchronizer.GriidcFundingEnvelope_EndDateColName)
				.setColValue(this.griidcFundingEnvelopeEndDate.toString());
		return tci.getDbColumnInfo();
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
