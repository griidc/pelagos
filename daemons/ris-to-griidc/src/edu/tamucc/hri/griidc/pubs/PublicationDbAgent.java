package edu.tamucc.hri.griidc.pubs;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collections;
import java.util.SortedSet;
import java.util.TreeSet;

import javax.xml.bind.JAXBException;

import org.xml.sax.SAXParseException;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PubNotFoundInRefBaseException;
import edu.tamucc.hri.griidc.exception.FileNotXmlException;
import edu.tamucc.hri.griidc.rdbms.DbColumnInfo;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnectionFactory;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsPubsUtils;
import edu.tamucc.hri.griidc.rdbms.RisGriidcDataStoreInterface;
import edu.tamucc.hri.griidc.rdbms.RisGriidcRelationalDataStore;
import edu.tamucc.hri.griidc.rdbms.TableColInfo;
import edu.tamucc.hri.griidc.utils.MiscUtils;
import edu.tamucc.hri.griidc.utils.ProgressSpinner;
import edu.tamucc.hri.griidc.xml.PubsJaxbHandler;

public class PublicationDbAgent {

	private static String PubNumberColName = RdbmsConstants.GriidcPublication_Number_ColName;
	private static String PubAuthorsColName = RdbmsConstants.GriidcPublication_Authors_ColName;
	private static String PubTitleColName = RdbmsConstants.GriidcPublication_Title_ColName;
	private static String PubJournalNameColName = RdbmsConstants.GriidcPublication_JournalName_ColName;
	private static String PubYearColName = RdbmsConstants.GriidcPublication_Year_ColName;
	private static String PubAbstractColName = RdbmsConstants.GriidcPublication_Abstract_ColName;
	private static String PubDoiColName = RdbmsConstants.GriidcPublication_DOI_ColName;
	private static RdbmsConnection griidcDbConn = null;

	private static boolean DeBug = false;

	private int pubsAdded = 0;
	private int pubsModified = 0;
	private int duplicatePubs = 0;
	private int pubSerialIdsProccessed = 0;
	private int pubNumbersNotFoundInRefBase = 0;
	private int pubsErrors = 0;

	private RisGriidcDataStoreInterface risDataStore = new RisGriidcRelationalDataStore();
	private RefBaseWebService webService = new RefBaseWebService();
	private PubsJaxbHandler xmlHandler = new PubsJaxbHandler();
	private XmlPreprocessor xmlPreprocessor = null;

	private SortedSet<Integer> storedPublicationNumbers = Collections
			.synchronizedSortedSet(new TreeSet<Integer>());

