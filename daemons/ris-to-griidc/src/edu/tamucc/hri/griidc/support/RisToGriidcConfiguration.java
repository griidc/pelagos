package edu.tamucc.hri.griidc.support;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.util.Enumeration;
import java.util.Iterator;
import java.util.Properties;
import java.util.Set;
import java.util.Vector;

import org.ini4j.Ini;
import org.ini4j.InvalidFileFormatException;
import org.ini4j.Profile.Section;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

/**
 * This class manages the configuration files in the ini format. There are three
 * ini files of interest all to be found in the base directory /etc/griidc
 * 
 * @author jvh
 * 
 */
public class RisToGriidcConfiguration {

	private static final String IniBaseDir = "/etc/griidc/";
	private static final String DbIniFileName = IniBaseDir + "db.ini";
	private static final String NotificationsFileName = IniBaseDir
			+ "notifications.ini";
	private static final String RisToGriidcIniFileName = IniBaseDir
			+ "ris-to-griidc.ini";
	// private static String PropertiesFilePath = DbIniFileName;

	private static final String[] fileName = { DbIniFileName,
			NotificationsFileName, RisToGriidcIniFileName };

	// section names found in files
	private static final String RisDbIniSection = "RIS_RO"; // db.ini
	private static final String GriidcDbIniSection =  "GRIIDC_RW"; // db.ini
	private static final String RisToGriidcNotificationsSection = "ris-to-griidc"; // notifications.ini
	private static final String RisErrorsType = "riserrors";
	private static final String PrimaryLogType = "primarylog";
	private static final String RisToGriidcRisDbSection = "RIS_DB"; // ris-to-griidc.ini
	private static final String RisToGriidcGriidcDbSection = "GRIIDC_DB"; // ris-to-griidc.ini
	private static final String RisToGriidcLogFilesSection = "LOG_FILES"; // ris-to-griidc.ini
	private static final String RisToGriidcOtherSection = "OTHER"; // ris-to-griidc.ini
	private static final String RisToGriidcEmailSection = "EMAIL"; // ris-to-griidc.ini
	private static final String GriidcMailSender = "mail.from"; // ris-to-griidc.ini
	private static final String GriidcMailHost = "mail.host"; // ris-to-griidc.ini
	private static final String GriidcMailUser = "mail.user"; // ris-to-griidc.ini

	private static Ini DbIniInstance = null;
	private static Ini NotificationsIniInstance = null;
	private static Ini RisToGriidcIniInstance = null;

	private static boolean Debug = false;

	private static Ini loadIniFile(String fileName)
			throws InvalidFileFormatException, FileNotFoundException,
			IOException {
		Ini ini = new Ini(new FileReader(fileName));
		return ini;
	}

	private RisToGriidcConfiguration() {
		super();
	}

	public static void setDebug(boolean trueOrFalse) {
		Debug = trueOrFalse;
	}

	/**
	 * get a property within a section of the database ini file
	 * 
	 * @param sectionName
	 * @param propertyName
	 * @return
	 * @throws PropertyNotFoundException
	 */
	public static String getDbIniProp(String sectionName, String propertyName)
			throws PropertyNotFoundException {

		return RisToGriidcConfiguration.getIniProp(getDbIniInstance(),
				sectionName, propertyName);
	}

	/**
	 * get a property within a section of the notifications ini file
	 * 
	 * @param sectionName
	 * @param propertyName
	 * @return
	 * @throws PropertyNotFoundException
	 */
	public static String getNotificationIniProp(String sectionName,
			String propertyName) throws PropertyNotFoundException {

		return RisToGriidcConfiguration.getIniProp(
				getNotificationsIniInstance(), sectionName, propertyName);
	}

	/**
	 * get a property within a section of the application specific ini file
	 * 
	 * @param sectionName
	 * @param propertyName
	 * @return
	 * @throws PropertyNotFoundException
	 */
	public static String getRisToGriiidcIniProp(String sectionName,
			String propertyName) throws PropertyNotFoundException {
		return RisToGriidcConfiguration.getIniProp(getRisToGriidcIniInstance(),
				sectionName, propertyName);
	}

