package edu.tamucc.hri.griidc.ris;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.utils.MiscUtils;
import edu.tamucc.hri.griidc.utils.RandomBoolean;

public class RolesSynchronizer extends SynchronizerBase {

	private static final String RisTableName = "Roles";
	private static final String GriidcTaskRolePrefix = "Task";
	private static final String GriidcProjRolePrefix = "Proj";
	private static final String GriidcRoleTableNameSuffix = "Role";
	private static final String GriidcTaskRoleTableName = GriidcTaskRolePrefix
			+ GriidcRoleTableNameSuffix;
	private static final String GriidcProjRoleTableName = GriidcProjRolePrefix
			+ GriidcRoleTableNameSuffix;
	private ResultSet risRS = null;
	private ResultSet griidcRS = null;

	private static boolean Debug = false;
	private boolean initialized = false;

	// RIS Projects column names
	private static String RisRole_ID_ColName = "Role_ID";
	private static String RisRole_Name_ColName = "Role_Name";
	// RIS column values
	private int risRoleId = -1;
	private String risRoleName = null;

	// GRIIDC Role column name suffix
	private static String GriidcRole_Number_ColName_Suffix = "Role_Number";
	private static String GriidcRoleName_ColName_Suffix = "Role_RoleName";
	private static String GriidcRoleDescription_ColName_Suffix = "Role_RoleDescription";
	private static String GriidcRole_RIS_ID_ColNameSuffix = "Role_RIS_ID";

	// GRIIDC TaskRole col values
	private int griidcRoleNumber = -1;
	private String griidcRoleName = null;
	private String griidcRoleDescription = null;
	private int griidcRoleRisId = -1;

	public static final String TaskSelectSuffix = " != 0";
	public static final String ProjSelectSuffix = " = 0";
	public static final String GriidcRolesSelect = "SELECT DISTINCT R.Role_ID,Role_Name FROM Roles R JOIN ProjPeople PP ON R.Role_ID = PP.Role_ID WHERE PP.Project_ID ";

	public static final String GriidcTaskRolesSelect = "SELECT DISTINCT R.Role_ID,Role_Name FROM Roles R JOIN ProjPeople PP ON R.Role_ID = PP.Role_ID WHERE PP.Project_ID != 0";

	public static final String GriidcProjRolesSelect = "SELECT DISTINCT R.Role_ID,Role_Name FROM Roles R JOIN ProjPeople PP ON R.Role_ID = PP.Role_ID WHERE PP.Project_ID = 0";

	private int risRecordCount = 0;
	private int risRecordErrors = 0;
	private int griidcTaskRoleAdded = 0;
	private int griidcTaskRoleModified = 0;
	private int griidcTaskRoleDuplicates = 0;
	private int griidcProjRoleAdded = 0;
	private int griidcProjRoleModified = 0;
	private int griidcProjRoleDuplicates = 0;


	public RolesSynchronizer() {
		// TODO Auto-generated constructor stub
	}

	public boolean isInitialized() {
		return initialized;
	}

	public void initialize() {
		super.commonInitialize();
		if (!isInitialized()) {
			initialized = true;
		}
	}

	public void syncGriidcRolesFromRisRoles() throws ClassNotFoundException,
			PropertyNotFoundException, IOException, SQLException,
			TableNotInDatabaseException {
		if (RolesSynchronizer.isDebug())
			System.out
					.println("RoleSynchronizer.syncGriidcRolesFromRisRoles() -- starting --");
		this.initialize();
		syncGriidcRoles(GriidcProjRolePrefix);
		syncGriidcRoles(GriidcTaskRolePrefix);
	}

	public String getSelectStatement(String prefix) {

		String query = RolesSynchronizer.GriidcProjRolesSelect
				+ RolesSynchronizer.ProjSelectSuffix;
		if (prefix.equals(RolesSynchronizer.GriidcTaskRolePrefix)) {
			query = RolesSynchronizer.GriidcProjRolesSelect
					+ RolesSynchronizer.TaskSelectSuffix;
		}
		return query;
	}

