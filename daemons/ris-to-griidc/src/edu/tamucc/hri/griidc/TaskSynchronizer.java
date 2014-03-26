package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.rdbms.utils.DbColumnInfo;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsConstants;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;
import edu.tamucc.hri.rdbms.utils.RisFundSrcProgramsStartEndCollection;
import edu.tamucc.hri.rdbms.utils.TableColInfo;

/**
 * read the RIS Projects Info and create the Task Table in GRIIDC
 * 
 * @author jvh
 * 
 */
public class TaskSynchronizer extends SynchronizerBase {

	public TaskSynchronizer() {
		// TODO Auto-generated constructor stub
	}

	private static final String RisTableName = "Projects";
	private static final String GriidcTaskTableName = "Task";

	private static final String GriidcProjectTableName = "Project";

	private int risRecordCount = 0;
	private int risRecordsSkipped = 0;
	private int risRecordErrors = 0;
	private int griidcRecordsAdded = 0;
	private int griidcRecordsModified = 0;
	private int griidcRecordDuplicates = 0;

	// RIS Projects fields
	private int risProject_ID = -1;
	private int risProgram_ID = -1;
	private int risProject_SubTaskNum = -1;
	private String risProject_Title = null;
	private int risProject_LeadInstitution = -1;
	private String risProject_Goals = null;
	private String risProject_Purpose = null;
	private String risProject_Objective = null;
	private String risProject_Abstract = null;
	private String risProject_WebAddr = null;
	private String risProject_Location = null;
	private String risProject_SGLink = null;
	private int risProject_SGRecID = -1;
	private String risProject_Comment = null;
	private int risProject_Completed = -1;

	// RIS Projects column names

	private static String RisProject_ID_ColName = "Project_ID";
	private static String RisProgram_ID_ColName = "Program_ID";
	private static String RisProject_SubTaskNum_ColName = "Project_SubTaskNum";
	private static String RisProject_Title_ColName = "Project_Title";
	private static String RisProject_LeadInstitution_ColName = "Project_LeadInstitution";
	private static String RisProject_Goals_ColName = "Project_Goals";
	private static String RisProject_Purpose_ColName = "Project_Purpose";
	private static String RisProject_Objective_ColName = "Project_Objective";
	private static String RisProject_Abstract_ColName = "Project_Abstract";
	private static String RisProject_WebAddr_ColName = "Project_WebAddr";
	private static String RisProject_Location_ColName = "Project_Location";
	private static String RisProject_SGLink_ColName = "Project_SGLink";
	private static String RisProject_SGRecID_ColName = "Project_SGRecID";
	private static String RisProject_Comment_ColName = "Project_Comment";
	private static String RisProject_Completed_ColName = "Project_Completed";

	// GRIIDC Task fields

	private int griidcTask_Number = -1;
	private String griidcFundingEnvelope_Cycle = null;
	private int griidcTaskProject_Number = -1;
	private String griidcTask_Abstract = null;
	private java.sql.Date griidcTask_EndDate = null; // was java.sql.Date
	private java.sql.Date griidcTask_StartDate = null; // was java.sql.Date
	private String griidcTask_Title = null;

	// GRIIDC Task Column Names
	private static String GriidcTask_Number_ColName = "Task_Number";
	private static String GriidcFundingEnvelope_Cycle_ColName = "FundingEnvelope_Cycle";
	private static String GriidcProject_Number_ColName = "Project_Number";
	private static String GriidcTask_Abstract_ColName = "Task_Abstract";
	private static String GriidcTask_EndDate_ColName = "Task_EndDate";
	private static String GriidcTask_StartDate_ColName = "Task_StartDate";
	private static String GriidcTask_Title_ColName = "Task_Title";

	private static String GriidcProjectTable_ProjectNumber_ColName = GriidcProject_Number_ColName;
	private static String GriidcProjectTable_StartDate_ColName = "Project_StartDate";
	private static String GriidcProjectTable_EndDate_ColName = "Project_EndDate";
	private static String GriidcProjectTable_FundingEnvelope_Cycle_ColName = GriidcFundingEnvelope_Cycle_ColName;

	private ResultSet risRS = null;
	private ResultSet griidcRS = null;

