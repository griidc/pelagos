package edu.tamucc.hri.griidc.ris;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.MultipleRecordsFoundException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.RisFundSrcProgramsStartEndCollection;
import edu.tamucc.hri.griidc.rdbms.RisProgramStartEnd;
import edu.tamucc.hri.griidc.rdbms.SynchronizerBase;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.utils.MiscUtils;
import edu.tamucc.hri.griidc.utils.GriidcConfiguration;

/**
 * read the RIS Programs Table and create the GRIIDC Project table
 * 
 * @author jvh
 * 
 */
public class ProjectSynchronizer extends SynchronizerBase {

	public ProjectSynchronizer() {
		// TODO Auto-generated constructor stub
	}

	private static final String RisTableName = "Programs";
	private static final String GriidcTableName = "Project";
	private int risRecordCount = 0;
	private int risRecordsSkipped = 0;
	private int risRecordErrors = 0;
	private int griidcRecordsAdded = 0;
	private int griidcRecordsModified = 0;
	private int griidcRecordDuplicates = 0;

	// RIS Programs fields
	private int risProgram_ID = -1;
	private String risProgram_Title = null;
	private int risProgram_FundSrc = -1;
	private int risProgram_LeadInstitution = -1;
	private int risProgram_SubTasks = -1;
	private java.sql.Date risProgram_StartDate = null;
	private java.sql.Date risProgram_EndDate = null;
	private String risProgram_Abstract = null;

	// RIS Programs column names
	private static String RisProgram_ID_ColName = "Program_ID";
	private static String RisProgram_Title_ColName = "Program_Title";
	private static String RisProgram_FundSrc_ColName = "Program_FundSrc";
	private static String RisProgram_LeadInstitution_ColName = "Program_LeadInstitution";
	private static String RisProgram_SubTasks_ColName = "Program_SubTasks";
	private static String RisProgram_StartDate_ColName = "Program_StartDate";
	private static String RisProgram_EndDate_ColName = "Program_EndDate";
	private static String RisProgram_Abstract_ColName = "Program_Abstract";

	// GRIIDC Project fields
	private int griidcProject_Number = -1;
	private String griidcFundingEnvelope_Cycle = null;
	private String griidcProject_Abstract = null;
	private java.sql.Date griidcProject_EndDate = null; // was java.sql.Date
	private java.sql.Date griidcProject_StartDate = null; // was java.sql.Date
	private String griidcProject_Title = null;

	// GRIIDC column Names
	private static String GriidcProject_Number_ColName = "Project_Number";
	private static String GriidcFundingEnvelope_Cycle_ColName = "FundingEnvelope_Cycle";
	private static String GriidcProject_Abstract_ColName = "Project_Abstract";
	private static String GriidcProject_EndDate_ColName = "Project_EndDate";
	private static String GriidcProject_StartDate_ColName = "Project_StartDate";
	private static String GriidcProject_Title_ColName = "Project_Title";

	private ResultSet rset = null;
	private ResultSet griidcRset = null;

	private static boolean Debug = false;
	private boolean initialized = false;

	private RisFundSrcProgramsStartEndCollection startEndDatePrograms = null;

	public boolean isInitialized() {
		return initialized;
	}

