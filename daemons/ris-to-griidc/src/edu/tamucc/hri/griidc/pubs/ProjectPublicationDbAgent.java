package edu.tamucc.hri.griidc.pubs;

import java.io.ByteArrayOutputStream;
import java.io.PrintStream;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Set;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.GriidcProjectFundingEnvelopeMap;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public class ProjectPublicationDbAgent {

	private final static String TableName = RdbmsConstants.GriidcProjectPublicationTableName;

	private final static String ProjectNumberColName = RdbmsConstants.GriidcProjectPublication_ProjectNumber_ColName;
	private final static String FundingEnvelopeCycleColName = RdbmsConstants.GriidcProjectPublication_FundingEnvelopeCycle_ColName;
	private final static String PublicationNumberColName = RdbmsConstants.GriidcProjectPublication_PublicationNumber_ColName;
	private RdbmsConnection dbCon = null;
	private GriidcProjectFundingEnvelopeMap projectFundingEnvelopeMap = GriidcProjectFundingEnvelopeMap
			.getInstance();

	private int recordsRead = 0;
	private int recordsAdded = 0;
	private int duplicateRecords = 0;
	private int errors = 0;

	public static boolean DeBug = false;
	private PublicationCache publicationCache = new PublicationCache();
	private static final String ErrorMsgPrefix = "Error ProjPub ";

	public ProjectPublicationDbAgent() {
		// TODO Auto-generated constructor stub
	}

	private void initialize() {
		if (this.dbCon == null) {
			try {
				this.dbCon = RdbmsUtils.getGriidcDbConnectionInstance();

			} catch (SQLException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
				System.exit(-1);
				;
			}
		}
		this.publicationCache.cachePublications();
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
	public void updateGriidcProjectPublication(RisProjPub[] projPubs) {
		initialize();
		int projectNumber = -1;
		int pubSerialNumber = -1;
		int projPubId = -1;
		
		String recIdMsg = null;
		String fundingEnvelopeCycle = null;
		for (RisProjPub rpp : projPubs) {
			this.recordsRead++;
			projectNumber = rpp.getProgramId();
			pubSerialNumber = rpp.getPublicatonSerialNum();
			projPubId = rpp.getProjPubId();
			recIdMsg = formatRecord(projPubId,projectNumber,pubSerialNumber);
			
			try {
				fundingEnvelopeCycle = projectFundingEnvelopeMap
						.getFundingEnvelopeNumber(projectNumber);
			} catch (NoRecordFoundException e) {
				String msg = " There is no GRIIDC FundingEnvelope_Cycle for GRIIDC Project "
						+ projectNumber;
				this.errorMessage(ErrorMsgPrefix + "1: " + recIdMsg + msg);
				errors++;
				continue;
			}
			try {
				findGriidcProjectPublication(fundingEnvelopeCycle, projectNumber,
						 pubSerialNumber);
				this.duplicateRecords++;
			} catch (NoRecordFoundException e) {
				if(this.publicationCache
						.findPublicationSerialNumber(pubSerialNumber) == null) {
					String msg = ErrorMsgPrefix + "2: " + recIdMsg + " Pub Serial# "
							+ pubSerialNumber + " does not exist";
					this.errorMessage(msg);
					errors++;
				} else {
					try {
						addGriidcProjectPublication(fundingEnvelopeCycle, projectNumber,
								 pubSerialNumber);
						this.recordsAdded++;
					} catch (SQLException e1) {
						String msg = ErrorMsgPrefix + "3: " + recIdMsg +  " - "
								+ e1.getMessage();
						this.errorMessage(msg);
						errors++;
					}
				}
			}
		}
		return;
	}

	private boolean findGriidcProjectPublication(String fundingEnvelopeCycle,int projectNumber,
			 int pubNumber)
			throws NoRecordFoundException {
		String query = formatFindQuery(fundingEnvelopeCycle, projectNumber, 
				pubNumber);
		this.debugMessage("ProjectPublicationDbAgent.findGriidcProjectPublication() \n"
				+ this.formatPayload(fundingEnvelopeCycle, projectNumber,
						pubNumber) + "\n" + "query: " + query);
		int count = 0;
		try {
			ResultSet rs = dbCon.executeQueryResultSet(query);
			while (rs.next()) {
				count++;
			}
		} catch (SQLException e) {
			throw new NoRecordFoundException("No Record Found "
					+ formatPayload(fundingEnvelopeCycle, projectNumber,
							pubNumber));
		}
		if (count <= 0) {
			throw new NoRecordFoundException("No Record Found "
					+ formatPayload(fundingEnvelopeCycle, projectNumber,
							pubNumber));
		}
		this.debugMessage("Found "
				+ formatPayload(fundingEnvelopeCycle, projectNumber, pubNumber));

		return true;
	}

	private String formatFindQuery(String fundingEnvelopeCycle,  int projectNumber,
			int pubNumber) {
		return "SELECT * FROM "
				+ RdbmsConnection.wrapInDoubleQuotes(TableName)
				+ " WHERE "
				+ RdbmsConnection
						.wrapInDoubleQuotes(FundingEnvelopeCycleColName)
				+ RdbmsConstants.EqualSign
				+ RdbmsConnection
						.wrapInSingleQuotes(fundingEnvelopeCycle)
				+ RdbmsConstants.And
				+ RdbmsConnection.wrapInDoubleQuotes(ProjectNumberColName)
				+ RdbmsConstants.EqualSign
				+ projectNumber
				+ RdbmsConstants.And
				+ RdbmsConnection.wrapInDoubleQuotes(PublicationNumberColName)
				+ RdbmsConstants.EqualSign + pubNumber;
	}

	private boolean addGriidcProjectPublication(String fundingEnvelopeCycle, int projectNumber,
			int pubNumber) throws SQLException {
		String query = formatAddQuery(fundingEnvelopeCycle, projectNumber,
				pubNumber);
		boolean status = dbCon.executeQueryBoolean(query);
		this.debugMessage("Added "
				+ formatPayload(fundingEnvelopeCycle, projectNumber, pubNumber));
		return status;
	}

	private String formatPayload(String fundingEnvelopeCycle, int projectNumber,
			int pubNumber) {
		String format = "%nProject-Publication: Funding Enveleope Cycle: %10s, Project Number: %5d, Publication Number: %5d";
		StringBuffer sb = new StringBuffer();
		ByteArrayOutputStream outStream = new ByteArrayOutputStream();
		PrintStream ps = new PrintStream(outStream);
		ps.printf(format, fundingEnvelopeCycle, projectNumber, pubNumber);
		return outStream.toString();
	}
	
	private String formatRecord(int projPubId,int projectNumber,int pubSerialNumber) {
		String format = "Rec ID: %4d, Proj #: %3d, Pub #: %4d";
		StringBuffer sb = new StringBuffer();
		ByteArrayOutputStream outStream = new ByteArrayOutputStream();
		PrintStream ps = new PrintStream(outStream);
		ps.printf(format, projPubId,projectNumber,pubSerialNumber);
		return outStream.toString();
	}

	private String formatAddQuery(String fundingEnvelopeCycle, int projectNumber, int pubNumber) throws SQLException {

		DbColumnInfo[] info = getDbColumnInfo(fundingEnvelopeCycle, projectNumber,
				pubNumber);
		String query = RdbmsUtils.formatInsertStatement(TableName, info);
		return query;

	}

	private DbColumnInfo[] getDbColumnInfo(String fundingEnvelopeCycle, int projectNumber,
			int pubNumber) throws SQLException {
		TableColInfo tci = RdbmsUtils.getMetaDataForTable(dbCon, TableName);

		tci.getDbColumnInfo(FundingEnvelopeCycleColName).setColValue(
				String.valueOf(fundingEnvelopeCycle));
		tci.getDbColumnInfo(ProjectNumberColName).setColValue(
				String.valueOf(projectNumber));
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
}