	private static boolean Debug = false;
	private boolean initialized = false;

	private static final String[] shortColumnNameList = {
			GriidcTask_Number_ColName, GriidcFundingEnvelope_Cycle_ColName,
			GriidcProject_Number_ColName, GriidcTask_EndDate_ColName,
			GriidcTask_StartDate_ColName, GriidcTask_Title_ColName };

	private RisFundSrcProgramsStartEndCollection startEndDatePrograms = null;

	public boolean isInitialized() {
		return initialized;
	}

	public void initialize() {
		this.commonInitialize();
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

	public void syncGriidcTaskFromProjects() {
		this.initialize();
		String msg = null;
		if (isDebug())
			System.out.println(MiscUtils.BreakLine);

		try {
			// get all records from the RIS Project table
			risRS = this.risDbConnection.selectAllValuesFromTable(RisTableName);
			// read each record retrieved from RIS
			while (risRS.next()) { // continue statements branch back to here
				risRecordCount++;

				this.risProject_ID = risRS.getInt(RisProject_ID_ColName);
				this.risProgram_ID = risRS.getInt(RisProgram_ID_ColName);
				this.risProject_SubTaskNum = risRS
						.getInt(RisProject_SubTaskNum_ColName);
				this.risProject_Title = risRS.getString(RisProject_Title_ColName);
				this.risProject_LeadInstitution = risRS
						.getInt(RisProject_LeadInstitution_ColName);
				this.risProject_Goals = risRS.getString(RisProject_Goals_ColName);
				this.risProject_Purpose = risRS
						.getString(RisProject_Purpose_ColName);
				this.risProject_Objective = risRS
						.getString(RisProject_Objective_ColName);
				this.risProject_Abstract = risRS
						.getString(RisProject_Abstract_ColName);
				this.risProject_WebAddr = risRS
						.getString(RisProject_WebAddr_ColName);
				this.risProject_Location = risRS
						.getString(RisProject_Location_ColName);
				this.risProject_SGLink = risRS.getString(RisProject_SGLink_ColName);
				this.risProject_SGRecID = risRS.getInt(RisProject_SGRecID_ColName);
				this.risProject_Comment = risRS
						.getString(RisProject_Comment_ColName);
				this.risProject_Completed = risRS
						.getInt(RisProject_Completed_ColName);
				
				// find the corresponding GRIIDC record(s)
				
				DbColumnInfo[] whereColInfo = getWhereClauseInfo(this.risProject_ID,this.risProgram_ID);
				String query = RdbmsUtils.formatSelectStatement(GriidcTaskTableName, whereColInfo);

			
				if (TaskSynchronizer.isDebug())
					System.out.println("formatGriidcFindQuery() " + query);

				int count = 0;
				String shortGriidcRecord = null;
				String whereClauseMessage = RdbmsUtils.formatWhereClause(whereColInfo);
				try { // find matching GRIIDC records - at most there should be one
					griidcRS = this.griidcDbConnection.executeQueryResultSet(query);
					while (griidcRS.next()) {
						count++;
						this.griidcTask_Number = griidcRS
								.getInt(GriidcTask_Number_ColName);
						this.griidcFundingEnvelope_Cycle = griidcRS
								.getString(GriidcFundingEnvelope_Cycle_ColName);
						this.griidcTaskProject_Number = griidcRS
								.getInt(GriidcProject_Number_ColName);
						this.griidcTask_Abstract = griidcRS
								.getString(GriidcTask_Abstract_ColName);
						this.griidcTask_EndDate = griidcRS
								.getDate(GriidcTask_EndDate_ColName);
						this.griidcTask_StartDate = griidcRS
								.getDate(GriidcTask_StartDate_ColName);
						this.griidcTask_Title = griidcRS
								.getString(GriidcTask_Title_ColName);
					}
					shortGriidcRecord = this.shortTaskTableColInfoToString();
					
				} catch (SQLException e1) {
					// TODO jvh catch the find matching GRIIDC records
					e1.printStackTrace();
				}
				if (isDebug())
					System.out.println("Found GRIIDC " + shortGriidcRecord);

				// are there matching GRIIDC records?
				// zero records found means ADD this record
				// one record found means UPDATE
				// more than ONE record found.. maybe an error???
				if (count == 0) { // Add the Task
					try {
						this.assignGriidcTaskFromRisProject();
						this.addGriidcTaskRecord();
						this.griidcRecordsAdded++;
					} catch (SQLException e) {
						msg = "Error adding GRIIDC " + GriidcTaskTableName
								+ " record : " + shortGriidcRecord;
						if (TaskSynchronizer.isDebug())
							System.err.println(msg);
						MiscUtils.writeToPrimaryLogFile(msg);
						MiscUtils.writeToRisErrorLogFile(msg);
						this.risRecordErrors++;
						this.risRecordsSkipped++;
						// back to next RIS record from resultSet
					}

				} else if (count == 1) { // modify the Task
					//System.out.println("\nCount == " + count + " query used: " + query);
					if (isCurrentRecordEqual()) {
						this.griidcRecordDuplicates++;
					} else { // not equal but keys match

						try {
							this.assignGriidcTaskFromRisProject();
							this.modifyGriidcTaskRecord();
							this.griidcRecordsModified++;
							// back to next RIS record from resultSet
						} catch (Exception e) {
							msg = "Error modifying GRIIDC Task record : "
									+ shortGriidcRecord;
							if (TaskSynchronizer.isDebug())
								System.err.println(msg);
							MiscUtils.writeToPrimaryLogFile(msg);
							MiscUtils.writeToRisErrorLogFile(msg);
							this.risRecordErrors++;
							this.risRecordsSkipped++;
						}
					}

				} else if (count > 1) { // multiple task records???
					msg = "There are " + count + " records in the  GRIIDC "
							+ GriidcTaskTableName + " table " + whereClauseMessage;
					if (TaskSynchronizer.isDebug())
						System.out.println(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToRisErrorLogFile(msg);
					// back to next RIS record from resultSet
				}
			} // end of while (risRS.next())
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (TableNotInDatabaseException e) {
				MiscUtils.fatalError(this.getClass().getName(),"TaskSynchronizer", "TableNotInDatabaseException: " + e.getMessage());
		}
	}
	private DbColumnInfo[] getWhereClauseInfo(int taskNumber, int taskProjNumber) throws SQLException  {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				RdbmsUtils.getGriidcDbConnectionInstance(), GriidcTaskTableName);

		DbColumnInfo dbciTemp1 = tci.getDbColumnInfo(GriidcTask_Number_ColName);
		dbciTemp1.setColValue(String.valueOf(taskNumber));

		DbColumnInfo dbciTemp2 = tci.getDbColumnInfo(GriidcProject_Number_ColName);
		dbciTemp2.setColValue(String.valueOf(taskProjNumber));

		DbColumnInfo[] info = { dbciTemp1, dbciTemp2 };
		return info;
	}
	/**
	 * RIS  vs GRIIDC
	 * RIS.Program    is GRIIDC.Project
	 * RIS.Project    is GRIIDC.Task
	 * @return
	 */
	private boolean isCurrentRecordEqual() {
		//
		//String format = "%n%15s: %10s to %10s %10s";
		//System.out.println("TaskSynchronizer.isCurrentRecordEqual()");
		//System.out.printf(format, "GRIIDC task #",this.griidcTask_Number,"RIS Project ID", this.risProject_ID);
		//System.out.printf(format, "GRIIDC project #",this.griidcTaskProject_Number,"RIS Program ID",this.risProgram_ID);
		//System.out.printf(format, "GRIIDC task title",this.griidcTask_Title,"RIS Prject ID", this.risProject_Title);
        //
		boolean eq = (this.griidcTask_Number == this.risProject_ID
				&& this.griidcTaskProject_Number == this.risProgram_ID
				&& this.griidcTask_Title.equals(this.risProject_Title) 
				// && this.griidcTask_Abstract.equals(this.risProject_Abstract)
				);
		// System.out.println("\tresult is " + ((eq) ? "EQUAL" : "NOT EQUAL"));
		return eq;
	}

	private void addGriidcTaskRecord() throws SQLException {
		String msg = null;

		String query = RdbmsUtils.formatInsertStatement(GriidcTaskTableName,
				this.getDbColumnInfo());
		// if (TaskSynchronizer.isDebug())
		// System.out.println("Query: " + query);
		this.griidcDbConnection.executeQueryBoolean(query);
		msg = "Added GRIIDC " + GriidcTaskTableName + ": "
				+ shortTaskTableColInfoToString();
		MiscUtils.writeToPrimaryLogFile(msg);
		if (TaskSynchronizer.isDebug())
			System.out.println(msg);
		return;
	}

	private void modifyGriidcTaskRecord() throws SQLException {
		String msg = null;
		String modifyQuery = null;

		modifyQuery = RdbmsUtils.formatUpdateStatement(GriidcTaskTableName,
				this.getDbColumnInfo(), this.getWhereColumnInfo());
		this.griidcDbConnection.executeQueryBoolean(modifyQuery);
		msg = "Modified GRIIDC " + GriidcTaskTableName + ": "
				+ shortTaskTableColInfoToString();
		MiscUtils.writeToPrimaryLogFile(msg);
		if (TaskSynchronizer.isDebug())
			System.out.println(msg);
		return;
	}

	private DbColumnInfo[] getDbColumnInfo() throws SQLException {
		return getGriidcTaskTableColInfo().getDbColumnInfo();
	}

	private TableColInfo getGriidcTaskTableColInfo() throws SQLException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				this.griidcDbConnection, GriidcTaskTableName);

		String tempValue = null;
		tci.getDbColumnInfo(GriidcTask_Number_ColName).setColValue(
				String.valueOf(this.griidcTask_Number));

		tempValue = null;
		if (this.griidcFundingEnvelope_Cycle != null) {
			tempValue = this.griidcFundingEnvelope_Cycle.toString();
		}
		tci.getDbColumnInfo(GriidcFundingEnvelope_Cycle_ColName).setColValue(
				tempValue);

		tci.getDbColumnInfo(GriidcProject_Number_ColName).setColValue(
				String.valueOf(this.griidcTaskProject_Number));

		tempValue = null;
		if (this.griidcTask_Abstract != null) {
			tempValue = this.griidcTask_Abstract.toString();
		}
		tci.getDbColumnInfo(GriidcTask_Abstract_ColName).setColValue(tempValue);

		tempValue = null;
		if (this.griidcTask_EndDate != null) {
			tempValue = this.griidcTask_EndDate.toString();
		}
		tci.getDbColumnInfo(GriidcTask_EndDate_ColName).setColValue(tempValue);

		tempValue = null;
		if (this.griidcTask_StartDate != null) {
			tempValue = this.griidcTask_StartDate.toString();
		}
		tci.getDbColumnInfo(GriidcTask_StartDate_ColName)
				.setColValue(tempValue);

		tempValue = null;
		if (this.griidcTask_Title != null) {
			tempValue = this.griidcTask_Title.toString();
		}
		tci.getDbColumnInfo(GriidcTask_Title_ColName).setColValue(tempValue);
		if (TaskSynchronizer.isDebug()) {
			System.out.println("TaskSynchronizer.getDbColumnInfo() TCI: "
					+ shortTaskTableColInfoToString(tci));
		}
		return tci;
	}