	public void initialize() {
		super.commonInitialize();
		if (!isInitialized()) {
			try {
				this.startEndDatePrograms = RdbmsUtils
						.getRisFundSrcProgramsStartEndCollection();
			} catch (SQLException e) {
				MiscUtils.fatalError(this.getClass().getName(), "initialize",
						e.getMessage());
			}
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
	 * @throws MultipleRecordsFoundException
	 */
	public void syncGriidcProjectFromRisPrograms()
			throws ClassNotFoundException, PropertyNotFoundException,
			IOException, SQLException, TableNotInDatabaseException {
		String msg = null;
		StringBuffer xtraInfo = null;
		if (isDebug())
			System.out.println(MiscUtils.BreakLine);

		this.initialize();

		// get all records from the RIS Project table
		try {
			rset = this.risDbConnection.selectAllValuesFromTable(RisTableName);

			while (rset.next()) {
				risRecordCount++;
				xtraInfo = new StringBuffer();
				try {
					this.risProgram_ID = rset.getInt(RisProgram_ID_ColName);
					this.risProgram_Title = rset.getString(
							RisProgram_Title_ColName).trim();
					this.risProgram_FundSrc = rset
							.getInt(RisProgram_FundSrc_ColName);
					this.risProgram_LeadInstitution = rset
							.getInt(RisProgram_LeadInstitution_ColName);
					this.risProgram_SubTasks = rset
							.getInt(RisProgram_SubTasks_ColName);

					// data fields fail in a noisy way. - stash some id info
					// here
					xtraInfo.append("RIS table: " + RisTableName + ", "
							+ RisProgram_ID_ColName + ": " + this.risProgram_ID
							+ ", " + RisProgram_Title_ColName + ": "
							+ this.risProgram_Title + ", "
							+ RisProgram_FundSrc_ColName + ": "
							+ this.risProgram_FundSrc + ", "
							+ RisProgram_LeadInstitution_ColName + ": "
							+ this.risProgram_LeadInstitution + ", "
							+ RisProgram_SubTasks_ColName + ": "
							+ this.risProgram_SubTasks);

					this.risProgram_StartDate = rset
							.getDate(RisProgram_StartDate_ColName);
					this.risProgram_EndDate = rset
							.getDate(RisProgram_EndDate_ColName);
					this.risProgram_Abstract = rset
							.getString(RisProgram_Abstract_ColName);

				} catch (SQLException e1) {
					msg = "In RIS Programs record " + xtraInfo.toString()
							+ "\nSQL Exception " + e1.getMessage();
					if (ProjectSynchronizer.isDebug())
						System.err.println(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToRisErrorLogFile(msg);
					this.risRecordErrors++;
					continue; // back to next RIS record from resultSet
				}
				String query = null;
				int count = 0;
				try {
					query = formatGriidcFindQuery();
					if (ProjectSynchronizer.isDebug())
						System.out.println("formatGriidcFindQuery() " + query);
					griidcRset = this.griidcDbConnection
							.executeQueryResultSet(query);
					// find the corresponding GRIIDC record(s)
					while (griidcRset.next()) {
						count++;

						this.griidcProject_Number = griidcRset
								.getInt(GriidcProject_Number_ColName);
						this.griidcFundingEnvelope_Cycle = griidcRset
								.getString(GriidcFundingEnvelope_Cycle_ColName);
						this.griidcProject_Abstract = griidcRset
								.getString(GriidcProject_Abstract_ColName);
						this.griidcProject_EndDate = griidcRset
								.getDate(GriidcProject_EndDate_ColName);
						this.griidcProject_StartDate = griidcRset
								.getDate(GriidcProject_StartDate_ColName);
						this.griidcProject_Title = griidcRset
								.getString(GriidcProject_Title_ColName);

						if (isDebug())
							System.out.println("Found GRIIDC "
									+ griidcProjectToString());
					}

				} catch (SQLException e1) {
					System.err
							.println("SQL Error: Find Project in GRIIDC - Query: "
									+ query);
					e1.printStackTrace();
				}

				// are there matching GRIIDC records?
				// zero records found means ADD this record
				// one record found means UPDATE
				// more than ONE record found.. maybe an error???
				if (count == 0) { // Add the Project
					try {
						this.griidcProject_Number = this.risProgram_ID;
						this.griidcProject_Title = this.risProgram_Title;
						this.griidcFundingEnvelope_Cycle = MiscUtils
								.getProjectNumberFundingCycleCache().getValue(
										this.risProgram_FundSrc);
						RisProgramStartEnd rfspsec = this.startEndDatePrograms
								.getFundSourceProgramStartEndDate(
										this.risProgram_FundSrc,
										this.griidcProject_Number);
						this.griidcProject_StartDate = rfspsec.getStartDate();
						this.griidcProject_EndDate = rfspsec.getEndDate();
						this.griidcProject_Abstract = this.risProgram_Abstract;
						this.addGriidcProjectRecord();
						this.griidcRecordsAdded++;
					} catch (SQLException e) {
						// TODO Auto-generated catch block
						msg = "Error adding GRIIDC Project record : "
								+ e.getMessage();
						if (ProjectSynchronizer.isDebug())
							System.err.println(msg);
						MiscUtils.writeToPrimaryLogFile(msg);
						MiscUtils.writeToRisErrorLogFile(msg);
						this.risRecordErrors++;
					} catch (NoRecordFoundException e) {
						msg = "In RIS " + RisTableName + " Table: "
								+ this.risProgram_FundSrc + " - "
								+ e.getMessage();
						MiscUtils.writeToRisErrorLogFile(msg);
						if (isDebug())
							System.err.println(msg);
						this.risRecordErrors++;
					}

				} else if (count == 1) {
					try {
						if (isCurrentRecordEqual()) {
							this.griidcRecordDuplicates++;
						} else {
							this.griidcProject_Number = this.risProgram_ID;
							this.griidcProject_Title = this.risProgram_Title;
							this.griidcFundingEnvelope_Cycle = MiscUtils
									.getProjectNumberFundingCycleCache()
									.getValue(this.risProgram_FundSrc);
							RisProgramStartEnd rfspsec = this.startEndDatePrograms
									.getFundSourceProgramStartEndDate(
											this.risProgram_FundSrc,
											this.griidcProject_Number);
							this.griidcProject_StartDate = rfspsec
									.getStartDate();
							this.griidcProject_EndDate = rfspsec.getEndDate();
							this.griidcProject_Abstract = this.risProgram_Abstract;
							this.modifyGriidcProjectRecord();
							this.griidcRecordsModified++;
						}
					} catch (NoRecordFoundException e) {
						msg = "In RIS " + RisTableName + " Table: "
								+ this.risProgram_FundSrc + " - "
								+ e.getMessage();
						MiscUtils.writeToRisErrorLogFile(msg);
						if (isDebug())
							System.err.println(msg);
						this.risRecordErrors++;
					}
				} else if (count > 1) { // duplicates
					this.griidcRecordDuplicates++;

					msg = "There are " + count + " records in the  GRIIDC "
							+ GriidcTableName + " table where "
							+ this.getGriidcSearchTermString();
					if (ProjectSynchronizer.isDebug())
						System.out.println(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
				}

			} // end of while (rset.next()) {
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return;
		// end of Project
	}

	private boolean isCurrentRecordEqual() {
		String risDerrivedFundingCycle = null;
		try {
			risDerrivedFundingCycle = MiscUtils
					.getProjectNumberFundingCycleCache().getValue(
							this.risProgram_FundSrc);
		} catch (NoRecordFoundException e) {
			return false;
		}
		RisProgramStartEnd rfspsec = this.startEndDatePrograms
				.getFundSourceProgramStartEndDate(this.risProgram_FundSrc,
						this.griidcProject_Number);

		return (this.griidcProject_Number == this.risProgram_ID
				&& this.griidcProject_Title.equals(this.risProgram_Title)
				&& this.griidcFundingEnvelope_Cycle
						.equals(risDerrivedFundingCycle)
				&& this.griidcProject_StartDate.equals(rfspsec.getStartDate())
				&& this.griidcProject_EndDate.equals(rfspsec.getEndDate()) && this.griidcProject_Abstract
					.equals(this.risProgram_Abstract));
	}

	private String griidcProjectToString() {
		return ProjectSynchronizer.GriidcProject_Number_ColName + ": "
				+ this.griidcProject_Number + ", "
				+ ProjectSynchronizer.GriidcFundingEnvelope_Cycle_ColName
				+ ": " + this.griidcFundingEnvelope_Cycle + ", "
				+ ProjectSynchronizer.GriidcProject_Abstract_ColName + ": "
				+ this.griidcProject_Abstract + ", "
				+ ProjectSynchronizer.GriidcProject_EndDate_ColName + ": "
				+ this.griidcProject_EndDate + ", "
				+ ProjectSynchronizer.GriidcProject_StartDate_ColName + ": "
				+ this.griidcProject_StartDate + ", "
				+ ProjectSynchronizer.GriidcProject_Title_ColName + ": "
				+ this.griidcProject_Title;
	}

	private String getGriidcSearchTermString() {
		return RdbmsConnection
				.wrapInDoubleQuotes(ProjectSynchronizer.GriidcProject_Number_ColName)
				+ RdbmsConstants.EqualSign + this.risProgram_ID;
	}

	private String formatGriidcFindQuery() {
		String query = null;
		query = "SELECT * FROM "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ " WHERE "
				+ RdbmsConnection
						.wrapInDoubleQuotes(ProjectSynchronizer.GriidcProject_Number_ColName)
				+ RdbmsConstants.EqualSign + this.risProgram_ID;

		return query;
	}

	private String formatModifyQuery() throws SQLException,
			ClassNotFoundException, FileNotFoundException,
			PropertyNotFoundException {

		DbColumnInfo[] info = this.getDbColumnInfo();

		DbColumnInfo[] whereInfo = new DbColumnInfo[1];

		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);

		tci.getDbColumnInfo(ProjectSynchronizer.GriidcProject_Number_ColName)
				.setColValue(String.valueOf(this.griidcProject_Number));

		whereInfo[0] = tci
				.getDbColumnInfo(ProjectSynchronizer.GriidcProject_Number_ColName);
		String query = RdbmsUtils.formatUpdateStatement(
				ProjectSynchronizer.GriidcTableName, info, whereInfo);

		if (ProjectSynchronizer.isDebug())
			System.out.println("formatModifyQuery() " + query);

		return query;
	}

	private void addGriidcProjectRecord() throws SQLException,
			ClassNotFoundException, IOException, PropertyNotFoundException {
		String msg = null;

		String addQuery = this.formatAddQuery();
		if (ProjectSynchronizer.isDebug())
			System.out.println("Query: " + addQuery);
		this.griidcDbConnection.executeQueryBoolean(addQuery);
		msg = "Added GRIIDC " + GriidcTableName + ": "
				+ griidcProjectToString();
		MiscUtils.writeToPrimaryLogFile(msg);
		if (ProjectSynchronizer.isDebug())
			System.out.println(msg);
		return;
	}

	private DbColumnInfo[] getDbColumnInfo() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTableName);

		tci.getDbColumnInfo(ProjectSynchronizer.GriidcProject_Number_ColName)
				.setColValue(String.valueOf(this.griidcProject_Number));
		tci.getDbColumnInfo(
				ProjectSynchronizer.GriidcFundingEnvelope_Cycle_ColName)
				.setColValue(this.griidcFundingEnvelope_Cycle);
		tci.getDbColumnInfo(ProjectSynchronizer.GriidcProject_Abstract_ColName)
				.setColValue(this.griidcProject_Abstract);
		tci.getDbColumnInfo(ProjectSynchronizer.GriidcProject_EndDate_ColName)
				.setColValue(this.griidcProject_EndDate.toString());
		tci.getDbColumnInfo(ProjectSynchronizer.GriidcProject_StartDate_ColName)
				.setColValue(this.griidcProject_StartDate.toString());
		tci.getDbColumnInfo(ProjectSynchronizer.GriidcProject_Title_ColName)
				.setColValue(String.valueOf(this.griidcProject_Title));
		return tci.getDbColumnInfo();
	}

	private String formatAddQuery() throws SQLException,
			ClassNotFoundException, FileNotFoundException,
			PropertyNotFoundException {

		DbColumnInfo[] info = getDbColumnInfo();
		String query = RdbmsUtils.formatInsertStatement(GriidcTableName, info);
		return query;
	}

	private void modifyGriidcProjectRecord() throws ClassNotFoundException,
			IOException, PropertyNotFoundException, SQLException {
		String msg = null;
		String modifyQuery = null;

		modifyQuery = this.formatModifyQuery();
		if (ProjectSynchronizer.isDebug()) 
			  System.out.println("Query: " + modifyQuery);
		this.griidcDbConnection.executeQueryBoolean(modifyQuery);
		msg = "Modified GRIIDC " + GriidcTableName + ": "
				+ griidcProjectToString();
		MiscUtils.writeToPrimaryLogFile(msg);
		if (ProjectSynchronizer.isDebug())
			System.out.println(msg);
		return;

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
		return ProjectSynchronizer.Debug;
	}

	public static void setDebug(boolean debug) {
		ProjectSynchronizer.Debug = debug;
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
