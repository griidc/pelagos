package edu.tamucc.hri.griidc.pubs;

import java.io.ByteArrayOutputStream;
import java.io.PrintStream;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.GriidcProjectFundingEnvelopeMap;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsPubsUtils;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public class ProjectPublicationDbAgent 
{

	
    private final static String TableName = RdbmsConstants.GriidcProjectPublicationTableName;
	
    private final static String ProjectNumberColName = RdbmsConstants.GriidcProjectPublication_ProjectNumber_ColName;
    private final static String FundingEnvelopeCycleColName = RdbmsConstants.GriidcProjectPublication_FundingEnvelopeCycle_ColName;
    private final static String PublicationNumberColName = RdbmsConstants.GriidcProjectPublication_PublicationNumber_ColName;
	private RdbmsConnection dbCon = null;
	private GriidcProjectFundingEnvelopeMap projectFundingEnvelopeMap = GriidcProjectFundingEnvelopeMap.getInstance();
	
	private int recordsRead = 0;
	private int recordsAdded = 0;
	private int duplicateRecords = 0;
	private int errors = 0;
	
	public static boolean DeBug = false;
	
	public ProjectPublicationDbAgent() {
		// TODO Auto-generated constructor stub
	}
	
	private void initialize() throws SQLException {
		if(this.dbCon == null) {
			this.dbCon = RdbmsPubsUtils.getGriidcDbConnectionInstance();
		}
	}
	/**
	 * 
	 * risPeoplePubs is an array of all PeoplePublication records from the RIS database.
	 * for each record ...
	 * if it exists in GRIIDC and is equal ( existence is equality since there are only two fields and
	 * they are the concatenated key) do nothing.
	 * if it does not exist in GRIIDC add it
	 * 
	 * @param risPeoplePubs
	 * @return
	 */
	public boolean updateGriidcProjectPublication(RisProjPub[] projPubs) {
		int risProgramId = -1;
		int risPubSerialId = -1;
		//  GRIIDC nouns
		int projectNumber = -1;
		String fundingEnvelopeCycle = null;
		int pubNumber = -1;
		for(RisProjPub rpp : projPubs) {
			this.recordsRead++;
			projectNumber = risProgramId = rpp.getProgramId();
			pubNumber = risPubSerialId = rpp.getPublicatonSerialNum();
			try {
				fundingEnvelopeCycle = projectFundingEnvelopeMap.getFundingEnvelopeNumber(projectNumber);
				try {
					if(findGriidcProjectPublication(projectNumber,fundingEnvelopeCycle,pubNumber)) {
						this.duplicateRecords++;
					} else {
						addGriidcProjectPublication(projectNumber,fundingEnvelopeCycle,pubNumber);
						this.recordsAdded++;
					}
				} catch (SQLException e) {
					errorMessage(e.getMessage());
					errors++;
				}
			} catch (NoRecordFoundException e) {
				String msg = "ProjectPublictionDbAgent: There is no GRIIDC FundingEnvelope_Cycle for GRIIDC Project " + projectNumber;
		        this.errorMessage(msg);
		        errors++;
			}
			
		}
		return false;
	}
	
	private boolean findGriidcProjectPublication(int projectNumber, String fundingEnvelopeCycle, int pubNumber) throws SQLException {
		initialize();
		boolean status = false;
		String query = formatFindQuery(projectNumber, fundingEnvelopeCycle, pubNumber);
        this.debugMessage("ProjectPublicationDbAgent.findGriidcProjectPublication() \n" + 
		     this.formatPayload(projectNumber, fundingEnvelopeCycle, pubNumber) + "\n" +
        		"query: " + query);
		ResultSet rs = dbCon.executeQueryResultSet(query);
		int count = 0;
		//int personN = -1;
		//int pubN = -1;
		while(rs.next()) {
		//	personN = rs.getInt(PersonNumberColName);
		//	pubN = rs.getInt(PublicationNumberColName);
			count++;
		}
		if(count > 0) {
			status = true;
		    this.debugMessage("Found " + formatPayload(projectNumber, fundingEnvelopeCycle, pubNumber));
		}
		return status;
	}
	
	private String formatFindQuery(int projectNumber, String fundingEnvelopeCycle, int pubNumber) {
		return "SELECT * FROM " + RdbmsConnection.wrapInDoubleQuotes(TableName) + 
				" WHERE " + 
				RdbmsConnection.wrapInDoubleQuotes(ProjectNumberColName) + RdbmsConstants.EqualSign + projectNumber +
				RdbmsConstants.And +
				RdbmsConnection.wrapInDoubleQuotes(FundingEnvelopeCycleColName) + RdbmsConstants.EqualSign + RdbmsConnection.wrapInSingleQuotes(FundingEnvelopeCycleColName) +
				RdbmsConstants.And +
				RdbmsConnection.wrapInDoubleQuotes(PublicationNumberColName) + RdbmsConstants.EqualSign + pubNumber;
	}
	private boolean addGriidcProjectPublication(int projectNumber, String fundingEnvelopeCycle, int pubNumber) throws SQLException {
		String query = formatAddQuery(projectNumber, fundingEnvelopeCycle, pubNumber);
		boolean status = dbCon.executeQueryBoolean(query);
		this.debugMessage("Added " + formatPayload(projectNumber, fundingEnvelopeCycle, pubNumber));
		return status;
	}
	
	private String formatPayload(int projectNumber, String fundingEnvelopeCycle, int pubNumber) {
		String format = "%nProject-Publication: Project Number: %5d,  Funding Enveleope Cycle: %10s Publication Number: %5d";
		StringBuffer sb = new StringBuffer();
		ByteArrayOutputStream outStream = new ByteArrayOutputStream();
		PrintStream ps = new PrintStream(outStream);
		ps.printf(format, projectNumber, fundingEnvelopeCycle, pubNumber);
		return outStream.toString();
	}
	
	

	private String formatAddQuery(int projectNumber, String fundingEnvelopeCycle, int pubNumber) throws SQLException {

		DbColumnInfo[] info = getDbColumnInfo(projectNumber, fundingEnvelopeCycle, pubNumber);
		String query = RdbmsPubsUtils.formatInsertStatement(TableName, info);
		return query;

	}

	private DbColumnInfo[] getDbColumnInfo(int projectNumber, String fundingEnvelopeCycle, int pubNumber) throws SQLException {
		TableColInfo tci = RdbmsPubsUtils.getMetaDataForTable(
				dbCon, TableName);

		tci.getDbColumnInfo(ProjectNumberColName).setColValue(
				String.valueOf(projectNumber));
		tci.getDbColumnInfo(FundingEnvelopeCycleColName).setColValue(
				String.valueOf(fundingEnvelopeCycle));
		tci.getDbColumnInfo(PublicationNumberColName).setColValue(
				String.valueOf(pubNumber));
		return tci.getDbColumnInfo();
	}

	public int getProjectPubsRead() {
		return this.recordsRead;
	}
	public int getRecordsAdded() {
		return this.recordsAdded;
	}

	public int getDuplicateRecords() {
		return this.duplicateRecords;
	}
	public int getErrors() {
		return this.errors;
	}

	private void errorMessage(String msg) {
		MiscUtils.writeToErrorLogFile(msg);
		debugMessage(msg);
	}
	
	private void debugMessage(String msg) {
		if(isDeBug()) System.out.println(msg);
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}
}
