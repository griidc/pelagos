package edu.tamucc.hri.griidc;

import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsConstants;

public class GomriPersonDepartmentRisIdAgent extends SynchronizerBase {

	public static final String TableName = RdbmsConstants.GriidcPersonDepartmentRisPeopleIdTableName;
	private static final String RIS_People_IDColName = "RIS_People_ID";
	private static final String Department_NumberColName = "Department_Number";
	private static final String Person_NumberColName = "Person_Number";
	private static final String RIS_ID_CommentColName = "RIS_ID_Comment";
	private boolean initialized = false;

	private int griidcRisPeopleId = -1;
	private int griidcDepartmentNumber = -1;
	private int griidcPersonNumber = -1;
	private String griidcIdComment = null;
	private int recordsAdded = 0;
	private int recordsModified = 0;
	
	public GomriPersonDepartmentRisIdAgent() {

	}

	public boolean isInitialized() {
		return initialized;
	}

	public void initialize() {
		if (initialized)
			return;
		super.commonInitialize();
		this.initialized = true;
		return;
	}

	private String formatInsertStatement(int risPeopleId,
			int griidcDepartmentNumber, int griidcPersonNumber,
			String risIdComment) {
		String query = "INSERT INTO "
				+ RdbmsConnection
						.wrapInDoubleQuotes(GomriPersonDepartmentRisIdAgent.TableName)
				+ RdbmsConstants.SPACE + "("
				+ RdbmsConnection.wrapInDoubleQuotes(RIS_People_IDColName)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(Department_NumberColName)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(Person_NumberColName)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(RIS_ID_CommentColName)
				+ ") VALUES (" + risPeopleId
				+ RdbmsConstants.CommaSpace + griidcDepartmentNumber
				+ RdbmsConstants.CommaSpace + griidcPersonNumber
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(risIdComment) + " )";
		return query;
	}

	public boolean updateGomriPersonDepartmentRisId(int risPeopleId,
			int griidcDepartmentNumber, int griidcPersonNumber,
			String risIdComment) throws SQLException {
		boolean status = false;
		try {
			this.readGomriPersonDepartmentRisId(risPeopleId,
					griidcDepartmentNumber);
			if(MiscUtils.areStringsEqual(risIdComment, this.griidcIdComment))
				status =  false;
			if(!MiscUtils.isStringEmpty(risIdComment)) {
				status =  this.modifyGomriPersonDepartmentRisId(risPeopleId, griidcDepartmentNumber, griidcPersonNumber, risIdComment);
			}
		} catch (NoRecordFoundException e) {
			status =  this.addGomriPersonDepartmentRisId(risPeopleId,
					griidcDepartmentNumber, griidcPersonNumber, risIdComment);
		}
		return status;
	}
	/**
	 * really the only thing that can change is the comment
	 * 
	 * @param risPeopleId
	 * @param griidcDepartmentNumber
	 * @param griidcPersonNumber
	 * @param risIdComment
	 * @return
	 * @throws SQLException
	 */
	private boolean modifyGomriPersonDepartmentRisId(int risPeopleId,
			int griidcDepartmentNumber, int griidcPersonNumber,
			String risIdComment) throws SQLException  {
		String modifyQuery = null;

		modifyQuery = this.formatModifyQuery(risPeopleId, griidcDepartmentNumber, griidcPersonNumber, risIdComment);
		boolean status = this.griidcDbConnection.executeQueryBoolean(modifyQuery);
		this.recordsModified++;
		return status;
	}

	private String formatModifyQuery(int risPeopleId,
			int griidcDepartmentNumber, int griidcPersonNumber,
			String risIdComment) {
		String q = "UPDATE " + RdbmsConnection.wrapInDoubleQuotes(TableName) + 
				" SET " +
				RdbmsConnection.wrapInDoubleQuotes(RIS_ID_CommentColName) + 
				RdbmsConstants.EqualSign + 
				RdbmsConnection.wrapInSingleQuotes(risIdComment) + 
				" WHERE " + 
				RdbmsConnection.wrapInDoubleQuotes(RIS_People_IDColName) + 
				RdbmsConstants.EqualSign + risPeopleId +
				RdbmsConstants.And + 
				RdbmsConnection.wrapInDoubleQuotes(Department_NumberColName) +
				RdbmsConstants.EqualSign + griidcDepartmentNumber + 
				RdbmsConstants.And +
				RdbmsConnection.wrapInDoubleQuotes(Person_NumberColName) +
				RdbmsConstants.EqualSign + griidcPersonNumber;
		return q;
	}
	private boolean addGomriPersonDepartmentRisId(int risPeopleId,
			int griidcDepartmentNumber, int griidcPersonNumber,
			String risIdComment) throws SQLException {
		String addQuery = this.formatInsertStatement(risPeopleId,
				griidcDepartmentNumber, griidcPersonNumber, risIdComment);
		
		boolean status = this.griidcDbConnection.executeQueryBoolean(addQuery);
		this.recordsAdded++;
		return status;
	}

	/**
	 * For the Person to exist it must be represented by a relationship record
	 * in the GoMRIPersonDepartmentRisId Table which connects GRIIDC Person ( by
	 * Number) to RIS People (by ID). It also connect the identified individual
	 * to a GRIIDC Department (Department Number)
	 * 
	 * @param risPeople_Id
	 * @return griidc Person_Number
	 * @throws SQLException
	 */
	public int readGomriPersonDepartmentRisId(int risPeopleId,
			int griidcDepartmentNumber)
			throws SQLException, NoRecordFoundException {
		this.initialize();
		String query = "SELECT * FROM "
				+ RdbmsConnection
						.wrapInDoubleQuotes(GomriPersonDepartmentRisIdAgent.TableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(RIS_People_IDColName)
				+ RdbmsConstants.EqualSign + risPeopleId + RdbmsConstants.And

				+ RdbmsConnection.wrapInDoubleQuotes(Department_NumberColName)
				+ RdbmsConstants.EqualSign + griidcDepartmentNumber;
		
		ResultSet crs = this.griidcDbConnection.executeQueryResultSet(query);
		int count = 0;
		while (crs.next()) {
			count++;
			this.griidcRisPeopleId = crs.getInt(RIS_People_IDColName);
			this.griidcDepartmentNumber = crs.getInt(Department_NumberColName);
			this.griidcPersonNumber = crs.getInt(Person_NumberColName);
			this.griidcIdComment = crs.getString(RIS_ID_CommentColName);
		}
		if (count == 0)
			throw new NoRecordFoundException(getMessage(risPeopleId,
					griidcDepartmentNumber, griidcPersonNumber));
		return this.griidcPersonNumber;
	}
	

	

	private String getMessage(int risPeopleId, int griidcDepartmentNumber,
			int griidcPersonNumber) {
		String msg = "No Record Found in table: "
				+ GomriPersonDepartmentRisIdAgent.TableName + "for values "
				+ RIS_People_IDColName + " = " + risPeopleId
				+ Department_NumberColName + " = " + griidcDepartmentNumber
				+ Person_NumberColName + " = " + griidcPersonNumber;
		return msg;
	}

	public int getGriidcRisPeopleId() {
		return griidcRisPeopleId;
	}

	public int getGriidcDepartmentNumber() {
		return griidcDepartmentNumber;
	}

	public int getGriidcPersonNumber() {
		return griidcPersonNumber;
	}

	public String getGriidcIdComment() {
		return griidcIdComment;
	}

	public int getRecordsAdded() {
		return recordsAdded;
	}

	public int getRecordsModified() {
		return recordsModified;
	}
}