	public void syncGriidcRoles(String prefix) throws ClassNotFoundException,
			PropertyNotFoundException, IOException, SQLException,
			TableNotInDatabaseException {
		String msg = null;
		if (RolesSynchronizer.isDebug())
			System.out.println("RoleSynchronizer.syncGriidcRoles(" + prefix
					+ ") starting");

		String griidcTableName = this.getGriidcTableNameFromPrefix(prefix);

		try {
			risRS = this.risDbConnection.executeQueryResultSet(this
					.getSelectStatement(prefix));

			while (risRS.next()) { // continue statements branch back to here
				risRecordCount++;
				try {

					this.risRoleId = risRS.getInt(RisRole_ID_ColName);
					this.risRoleName = risRS.getString(RisRole_Name_ColName);
					if (RolesSynchronizer.isDebug())
						System.out
								.println("\n\nRoleSynchronizer.syncGriidcRoles("
										+ prefix
										+ ") read RIS Roles record\n"
										+ this.risRoleToString());

				} catch (SQLException e1) {
					msg = "In RIS " + RisTableName + " record SQL Exception "
							+ e1.getMessage();
					if (RolesSynchronizer.isDebug())
						System.out.println(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToErrorLogFile(msg);
					this.risRecordErrors++;
					continue; // back to next RIS record from resultSet
				}
				String query = null;
				// find the matching GRIIDC record(s) - if any

				try {
					query = formatGriidcFindRoleQuery(prefix, this.risRoleId);
					if (RolesSynchronizer.isDebug())
						System.out.println("formatGriidcFindRoleQuery() "
								+ query);
					griidcRS = this.griidcDbConnection
							.executeQueryResultSet(query);

				} catch (SQLException e1) {
					System.err.println("SQL Error: Find Role in GRIIDC "
							+ griidcTableName + " - Query: " + query);
					e1.printStackTrace();
				}

				int count = 0;
				// count the number of matches
				try {
					while (griidcRS.next()) {
						count++;
						this.griidcRoleNumber = griidcRS.getInt(prefix
								+ GriidcRole_Number_ColName_Suffix);
						this.griidcRoleRisId = griidcRS.getInt(prefix 
										+ GriidcRole_RIS_ID_ColNameSuffix);
						this.griidcRoleName = griidcRS.getString(prefix
								+ GriidcRoleName_ColName_Suffix);
						this.griidcRoleDescription = griidcRS.getString(prefix
								+ GriidcRoleDescription_ColName_Suffix);
						
								

						if (isDebug())
							System.out.println("Found GRIIDC "
									+ this.griidcRoleToString(prefix));
					}

				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}

				if (isDebug())
					System.out.println("-- Found " + count
							+ " matching GRIIDC records");

				// are there matching GRIIDC records?
				// zero records found means ADD this record
				// one record found means UPDATE
				// more than ONE record found.. maybe an error???
				this.setGriidcFromRis();
				if (count == 0) { // Add the Task
					try {

						if (isDebug()) {
							System.out
									.println("Zero matches found - add the record");
							System.out.println(this.griidcRoleToString(prefix));
							System.out.println(this.risRoleToString());
						}
						this.addGriidcRoleRecord(prefix);
						this.incrementAdded(prefix);
					} catch (SQLException e) {
						msg = "Error adding GRIIDC " + griidcTableName
								+ " record : " + e.getMessage();
						if (RolesSynchronizer.isDebug())
							System.out.println(msg);
						MiscUtils.writeToPrimaryLogFile(msg);
						MiscUtils.writeToErrorLogFile(msg);
						this.risRecordErrors++;
						// back to next RIS record from resultSet
					}

				} else if (count == 1) { // modify if keys match but content has
											// changed
					if (isDebug())
						System.out.println("Found a matching key: "
								+ this.griidcRoleToString(prefix));
					if (isCurrentRecordEqual(prefix)) {
						this.incrementDuplicate(prefix);
					} else { // not equal but matches the keys in GRIIDC
						try {
							this.modifyGriidcRoleRecord(prefix);
							this.incrementModified(prefix);
							// back to next RIS record from resultSet
						} catch (Exception e) {
							msg = "Error modifying GRIIDC " + griidcTableName
									+ " record : " + e.getMessage();
							if (RolesSynchronizer.isDebug())
								System.out.println(msg);
							MiscUtils.writeToPrimaryLogFile(msg);
							MiscUtils.writeToErrorLogFile(msg);
							this.risRecordErrors++;
						}
					}
				} else if (count > 1) { // duplicates

					msg = "There are "
							+ count
							+ " records in the  GRIIDC "
							+ griidcTableName
							+ " table "
							+ RdbmsUtils.formatWhereClause(this
									.getWhereColumnInfo(prefix));
					if (RolesSynchronizer.isDebug())
						System.out.println(msg);
					MiscUtils.writeToPrimaryLogFile(msg);
					MiscUtils.writeToErrorLogFile(msg);
					// back to next RIS record from resultSet
				}
				reportTheCounts();
			} // end of main while loop
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return;
		// end of Project
	}

	private String formatGriidcFindRoleQuery(String prefix, int taskKey) {
		String query = null;
		String tableName = getGriidcTableNameFromPrefix(prefix);
		query = "SELECT * FROM "
				+ RdbmsConnection.wrapInDoubleQuotes(tableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(prefix
						+ GriidcRole_RIS_ID_ColNameSuffix)
				+ RdbmsConstants.EqualSign + taskKey;

		return query;
	}

	private boolean isCurrentRecordEqual(String prefix) {
		if (isDebug()) {
			System.out.println("is CurrentRecordEqual "
					+ this.risRoleToString() + this.griidcRoleToString(prefix));
		}
		boolean eq = (this.griidcRoleNumber == this.risRoleId && this.griidcRoleName
				.equals(this.risRoleName));
		eq = (eq && RandomBoolean.getInstance().getRandomBoolean());
		if (isDebug()) {
			System.out.println("is CurrentRecordEqual returning: " + eq);
		}
		return eq;
	}

	/**
	 * IF RandomBoolean.isOn() (flag set to true) rollTheDice the return true
	 * about half the time, false about half. IF RandomBoolean.isOff()
	 * roolTheDice will always return true;
	 * 
	 * @return
	 */
	private boolean rollTheDice() {
		return RandomBoolean.getInstance().getRandomBoolean();
	}

	private void addGriidcRoleRecord(String prefix) throws SQLException,
			ClassNotFoundException, IOException, PropertyNotFoundException {
		String msg = null;
		String tableName = getGriidcTableNameFromPrefix(prefix);
		if (RolesSynchronizer.isDebug())
			System.out.println("RoleSynchronizer.addGriidcRoleRecord(" + prefix
					+ ")");
		DbColumnInfo[] dci = this.getInsertDbColumnInfo(prefix);
		String query = RdbmsUtils.formatInsertStatement(tableName,dci);
		if (RolesSynchronizer.isDebug())
			System.out.println("Add Griidc Roll Query: " + query);
		this.griidcDbConnection.executeQueryBoolean(query);
		msg = "Added GRIIDC " + tableName + ": " + griidcRoleToString(prefix);
		MiscUtils.writeToPrimaryLogFile(msg);
		if (RolesSynchronizer.isDebug())
			System.out.println(msg);
		return;
	}

	private void modifyGriidcRoleRecord(String prefix)
			throws ClassNotFoundException, IOException,
			PropertyNotFoundException, SQLException {
		String msg = null;
		String modifyQuery = null;
		String tableName = getGriidcTableNameFromPrefix(prefix);
		DbColumnInfo[] modColInfo = this.getDbColumnInfo(prefix);
		DbColumnInfo[] whereColInfo = this.getWhereColumnInfo(prefix);
		modifyQuery = RdbmsUtils.formatUpdateStatement(tableName, modColInfo,
				whereColInfo);

		this.griidcDbConnection.executeQueryBoolean(modifyQuery);
		msg = "Modified GRIIDC " + tableName + ": "
				+ griidcRoleToString(prefix);
		MiscUtils.writeToPrimaryLogFile(msg);
		return;
	}

	private void setGriidcFromRis() {
		this.griidcRoleName = this.risRoleName;
		this.griidcRoleDescription = this.risRoleName;
		this.griidcRoleRisId = this.risRoleId;
	}

	private String getGriidcTableNameFromPrefix(String prefix) {
		String tableName = GriidcProjRoleTableName;
		if (prefix.equals(RolesSynchronizer.GriidcTaskRolePrefix)) {
			tableName = GriidcTaskRoleTableName;
		}
		return tableName;
	}

	private void incrementAdded(String prefix) {
		if (prefix.equals(RolesSynchronizer.GriidcTaskRolePrefix)) {
			this.griidcTaskRoleAdded++;
		} else {
			this.griidcProjRoleAdded++;
		}
		return;
	}

	private void incrementModified(String prefix) {
		if (prefix.equals(RolesSynchronizer.GriidcTaskRolePrefix)) {
			this.griidcTaskRoleModified++;
		} else
			this.griidcProjRoleModified++;
		return;
	}

	private void incrementDuplicate(String prefix) {
		if (prefix.equals(RolesSynchronizer.GriidcTaskRolePrefix)) {
			this.griidcTaskRoleDuplicates++;
		} else
			this.griidcProjRoleDuplicates++;
		return;
	}

	private DbColumnInfo[] getDbColumnInfo(String prefix) throws SQLException,
			ClassNotFoundException {

		String tableName = getGriidcTableNameFromPrefix(prefix);

		

		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				this.griidcDbConnection, tableName);

		tci.getDbColumnInfo(prefix + GriidcRole_Number_ColName_Suffix)
				.setColValue(String.valueOf(this.griidcRoleNumber));

		tci.getDbColumnInfo(prefix + GriidcRoleName_ColName_Suffix)
				.setColValue(this.griidcRoleName);

		tci.getDbColumnInfo(prefix + GriidcRoleDescription_ColName_Suffix)
				.setColValue(this.griidcRoleDescription);
		
		tci.getDbColumnInfo(prefix + GriidcRole_RIS_ID_ColNameSuffix)
				.setColValue(String.valueOf(this.griidcRoleRisId));

		return tci.getDbColumnInfo();
	}

	/**
	 * get the column information for all the columns except 
	 * the key which is generated by a sequence
	 * @param prefix
	 * @return
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 */
	private DbColumnInfo[] getInsertDbColumnInfo(String prefix) 
			throws SQLException, ClassNotFoundException {
		DbColumnInfo[] dbciArray = getDbColumnInfo(prefix);
		String targetColName = prefix + GriidcRole_Number_ColName_Suffix;
		DbColumnInfo[] insertColInfo = new DbColumnInfo[dbciArray.length -1];
		int i = 0;
		for(DbColumnInfo dbci : dbciArray) {
			if(!(dbci.getColName().equals(targetColName))) {
				insertColInfo[i++] = dbci;
			}
		}
		return insertColInfo;
	}
	private DbColumnInfo[] getWhereColumnInfo(String prefix)
			throws SQLException, ClassNotFoundException {
		String tableName = getGriidcTableNameFromPrefix(prefix);
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(
				this.griidcDbConnection, tableName);

		DbColumnInfo dbci = tci.getDbColumnInfo(prefix
				+ GriidcRole_Number_ColName_Suffix);

		dbci.setColValue(String.valueOf(this.griidcRoleNumber));
		DbColumnInfo[] whereColInfo = new DbColumnInfo[1];
		whereColInfo[0] = dbci;
		return whereColInfo;
	}

	

	public String griidcRoleToString(String prefix) {
		return "GRIIDC " + prefix + "Role [roleNumber=" + griidcRoleNumber
				+ ", roleName=" + griidcRoleName + ", griidcRoleDescription="
				+ griidcRoleDescription + "]";

	}

	public String risRoleToString() {
		return "RIS Role [Role_ID=" + risRoleId + ", Role_Name=" + risRoleName
				+ "]";

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

	public int getRisRecordErrors() {
		return risRecordErrors;
	}

	public int getGriidcTaskRoleDuplicates() {
		return griidcTaskRoleDuplicates;
	}

	public int getGriidcProjRoleDuplicates() {
		return griidcProjRoleDuplicates;
	}

	public int getGriidcTaskRoleAdded() {
		return griidcTaskRoleAdded;
	}

	public int getGriidcTaskRoleModified() {
		return griidcTaskRoleModified;
	}

	public int getGriidcProjRoleAdded() {
		return griidcProjRoleAdded;
	}

	public int getGriidcProjRoleModified() {
		return griidcProjRoleModified;
	}

	private String countFormat = "%n %6d %6d %6d %6d %6d %6d %6d %6d %6d";
	private String countHeaderFormat = "%n %6s %6s %6s %6s %6s %6s %6s %6s %6s";
	private boolean firstTime = true;

	private void reportTheCounts() {
		if (RolesSynchronizer.isDebug()) {
			//if (firstTime) {
				System.out
						.printf(countHeaderFormat, "read", "err",
								"tadd", "tmod", "tdup", "radd", "rmod", "rdup",
								"total");
			//	firstTime = false;
			//}
			int total = griidcTaskRoleAdded + griidcTaskRoleModified
					+ griidcTaskRoleDuplicates + griidcProjRoleAdded
					+ griidcProjRoleModified + griidcProjRoleDuplicates;
			System.out.printf(countFormat, risRecordCount,
					risRecordErrors, griidcTaskRoleAdded,
					griidcTaskRoleModified, griidcTaskRoleDuplicates,
					griidcProjRoleAdded, griidcProjRoleModified,
					griidcProjRoleDuplicates, total);
		}
	}

	public static void main(String[] args) {
		System.out.println("RolesSynchronizer.main() - Start -");
		RolesSynchronizer.setDebug(false);
		RandomBoolean.getInstance().off();
		RolesSynchronizer roleSynchronizer = new RolesSynchronizer();
		try {
			roleSynchronizer.syncGriidcRolesFromRisRoles();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (TableNotInDatabaseException e) {
			System.err.println(e.getMessage());
		}

		String pFormat = "%-44s %10d%n";
		String titleFormat = "%n*****************************  %-40s  ********************************%n";
		String title = "RIS Institutions to GRIIDC Institution";

		System.out.println("\n\nRisToGriidcMain finished");

		System.out.printf(titleFormat, title);

		System.out.printf(pFormat, "RIS Roles records read:",
				roleSynchronizer.getRisRecordCount());
		System.out.printf(pFormat, "RIS Role errors:",
				roleSynchronizer.getRisRecordErrors());

		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");

		System.out.printf(pFormat, "GRIIDC TaskRole added:",
				roleSynchronizer.getGriidcTaskRoleAdded());
		System.out.printf(pFormat, "GRIIDC TaskRole modified:",
				roleSynchronizer.getGriidcTaskRoleModified());
		System.out.printf(pFormat, "GRIIDC TaskRole duplicates:",
				roleSynchronizer.getGriidcTaskRoleDuplicates());
		System.out.printf(pFormat, "GRIIDC ProjRole added:",
				roleSynchronizer.getGriidcProjRoleAdded());
		System.out.printf(pFormat, "GRIIDC ProjRole modified:",
				roleSynchronizer.getGriidcProjRoleModified());
		System.out.printf(pFormat, "GRIIDC ProjRole duplicates:",
				roleSynchronizer.getGriidcProjRoleDuplicates());

		System.out.println("RolesSynchronizer.main() - END -");
	}
}
