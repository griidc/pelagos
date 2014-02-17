package edu.tamucc.hri.griidc;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.DuplicateRecordException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

import javax.mail.*;
import javax.mail.internet.AddressException;
import javax.mail.internet.InternetAddress;

/**
 * Create or update a the EmailInfo table in GRIIDC
 * This is not run directly from the main
 * but is called as a delegate from PersonSynchronizer
 * 
 * @author jvh
 * @see PersonSynchronizer
 */
public class EmailSynchronizer {

	private static final String GriidcTableName = "EmailInfo";
	private static final String GriidcPersonNumberCol = "Person_Number";
	private static final String EmailCol = "EmailInfo_Address";
	private static final String EmailPrimaryCol = "EmailInfo_PrimaryEmail";
	private RdbmsConnection griidcDbConnection = null;
	private boolean initialized = false;
	private static boolean Debug = false;

	public EmailSynchronizer() {
		// TODO Auto-generated constructor stub
	}

	public void initializeStartUp() throws IOException,
			PropertyNotFoundException, SQLException, ClassNotFoundException {
		if (!initialized) {
			this.griidcDbConnection = RdbmsUtils
					.getGriidcDbConnectionInstance();
			initialized = true;
		}
	}

	public boolean update(int personNumber, String emailAddr,
			boolean primary) throws DuplicateRecordException, SQLException,
			ClassNotFoundException, AddressException {

		int count = 0;
		String tempEmailAddr = emailAddr.trim();
		ResultSet rs = this.find(personNumber);
		while (rs.next()) {
			count++;
		}
		this.validate(tempEmailAddr);
		if (count > 1) {
			throw new DuplicateRecordException(
					"ERROR updateing GRIIDC - there are " + count + " "
							+ GriidcTableName + " records with "
							+ GriidcPersonNumberCol + " equal to " + personNumber);

		}
		if (count == 0) {
			this.add(personNumber, tempEmailAddr, primary);
		} else if (count == 1) {
			this.modify(personNumber, tempEmailAddr, primary);
		} 

		return true;
	}

	private boolean validate(String addr) throws AddressException {
		return isValidEmailAddress(addr.trim());
	}
	/**
	 * look for a valid email address. For now just make sure it is not blank
	 * 
	 * @param addr
	 * @return
	 * @throws AddressException 
	 */
	public boolean isValidEmailAddress(String addr) throws AddressException {
		if(addr == null) throw new AddressException("Email address is blank");
		if(addr.trim().length() == 0) throw new AddressException("Email address is blank");
		boolean result = true;
		InternetAddress emailAddr = new InternetAddress(addr.trim());
		emailAddr.validate();
		return result;

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
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcPersonNumberCol)
				+ RdbmsUtils.EqualSign + personNumber;
		return query;
	}

	public boolean add(int personNumber, String email1, boolean primary)
			throws SQLException, ClassNotFoundException {
		String query = formatInsertStatement(personNumber, email1, primary);
		boolean status = this.griidcDbConnection.executeQueryBoolean(query);
		if (EmailSynchronizer.isDebug())
			System.out.println("ADDED EMAIL "
					+ formatData(personNumber, email1, primary));
		return status;
	}

	public static String formatData(int personNumber, String email1,
			boolean primary) {
		return "PersonNum: " + personNumber + ", Email: " + email1
				+ ", Primary: " + primary;
	}

	private String formatInsertStatement(int personNumber, String email1,
			boolean primary) {
		String query = "INSERT INTO "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ RdbmsUtils.SPACE + "("
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcPersonNumberCol)
				+ RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(EmailCol)
				+ RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(EmailPrimaryCol)
				+ ") VALUES (" + personNumber + RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(email1)
				+ RdbmsUtils.CommaSpace + RdbmsUtils.getPgBoolean(primary)
				+ " )";
		return query;
	}

	public boolean modify(int personNumber, String email1, boolean primary)
			throws SQLException, ClassNotFoundException {
		String query = formatUpdateStatement(personNumber, email1, primary);
		boolean status = this.griidcDbConnection.executeQueryBoolean(query);
		if (EmailSynchronizer.isDebug())
			System.out.println("MODIFIED EMAIL "
					+ formatData(personNumber, email1, primary));
		return status;
	}

	private String formatUpdateStatement(int personNumber, String email1,
			boolean primary) {
		String query = "UPDATE  "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ RdbmsUtils.SPACE + " SET "
				+ RdbmsConnection.wrapInDoubleQuotes(EmailCol)
				+ RdbmsUtils.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(email1)
				+ RdbmsUtils.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(EmailPrimaryCol)
				+ RdbmsUtils.EqualSign + RdbmsUtils.getPgBoolean(primary)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcPersonNumberCol)
				+ RdbmsUtils.EqualSign + personNumber;
		return query;
	}

	public static boolean isDebug() {
		return EmailSynchronizer.Debug;
	}

	public static void setDebug(boolean db) {
		EmailSynchronizer.Debug = db;
	}

	public static void main(String[] args) {

		int personNumber = 222;
		String email1 = "joe.holland@tamucc.edu";
		boolean primary = true;
		EmailSynchronizer emu = new EmailSynchronizer();
		try {
			emu.initializeStartUp();
			System.out.println("\n" + emu.formatSelect(personNumber));
			System.out.println("\n"
					+ emu.formatInsertStatement(personNumber, email1, primary));
			System.out.println("\n"
					+ emu.formatUpdateStatement(personNumber, email1, primary));
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
		}

	}

}