	private String shortTaskTableColInfoToString() throws SQLException {
		TableColInfo tci = getGriidcTaskTableColInfo();
		return shortTaskTableColInfoToString(tci);
	}

	private String shortTaskTableColInfoToString(TableColInfo tci) {
		StringBuffer sb = new StringBuffer();
		boolean notFirstTime = false;
		for (String colName : shortColumnNameList) {
			if (notFirstTime)
				sb.append(", ");
			notFirstTime = true;
			sb.append(colName);
			sb.append(": ");
			sb.append(tci.getDbColumnInfo(colName).getColValue());
		}
		return sb.toString();
	}

	private DbColumnInfo[] getWhereColumnInfo() throws SQLException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				this.griidcDbConnection, GriidcTaskTableName);

		DbColumnInfo dbci = tci
				.getDbColumnInfo(TaskSynchronizer.GriidcTask_Number_ColName);

		dbci.setColValue(String.valueOf(this.griidcTask_Number));
		DbColumnInfo[] whereColInfo = new DbColumnInfo[1];
		whereColInfo[0] = dbci;
		return whereColInfo;
	}

	public String griidcTaskToString() {
		return "GRIIDC Task [griidcTask_Number=" + griidcTask_Number
				+ ", griidcFundingEnvelope_Cycle="
				+ griidcFundingEnvelope_Cycle + ", griidcProject_Number="
				+ griidcTaskProject_Number + ", griidcTask_Abstract="
				+ griidcTask_Abstract + ", griidcTask_EndDate="
				+ griidcTask_EndDate + ", griidcTask_StartDate="
				+ griidcTask_StartDate + ", griidcTask_Title="
				+ griidcTask_Title + "]";
	}

	public String risProjectToString() {
		return "RIS Project [risProject_ID=" + risProject_ID
				+ ", risProgram_ID=" + risProgram_ID
				+ ", risProject_SubTaskNum=" + risProject_SubTaskNum
				+ ", risProject_Title=" + risProject_Title
				+ ", risProject_LeadInstitution=" + risProject_LeadInstitution
				+ ", risProject_Goals=" + risProject_Goals
				+ ", risProject_Purpose=" + risProject_Purpose
				+ ", risProject_Objective=" + risProject_Objective
				+ ", risProject_Abstract=" + risProject_Abstract
				+ ", risProject_WebAddr=" + risProject_WebAddr
				+ ", risProject_Location=" + risProject_Location
				+ ", risProject_SGLink=" + risProject_SGLink
				+ ", risProject_SGRecID=" + risProject_SGRecID
				+ ", risProject_Comment=" + risProject_Comment
				+ ", risProject_Completed=" + risProject_Completed + "]";
	}

	private String formatGriidcFindTaskQuery(int taskKey) {
		String query = null;
		query = "SELECT * FROM "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTaskTableName)
				+ " WHERE "
				+ RdbmsConnection
						.wrapInDoubleQuotes(TaskSynchronizer.GriidcTask_Number_ColName)
				+ RdbmsConstants.EqualSign + taskKey;

		return query;
	}

	/**
	 * populate the the GRIIDC Task state variables from the RIS Project data
	 * where possible. The other values, end date, start date and funding cycle
	 * we get from the corresponding GRIIDC Project table
	 * 
	 * @throws ClassNotFoundException
	 * @throws SQLException
	 */
	private void assignGriidcTaskFromRisProject() throws SQLException {
		this.griidcTask_Number = this.risProject_ID;
		this.griidcTaskProject_Number = this.risProgram_ID;
		this.getGriidcTaskValuesFromGriidcProject(this.risProgram_ID);
		this.griidcTask_Abstract = this.risProject_Abstract;
		this.griidcTask_Title = this.risProject_Title;
	}

	/**
	 * there are some values in the Task record that are not in the RIS Projects
	 * record. Get these values from the associated GRIIDC.Project table record.
	 * 
	 * @param griidcProjectNumber
	 * @return
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 */
	private boolean getGriidcTaskValuesFromGriidcProject(int griidcProjectNumber)
			throws SQLException {
		String cycleName = null;
		java.sql.Date sDate = null;
		java.sql.Date eDate = null;
		String query = formatFindGriidcProjectQuery(griidcProjectNumber);
		this.griidcRS = this.griidcDbConnection.executeQueryResultSet(query);
		boolean foundOne = false;
		while (this.griidcRS.next()) {
			cycleName = this.griidcRS
					.getString(GriidcProjectTable_FundingEnvelope_Cycle_ColName);
			sDate = this.griidcRS.getDate(GriidcProjectTable_StartDate_ColName);
			eDate = this.griidcRS.getDate(GriidcProjectTable_EndDate_ColName);
			foundOne = true;
		}
		if (foundOne) {
			this.griidcFundingEnvelope_Cycle = cycleName;
			this.griidcTask_EndDate = eDate;
			this.griidcTask_StartDate = sDate;
		}
		return foundOne;
	}

	private String formatFindGriidcProjectQuery(int griidcProjectNumber) {
		String query = null;
		query = "SELECT * FROM "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcProjectTableName)
				+ " WHERE "
				+ RdbmsConnection
						.wrapInDoubleQuotes(TaskSynchronizer.GriidcProject_Number_ColName)
				+ RdbmsConstants.EqualSign + griidcProjectNumber;

		return query;
	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
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