	/**
	 * return a property from within a section of a particular file
	 * 
	 * @param ini
	 * @param sectionName
	 * @param propertyName
	 * @return
	 * @throws PropertyNotFoundException
	 */
	public static String getIniProp(Ini ini, String sectionName,
			String propertyName) throws PropertyNotFoundException {
		String prop = ini.get(sectionName).get(propertyName);

		if (prop == null) {
			System.err.println("RisToGriidcConfiguration.getIniProp(Ini," + sectionName + ",  " + 
			propertyName + ") - property not found");
			throw new PropertyNotFoundException("No property: " + propertyName
					+ " found in file: "
					+ DbIniInstance.getFile().getAbsolutePath());
		}
		return prop;
	}

	public static String getDbIniFileName() {
		return DbIniFileName;
	}

	public static String getRisToGriidcIniFileName() {
		return RisToGriidcIniFileName;
	}

	public static String getRisDbIniSection() {
		return RisDbIniSection;
	}

	public static String getGriidcDbIniSection() {
		return GriidcDbIniSection;
	}

	public static String getRisToGriidcNotificationsSection() {
		return RisToGriidcNotificationsSection;
	}

	public static String getRisToGriidcRisDbSection() {
		return RisToGriidcRisDbSection;
	}

	public static String getRisToGriidcGriidcDbSection() {
		return RisToGriidcGriidcDbSection;
	}

	public static String getRisToGriidcLogFilesSection() {
		return RisToGriidcLogFilesSection;
	}

	public static String getRisToGriidcOtherSection() {
		return RisToGriidcOtherSection;
	}

	public static String getRisToGriidcEmailSection() {
		return RisToGriidcEmailSection;
	}

	public static Ini getDbIniInstance() {
		if (DbIniInstance == null) {
			try {
				DbIniInstance = loadIniFile(DbIniFileName);
			} catch (InvalidFileFormatException e) {
				System.err.println("InvalidFileFormatException for file "
						+ DbIniFileName);
				e.printStackTrace();
				System.exit(-1);
			} catch (FileNotFoundException e) {
				System.err.println("FileNotFoundException for file "
						+ DbIniFileName);
				e.printStackTrace();
				System.exit(-1);
			} catch (IOException e) {
				System.err.println("IOException for file " + DbIniFileName);
				e.printStackTrace();
				System.exit(-1);
			}
		}
		return DbIniInstance;
	}

	public static Ini getNotificationsIniInstance() {
		if (NotificationsIniInstance == null) {
			try {
				NotificationsIniInstance = loadIniFile(NotificationsFileName);
			} catch (InvalidFileFormatException e) {
				System.err.println("InvalidFileFormatException for file "
						+ NotificationsFileName);
				e.printStackTrace();
				System.exit(-1);
			} catch (FileNotFoundException e) {
				System.err.println("FileNotFoundException for file "
						+ NotificationsFileName);
				e.printStackTrace();
				System.exit(-1);
			} catch (IOException e) {
				System.err.println("IOException for file "
						+ NotificationsFileName);
				e.printStackTrace();
				System.exit(-1);
			}
		}

		return NotificationsIniInstance;
	}

	public static Ini getRisToGriidcIniInstance() {
		if (RisToGriidcIniInstance == null) {
			try {
				RisToGriidcIniInstance = loadIniFile(RisToGriidcIniFileName);

			} catch (InvalidFileFormatException e) {
				System.err.println("InvalidFileFormatException for file "
						+ RisToGriidcIniFileName);
				e.printStackTrace();
				System.exit(-1);
			} catch (FileNotFoundException e) {
				System.err.println("FileNotFoundException for file "
						+ RisToGriidcIniFileName);
				e.printStackTrace();
				System.exit(-1);
			} catch (IOException e) {
				System.err.println("IOException for file "
						+ RisToGriidcIniFileName);
				e.printStackTrace();
				System.exit(-1);
			}
		}
		return RisToGriidcIniInstance;
	}

	public static String getWorkingDirectory() {
		return System.getProperty("user.dir");
	}

	public static String getLogFileDirectory() throws PropertyNotFoundException {
		return RisToGriidcConfiguration.getRisToGriiidcIniProp(
				RisToGriidcConfiguration.getRisToGriidcLogFilesSection(),
				"logFileDir");
	}

	public static String getPrimaryLogFileName()
			throws PropertyNotFoundException {
		return getLogFileDirectory()
				+ RisToGriidcConfiguration.getRisToGriiidcIniProp(
						RisToGriidcConfiguration
								.getRisToGriidcLogFilesSection(),
						"primaryLogName");
	}

	public static String getRisErrorLogFileName()
			throws PropertyNotFoundException {
		return getLogFileDirectory()
				+ RisToGriidcConfiguration.getRisToGriiidcIniProp(
						RisToGriidcConfiguration
								.getRisToGriidcLogFilesSection(),
						"risErrorLogName");
	}

