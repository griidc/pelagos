package edu.tamucc.hri.griidc.ris;

import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.SynchronizerBase;

public class GomriPersonAgent  extends SynchronizerBase {

	public static final String TableName = RdbmsConstants.GriidcGomriPersonTableName;
	private static final String Person_NumberColName = "Person_Number";
	private static final String GoMRIPerson_DisplayNameColName = "GoMRIPerson_DisplayName";
	private static final String GoMRIPerson_ResearchInterestColName = "GoMRIPerson_ResearchInterest";
	private static final String GoMRIPerson_TitleColName = "GoMRIPerson_Title";
	
	
	private int gpPerson_Number = -1;
	private String gpDisplayName = null;
	private String gpResearchInterest  = null;
	private String gpTitle  = null;
	
	private boolean initialized = false;
	
	private int recordsAdded = 0;
	//private int recordsModified = 0;
	
	public GomriPersonAgent() {
		
	}
    
	public boolean isInitialized() {
		return initialized;
	}

	public void initialize() {
		if(initialized) return;
		super.commonInitialize();
		this.initialized = true;
		return;
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
	public int readGomriPerson(int personNumber)
			throws SQLException, NoRecordFoundException {
		
		String query = "SELECT * FROM "
				+ RdbmsConnection
						.wrapInDoubleQuotes(GomriPersonAgent.TableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(Person_NumberColName)
				+ RdbmsConstants.EqualSign + personNumber;
		
		ResultSet crs = this.griidcDbConnection.executeQueryResultSet(query);
		int count = 0;
		while (crs.next()) {
			count++;
			this.gpPerson_Number = crs.getInt(Person_NumberColName);
			this.gpDisplayName = crs.getString(GoMRIPerson_DisplayNameColName);
			this.gpResearchInterest = crs.getString(GoMRIPerson_ResearchInterestColName);
			this.gpTitle = crs.getString(GoMRIPerson_TitleColName);
		}
		if (count == 0)
			throw new NoRecordFoundException("NoRecordFoundException: No match found for " + this.Person_NumberColName + " = " + personNumber + " in table " + GomriPersonAgent.TableName );
		return this.gpPerson_Number;
	}
	
	
	private String formatInsertStatement(int personNumber,String displayName, String researchInterest, String title) {
		String query = "INSERT INTO "
				+ RdbmsConnection.wrapInDoubleQuotes(GomriPersonAgent.TableName)
				+ RdbmsConstants.SPACE + "("
				+ RdbmsConnection.wrapInDoubleQuotes(Person_NumberColName)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(GoMRIPerson_DisplayNameColName)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(GoMRIPerson_ResearchInterestColName)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(GoMRIPerson_TitleColName)
				
				+ ") VALUES (" 
				+ personNumber + RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(displayName) + RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(researchInterest) + RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(title)
				+ " )";
		return query;
	}
	
	public boolean updateGoMRIPerson(int personNumber,String firstName, String middleName, String lastName, String researchInterest, String title) throws SQLException {
		this.initialize();
		boolean status = false;
		try {
			this.readGomriPerson(personNumber);
			return true;
		}  catch (NoRecordFoundException e) {
			status = this.addGoMRIPerson(personNumber, firstName, middleName, lastName, researchInterest, title);
		}
		return status;
	}
	private boolean addGoMRIPerson(int personNumber,String firstName, String middleName, String lastName, String researchInterest, String title)
			throws SQLException {
		String displayName = firstName;
		if(middleName != null && middleName.length() > 0) {
			displayName += (" " + middleName);
		}
		displayName += (" " + lastName);
		String lTitle = title;
		if(title == null || title.length() > 0) {
			lTitle = " ";
		}
        String addQuery = this.formatInsertStatement(personNumber, displayName, researchInterest, lTitle);
		
	    boolean status = this.griidcDbConnection.executeQueryBoolean(addQuery);
	    this.recordsAdded++;
	    return status;
	}

	public int getGpPerson_Number() {
		return gpPerson_Number;
	}

	public String getGpDisplayName() {
		return gpDisplayName;
	}

	public String getGpResearchInterest() {
		return gpResearchInterest;
	}

	public String getGpTitle() {
		return gpTitle;
	}

	public int getRecordsAdded() {
		return recordsAdded;
	}
}
