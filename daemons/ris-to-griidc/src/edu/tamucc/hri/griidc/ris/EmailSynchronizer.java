package edu.tamucc.hri.griidc.ris;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.MultipleRecordsFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;
import edu.tamucc.hri.griidc.rdbms.SynchronizerBase;
import edu.tamucc.hri.griidc.utils.MiscUtils;

import javax.mail.internet.AddressException;
import javax.mail.internet.InternetAddress;

/**
 * Create or update a the EmailInfo table in GRIIDC This is not run directly
 * from the main but is called as a delegate from PersonSynchronizer
 * 
 * @author jvh
 * @see PersonSynchronizer
 */
public class EmailSynchronizer extends SynchronizerBase {

	private static final String GriidcTableName = "EmailInfo";
	private static final String GriidcPersonNumberColName = "Person_Number";
	private static final String GriidcEmailColName = "EmailInfo_Address";
	private static final String GriidcEmailPrimaryColName = "EmailInfo_PrimaryEmail";

	private static boolean Debug = false;

	private int emailRecordsAdded = 0;
	private int emailRecordsModified = 0;
	private int emailRecordsDuplicates = 0;
	private int emailRecordsRead = 0;
	private int emailRecordsErrors = 0;

	private int griidcPersonNum = -1;
	private String griidcEmailAddress = null;
	private boolean griidcPrimaryTag = false;

	private static EmailSynchronizer instance = null;
	private boolean initialized = false;

	public static EmailSynchronizer getInstance() {
		if (EmailSynchronizer.instance == null) {
			EmailSynchronizer.instance = new EmailSynchronizer();
		}
		return EmailSynchronizer.instance;
	}

	private EmailSynchronizer() {
	}

	public void initialize() {
		if(initialized) return;
		super.commonInitialize();
		initialized = true;
	}

	/**
	 * this is the entry point for all email record modifications. It is called
	 * from PersonSynchronizer. This function can result in add, modify, errors
	 * or no action
	 * 
	 * @param personNumber
	 * @param emailAddr
	 * @param primaryTag
	 * @return
	 * @throws MultipleRecordsFoundException
	 * @throws SQLException
	 * @throws AddressException
	 */
	public boolean update(int personNumber, String emailAddr,
			boolean primaryTag) throws AddressException, SQLException,
			MultipleRecordsFoundException {
		initialize();
		this.emailRecordsRead++;
		int count = 0;
		String tempEmailAddr = emailAddr.trim();

		this.validate(tempEmailAddr);
		ResultSet rs = this.find(personNumber);
		while (rs.next()) {
			count++;
			this.griidcPersonNum = rs.getInt(GriidcPersonNumberColName);
			this.griidcEmailAddress = rs.getString(GriidcEmailColName);
			this.griidcPrimaryTag = rs.getBoolean(GriidcEmailPrimaryColName);
		}
		if (count == 0) { // no match found - add it
			this.add(personNumber, tempEmailAddr, primaryTag);
			emailRecordsAdded++;
		} else if (count == 1) { // one match - if not equal modify
			if (this.isMatch(personNumber, tempEmailAddr, primaryTag)) {
				this.emailRecordsDuplicates++;
			} else {
				this.modify(personNumber, tempEmailAddr, primaryTag);
				this.emailRecordsModified++;
			}
		} else { // count > 1)
			throw new MultipleRecordsFoundException(
					"ERROR updateing GRIIDC - there are " + count + " "
							+ GriidcTableName + " records with "
							+ GriidcPersonNumberColName + " equal to "
							+ personNumber);
		}
		return true;
	}

	private boolean isMatch(int personNumber, String emailAddr,
			boolean primaryTag) {
		return ((this.griidcPersonNum == personNumber)
				&& (MiscUtils.areStringsEqual(this.griidcEmailAddress,
						emailAddr)) && (this.griidcPrimaryTag == primaryTag));
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
		if (addr == null) {
			this.emailRecordsErrors++;
			throw new AddressException("Email address is blank");
		}
		if (addr.trim().length() == 0) {
			this.emailRecordsErrors++;
			throw new AddressException("Email address is blank");
		}
		boolean result = true;
		InternetAddress emailAddr = new InternetAddress(addr.trim());
		try {
			emailAddr.validate();
		} catch (AddressException e) {
			this.emailRecordsErrors++;
			throw e;
		}
		return result;

	}

	public ResultSet find(int personNumber) throws SQLException {
		String query = this.formatSelect(personNumber);
		return griidcDbConnection.executeQueryResultSet(query);
	}

	private String formatSelect(int personNumber) {
		String query = "SELECT * FROM "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcTableName)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcPersonNumberColName)
				+ RdbmsConstants.EqualSign + personNumber;
		return query;
	}

	public boolean add(int personNumber, String email1, boolean primary)
			throws SQLException {
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
				+ RdbmsConstants.SPACE + "("
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcPersonNumberColName)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcEmailColName)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcEmailPrimaryColName)
				+ ") VALUES (" + personNumber + RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInSingleQuotes(email1)
				+ RdbmsConstants.CommaSpace + RdbmsUtils.getPgBoolean(primary)
				+ " )";
		return query;
	}

	public boolean modify(int personNumber, String email1, boolean primary)
			throws SQLException {
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
				+ RdbmsConstants.SPACE + " SET "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcEmailColName)
				+ RdbmsConstants.EqualSign
				+ RdbmsConnection.wrapInSingleQuotes(email1)
				+ RdbmsConstants.CommaSpace
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcEmailPrimaryColName)
				+ RdbmsConstants.EqualSign + RdbmsUtils.getPgBoolean(primary)
				+ " WHERE "
				+ RdbmsConnection.wrapInDoubleQuotes(GriidcPersonNumberColName)
				+ RdbmsConstants.EqualSign + personNumber;
		return query;
	}

	public int getEmailRecordsAdded() {
		return emailRecordsAdded;
	}

	public int getEmailRecordsModified() {
		return emailRecordsModified;
	}

	public int getEmailRecordsDuplicates() {
		return emailRecordsDuplicates;
	}

	public int getEmailRecordsRead() {
		return emailRecordsRead;
	}

	public int getEmailRecordsErrors() {
		return emailRecordsErrors;
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
		emu.initialize();
		System.out.println("\n" + emu.formatSelect(personNumber));
		System.out.println("\n"
				+ emu.formatInsertStatement(personNumber, email1, primary));
		System.out.println("\n"
				+ emu.formatUpdateStatement(personNumber, email1, primary));

	}

}
