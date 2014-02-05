package edu.tamucc.hri.griidc;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import javax.mail.internet.AddressException;
import javax.mail.internet.InternetAddress;

import edu.tamucc.hri.griidc.exception.DuplicateRecordException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

/***
 * Deprecated - Patrick decided to make this
 * a view. No table
 * @author jvh
 *
 */
public class EmailInfoGomriPersonInstitutionSynchronizer {

	public EmailInfoGomriPersonInstitutionSynchronizer() {
		// TODO Auto-generated constructor stub
	}

	private static final String GriidcTableName = "EmailInfo-GoMRIPerson-Institution";
	private static final String PersonNumberCol = "Person_Number";
	private static final String EmailInfoNumberCol = "EmailInfo_Number";
	private static final String InstitutionNumberCol = "Institution_Number";
	private RdbmsConnection griidcDbConnection = null;
	private boolean initialized = false;
	private static boolean Debug = false;

	public void initializeStartUp() throws IOException,
			PropertyNotFoundException, SQLException, ClassNotFoundException {
		if (!initialized) {
			this.griidcDbConnection = RdbmsUtils
					.getGriidcDbConnectionInstance();
			initialized = true;
		}
	}

	public boolean update(int emailInfoNumber,
			int institutionNumber, int personNumber ) throws DuplicateRecordException, SQLException,
			ClassNotFoundException {

		int count = 0;
		ResultSet rs = this.find(personNumber);
		while (rs.next()) {
			count++;
		}
		this.validate( );
		if (count > 1) {
			throw new DuplicateRecordException(
					"ERROR updateing GRIIDC - there are " + count + " "
							+ GriidcTableName + " records with "
							+ PersonNumberCol + " equal to " + personNumber);

		}
		if (count == 0) {
			this.add(emailInfoNumber, institutionNumber, personNumber);
		} else if (count == 1) {
			this.modify(emailInfoNumber, institutionNumber, personNumber);
		} 

		return true;
	}

	private boolean validate( ) {
		return true;
	}
	

	public ResultSet find(int personNumber) throws SQLException,
			ClassNotFoundException {

		String query = this.formatSelect(personNumber);
		return griidcDbConnection.executeQueryResultSet(query);
	}

	private String formatSelect(int personNumber) {
		String query = "SELECT * FROM "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(PersonNumberCol)
				+ RdbmsUtils.EqualSign + personNumber;
		return query;
	}

	public boolean add(int emailInfoNumber,int institutionNumber, int personNumber)
			throws SQLException, ClassNotFoundException {
		String query = formatInsertStatement(emailInfoNumber, institutionNumber, personNumber);
		boolean status = this.griidcDbConnection.executeQueryBoolean(query);
		if (EmailInfoGomriPersonInstitutionSynchronizer.isDebug())
			System.out.println("ADDED EMAIL "
					+ formatData(emailInfoNumber, institutionNumber, personNumber));
		return status;
	}

	public static String formatData(int emailInfoNumber,int institutionNumber, int personNumber) {
		return "EmailInfo_Number: " + emailInfoNumber + ", Institution_Number: " + institutionNumber
				+ ", Person_Number: " + personNumber;
	}

	private String formatInsertStatement(int emailInfoNumber,int institutionNumber, int personNumber) {
		String query = "INSERT INTO "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ RdbmsUtils.SPACE + "("
				+ RdbmsConnection.wrapInDoubleQuotes(EmailInfoNumberCol)
				+ RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(InstitutionNumberCol)
				+ RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(PersonNumberCol)
				
				+ ") VALUES (" 
				+ emailInfoNumber + RdbmsUtils.CommaSpace
				+ institutionNumber + RdbmsUtils.CommaSpace
				+ personNumber
				+ " )";
		return query;
	}

	public boolean modify(int emailInfoNumber,int institutionNumber, int personNumber)
			throws SQLException, ClassNotFoundException {
		String query = formatModifyStatement(emailInfoNumber, institutionNumber, personNumber);
		boolean status = this.griidcDbConnection.executeQueryBoolean(query);
		if (EmailInfoGomriPersonInstitutionSynchronizer.isDebug())
			System.out.println("MODIFIED EMAIL "
					+ formatData(emailInfoNumber, institutionNumber, personNumber));
		return status;
	}

	private String formatModifyStatement(int emailInfoNumber,int institutionNumber, int personNumber) {
		String query = "UPDATE  "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ RdbmsUtils.SPACE + " SET "
				+ RdbmsConnection.wrapInDoubleQuotes(EmailInfoNumberCol)
				+ RdbmsUtils.EqualSign + emailInfoNumber
				
				+ RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(InstitutionNumberCol)
				+ RdbmsUtils.EqualSign + institutionNumber
				
				+ RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(PersonNumberCol)
				+ RdbmsUtils.EqualSign + personNumber
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(PersonNumberCol)
				+ RdbmsUtils.EqualSign + personNumber;
		return query;
	}

	public static boolean isDebug() {
		return EmailInfoGomriPersonInstitutionSynchronizer.Debug;
	}

	public static void setDebug(boolean db) {
		EmailInfoGomriPersonInstitutionSynchronizer.Debug = db;
	}

	public static void main(String[] args) {

		

	}

	
}
