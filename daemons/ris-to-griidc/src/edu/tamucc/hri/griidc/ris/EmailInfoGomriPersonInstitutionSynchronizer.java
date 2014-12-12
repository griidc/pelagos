package edu.tamucc.hri.griidc.ris;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import javax.mail.internet.AddressException;
import javax.mail.internet.InternetAddress;

import edu.tamucc.hri.griidc.exception.MultipleRecordsFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.SynchronizerBase;

/***
 * Deprecated - Patrick decided to make this
 * a view. No table
 * @author jvh
 *
 */
public class EmailInfoGomriPersonInstitutionSynchronizer extends SynchronizerBase {

	public EmailInfoGomriPersonInstitutionSynchronizer() {
		// TODO Auto-generated constructor stub
	}

	private static final String GriidcTableName = "EmailInfo-GoMRIPerson-Institution";
	private static final String PersonNumberCol = "Person_Number";
	private static final String EmailInfoNumberCol = "EmailInfo_Number";
	private static final String InstitutionNumberCol = "Institution_Number";
	private static boolean Debug = false;

	public void initialize()  {
		this.commonInitialize();
	}

	public boolean update(int emailInfoNumber,
			int institutionNumber, int personNumber ) throws MultipleRecordsFoundException, SQLException,
			ClassNotFoundException {

		int count = 0;
		ResultSet rs = this.find(personNumber);
		while (rs.next()) {
			count++;
		}
		this.validate( );
		if (count > 1) {
			throw new MultipleRecordsFoundException(
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
				+ RdbmsConstants.EqualSign + personNumber;
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
				+ RdbmsConstants.SPACE + "("
				+ RdbmsConnection.wrapInDoubleQuotes(EmailInfoNumberCol)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(InstitutionNumberCol)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(PersonNumberCol)
				
				+ ") VALUES (" 
				+ emailInfoNumber + RdbmsConstants.CommaSpace
				+ institutionNumber + RdbmsConstants.CommaSpace
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
				+ RdbmsConstants.SPACE + " SET "
				+ RdbmsConnection.wrapInDoubleQuotes(EmailInfoNumberCol)
				+ RdbmsConstants.EqualSign + emailInfoNumber
				
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(InstitutionNumberCol)
				+ RdbmsConstants.EqualSign + institutionNumber
				
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(PersonNumberCol)
				+ RdbmsConstants.EqualSign + personNumber
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(PersonNumberCol)
				+ RdbmsConstants.EqualSign + personNumber;
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