	public PublicationDbAgent() {
		// TODO Auto-generated constructor stub
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	private void debugOut(String msg) {
		if (PublicationDbAgent.isDeBug()) {
			System.out.println("PublicationDbAgent." + msg);
		}
	}

	public static int errorCount = 0;

	public void updateAllPublications(int[] allPubs) {
		Publication publication = null;
        ProgressSpinner spinner = new ProgressSpinner();
		for (int pubSerial : allPubs) {
			spinner.spin();
			this.debugOut("updateAllPublications() - processing publication number: "
					+ pubSerial);
			this.pubSerialIdsProccessed++;
			try {
				publication = this
						.createPublicationFromRefBaseWebService(pubSerial);
				// is this Publication suitable for inclusion in the GRIIDC
				// database??
				if (publication.isValidForGriidcDb()) {

					try {
						this.updatePublication(publication);
					} catch (SQLException e) {
						this.pubsErrors++;
						String msg = "Error in Pub serial number: " + pubSerial
								+ "  " + e.getMessage();
						MiscUtils.writeToErrorLogFile(msg);
						if (PubsToGriidcMain.isDeBug()) {
							System.out.println(msg);
							// e.printStackTrace();
						}
					}
				} else { // NOT ValidForGriidcDb
					MiscUtils.writeToErrorLogFile(publication
							.getGriidcDBValidityErrorMessage());
					this.pubsErrors++;
				}
			} catch (PubNotFoundInRefBaseException e) {
				MiscUtils.writeToErrorLogFile(e.getMessage());
			}
		}
	}

	public Publication createPublicationFromRefBaseWebService(int serialNumber)
			throws PubNotFoundInRefBaseException {
		String fileName = null;
		Publication publication = null;

		try {
			try {
				fileName = webService.getRefBaseXmlResponse(serialNumber);
				this.xmlPreprocessor = new XmlPreprocessor(fileName);
				this.xmlPreprocessor.processFile();
				publication = this.xmlHandler.processRefBaseXmlFile(fileName);
				publication.setNumber(serialNumber);
				return publication;
			} catch (JAXBException e) {
				// publication serial number not found in REF BASE
				this.pubsErrors++;
				this.pubNumbersNotFoundInRefBase++;
				throw new PubNotFoundInRefBaseException("Publication Number "
						+ serialNumber + " was not found in REF-BASE");
			} catch (FileNotXmlException e) {
				// an HTML file was returned from refbase - publication serial number not found in REF BASE
				this.pubsErrors++;
				this.pubNumbersNotFoundInRefBase++;
				throw new PubNotFoundInRefBaseException("Publication Number "
						+ serialNumber + " was not found in REF-BASE");
			}
		} catch (IOException e) {
			System.err
					.println("PublicationDbAgent.createPublicationFromRefBaseWebService() serialNumber "
							+ serialNumber);
			e.printStackTrace();
			System.exit(-1);
		}
		return publication;
	}

	public String concatinateLinkedExceptionMessages(JAXBException jaxbEx) {
		String msg = "";
		if (jaxbEx.getMessage() != null) {
			msg = jaxbEx.getMessage() + "; ";
		}
		StringBuffer msgBuffer = new StringBuffer(msg);
		SAXParseException saxEx = null;
		if (jaxbEx.getLinkedException() instanceof SAXParseException) {
			saxEx = (SAXParseException) jaxbEx.getLinkedException();
			msgBuffer.append(saxEx.getMessage());
			msgBuffer.append(" line number: ");
			msgBuffer.append(saxEx.getLineNumber());
			msgBuffer.append("; column number: ");
			msgBuffer.append(saxEx.getColumnNumber());
		}
		return msgBuffer.toString();
	}

	public boolean updatePublication(Publication pub) throws SQLException {
		Publication storedPub = null;
		boolean status = false;

		try {
			storedPub = this.findPublication(pub.getSerialNumber());
			// debugOut("updatePublication() found it - is it equal?");
			if (pub.equals(storedPub)) {
				this.duplicatePubs++;
				status = true;
			} else {
				// debugOut("updatePublication() NOT equal - modify it");
				status = this.modifyPublication(pub);
				this.pubsModified++;
			}
		} catch (NoRecordFoundException e) {
			// debugOut("updatePublication() NOT found - ADD it");
			status = this.addPublication(pub);
			this.pubsAdded++;
		}
		return status;
	}

	private boolean modifyPublication(Publication pub) throws SQLException {
		String query = formatModifyQuery(pub);
		// this.debugOut("modifyPublication(); query: " + query);
		return PublicationDbAgent.getGriidcDbConnection().executeQueryBoolean(
				query);
	}

	private boolean addPublication(Publication pub) throws SQLException {
		String query = formatAddQuery(pub);
		// this.debugOut("addPublication(); query: " + query);
		return PublicationDbAgent.getGriidcDbConnection().executeQueryBoolean(
				query);
	}

	/**
	 * look for a Publication record in the GRIIDC Database and if found
	 * instantiate and return a Publication object
	 * 
	 * @param pubSerialNumber
	 * @return
	 * @throws NoRecordFoundException
	 * @throws SQLException
	 */
	private Publication findPublication(int pubSerialNumber)
			throws NoRecordFoundException, SQLException {
		// this.debugOut("PublictionDbAgent.findPublication(" + pubSerialNumber
		// + ")");
		String query = "SELECT * FROM "
				+ RdbmsConnection
						.wrapInDoubleQuotes(RdbmsConstants.GriidcPublicationTableName)
				+ "WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(PubNumberColName)
				+ RdbmsConstants.EqualSign + pubSerialNumber;
		ResultSet rs = PublicationDbAgent.getGriidcDbConnection()
				.executeQueryResultSet(query);
		int count = 0;
		Publication pub = new Publication();
		while (rs.next()) {
			count++;
			pub.setSerialNumber(rs.getInt(PubNumberColName));
			pub.setAuthor(rs.getString(PubAuthorsColName));
			pub.setTitle(rs.getString(PubTitleColName));
			pub.setPublisher(rs.getString(PubJournalNameColName));
			pub.setPublicationYear(rs.getInt(PubYearColName));
			pub.setAbstract(rs.getString(PubAbstractColName));
			pub.setDoi(rs.getString(PubDoiColName));
		}
		if (count == 0) {
			throw new NoRecordFoundException(
					"No Publication record found for publication serial number: "
							+ pubSerialNumber);
		}
		// this.debugOut("PublictionDbAgent.findPublication(" + pubSerialNumber
		// + ") returning - count is: " + count);
		return pub;
	}

	private String formatAddQuery(Publication pub) throws SQLException {

		DbColumnInfo[] info = getDbColumnInfo(pub);
		String query = RdbmsPubsUtils.formatInsertStatement(
				RdbmsConstants.GriidcPublicationTableName, info);
		return query;

	}

	private String formatModifyQuery(Publication pub) throws SQLException {

		DbColumnInfo[] info = getDbColumnInfo(pub);
		DbColumnInfo[] whereInfo = new DbColumnInfo[1];

		TableColInfo tci = RdbmsPubsUtils.getMetaDataForTable(
				RdbmsPubsUtils.getGriidcDbConnectionInstance(),
				RdbmsConstants.GriidcPublicationTableName);

		whereInfo[0] = tci.getDbColumnInfo(PubNumberColName);
		whereInfo[0].setColValue(pub.getSerialNumber());

		// this.debugOut("formatModifyQuery() modify clause info: \n"
		// + DbColumnInfo.toString(info));
		// this.debugOut("formatModifyQuery() where clause info: \n"
		// + DbColumnInfo.toString(whereInfo));

		String query = RdbmsPubsUtils.formatUpdateStatement(
				RdbmsConstants.GriidcPublicationTableName, info, whereInfo);
		return query;
	}

	private DbColumnInfo[] getDbColumnInfo(Publication pub) throws SQLException {
		TableColInfo tci = RdbmsPubsUtils.getMetaDataForTable(
				getGriidcDbConnection(),
				RdbmsConstants.GriidcPublicationTableName);

		tci.getDbColumnInfo(PubNumberColName).setColValue(
				String.valueOf(pub.getNumber()));
		tci.getDbColumnInfo(PubAuthorsColName).setColValue(
				String.valueOf(pub.getAuthorsString()));
		tci.getDbColumnInfo(PubTitleColName).setColValue(pub.getTitle());
		tci.getDbColumnInfo(PubJournalNameColName).setColValue(
				pub.getPublisher());
		tci.getDbColumnInfo(PubYearColName).setColValue(
				pub.getPublicationYear());
		tci.getDbColumnInfo(PubAbstractColName).setColValue(pub.getAbstract());
		tci.getDbColumnInfo(PubDoiColName).setColValue(pub.getDoi());
		return tci.getDbColumnInfo();
	}

	public static RdbmsConnection getGriidcDbConnection() {

		try {
			if (PublicationDbAgent.griidcDbConn == null) {
				PublicationDbAgent.griidcDbConn = RdbmsConnectionFactory
						.getGriidcDbConnectionInstance();
			}
		} catch (SQLException e) {
			MiscUtils.fatalError("PublicationDbLayer", "getDbConnection",
					e.getMessage());
		}
		return PublicationDbAgent.griidcDbConn;
	}

	public int getPubsAdded() {
		return pubsAdded;
	}

	public int getPubsModified() {
		return pubsModified;
	}

	public int getDuplicatePubs() {
		return duplicatePubs;
	}

	public int getPubsErrors() {
		return this.pubsErrors;
	}

	public int getPubSerialIdsProccessed() {
		return pubSerialIdsProccessed;
	}
	public int getPubNumbersNotFoundInRefBase() {
		return pubNumbersNotFoundInRefBase;
	}
	
}