	public static String getDeveloperReportFileName()
			throws PropertyNotFoundException {
		return getLogFileDirectory()
				+ RisToGriidcConfiguration.getRisToGriiidcIniProp(
						RisToGriidcConfiguration
								.getRisToGriidcLogFilesSection(),
						"developerLogName");
	}

	public static boolean isFuzzyPostalCodeTrue()
			throws PropertyNotFoundException {
		String s = RisToGriidcConfiguration.getRisToGriiidcIniProp(
				RisToGriidcConfiguration.getRisToGriidcOtherSection(),
				"fuzzyHeuristicPostalCodeMatching");
		return Boolean.getBoolean(s);
	}

	public static Properties getEmailProperties()
			throws PropertyNotFoundException {

		Properties props = new Properties();
		props = new Properties();
		props.setProperty(GriidcMailSender,
				getRisToGriiidcEmailIniProp(GriidcMailSender));
		props.setProperty(GriidcMailHost,
				getRisToGriiidcEmailIniProp(GriidcMailHost));
		props.setProperty(GriidcMailUser,
				getRisToGriiidcEmailIniProp(GriidcMailUser));
		return props;
	}

	/**
	 * the recipients for the RIS error log are listed in the
	 * /etc/griidc/notifications.ini file
	 * 
	 * @return
	 * @throws PropertyNotFoundException
	 */
	public static String[] getRisErrorMsgLogRecipients()
			throws PropertyNotFoundException {
		return getRecipients(RisErrorsType);
	}

	public static String[] getPrimaryMsgLogRecipients()
			throws PropertyNotFoundException {
		return getRecipients(PrimaryLogType);
	}

	private static String[] getRecipients(String type)
			throws PropertyNotFoundException {
		Vector<String> addrs = new Vector<String>();
		Ini ini = getNotificationsIniInstance();
		Section section = ini.get(RisToGriidcNotificationsSection);
		for (String emailAddr : section.keySet()) {
			String types = section.get(emailAddr);
			if (types.contains(type)) {
				addrs.add(emailAddr);
			}

		}
		String[] s = new String[addrs.size()];
		return addrs.toArray(s);
	}

	public static String getGriidcMailSender() throws PropertyNotFoundException {
		return RisToGriidcConfiguration
				.getRisToGriiidcEmailIniProp(GriidcMailSender);
	}

	public static String getRisToGriiidcEmailIniProp(String property)
			throws PropertyNotFoundException {
		return RisToGriidcConfiguration.getRisToGriiidcIniProp(
				RisToGriidcConfiguration.RisToGriidcEmailSection, property);
	}

	public static void main(String[] args) throws PropertyNotFoundException,
			InvalidFileFormatException, IOException {

		RisToGriidcConfiguration.setDebug(true);
		String[] addrs = RisToGriidcConfiguration.getPrimaryMsgLogRecipients();
		System.out.println("\nWho wants Primary log ???");
		for (String ad : addrs) {
			System.out.println("\t" + ad);
		}

		addrs = RisToGriidcConfiguration.getRisErrorMsgLogRecipients();
		System.out.println("\nWho wants RIS Error log ???");
		for (String ad : addrs) {
			System.out.println("\t" + ad);
		}

		System.out.println("\nEmail properties");
		Properties eProps = RisToGriidcConfiguration.getEmailProperties();
		Set<String> props = eProps.stringPropertyNames();
		Iterator<String> it = props.iterator();
		while (it.hasNext()) {
			String key = it.next();
			System.out.println(key + " -> " + eProps.getProperty(key));
		}

		System.out.println("\nWorking Directory - "
				+ RisToGriidcConfiguration.getWorkingDirectory());

		System.out.println("\ngetLogFileDirectory - "
				+ RisToGriidcConfiguration.getLogFileDirectory());

		System.out.println("\ngetPrimaryLogFileName - "
				+ RisToGriidcConfiguration.getPrimaryLogFileName());

		System.out.println("\ngetRisErrorLogFileName - "
				+ RisToGriidcConfiguration.getRisErrorLogFileName());
		System.out.println("\ngetDeveloperReportFileName - "
				+ RisToGriidcConfiguration.getDeveloperReportFileName());

		System.out.println("\nisFuzzyPostalCodeTrue - "
				+ RisToGriidcConfiguration.isFuzzyPostalCodeTrue());

	}
}
