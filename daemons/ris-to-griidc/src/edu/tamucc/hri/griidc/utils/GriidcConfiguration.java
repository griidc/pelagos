package edu.tamucc.hri.griidc.utils;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.Iterator;
import java.util.Properties;
import java.util.Set;

import org.ini4j.InvalidFileFormatException;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;

/**
 * This class manages the configuration files in the ini format. There are three
 * ini files of interest all to be found in the base directory /etc/griidc
 * "db.ini", "notifications.ini",  "ris-to-griidc.ini"
 * 
 * RdbmsConstants also has a bunch of constants that are used for database connections
 * 
 * @see edu.tamucc.hri.rdbms.utils.RdbmsConstants
 * @author jvh
 * 
 */
public class GriidcConfiguration {

	private static final String IniBaseDir = "/etc/griidc/";
	private static final String DbIniFileName = IniBaseDir + "db.ini";
	private static final String NotificationsFileName = IniBaseDir
			+ "notifications.ini";
	private static final String RisToGriidcIniFileName = IniBaseDir
			+ "ris-to-griidc.ini";
	
	private static final String RisType = RdbmsConstants.RIS;
	private static final String GriidcType = RdbmsConstants.GRIIDC;
	// section names found in files
	private static final String RisDbIniSectionName = "RIS_RO"; // db.ini
	private static final String GriidcDbIniSectionName =  "GRIIDC_RW"; // db.ini
	
	
	private static final String RisDbSectionName = "RIS_DB"; // ris-to-griidc.ini
	private static final String GriidcDbSectionName = "GRIIDC_DB"; // ris-to-griidc.ini
	private static final String LogFilesSectionName = "LOG_FILES"; // ris-to-griidc.ini
	private static final String OtherSectionName = "OTHER"; // ris-to-griidc.ini
	private static final String EmailSectionName = "EMAIL"; // ris-to-griidc.ini
	
	//  email properties
	private static final String GriidcMailSender = "mail.from"; // ris-to-griidc.ini
	private static final String GriidcMailHost = "mail.host"; // ris-to-griidc.ini
	private static final String GriidcMailUser = "mail.user"; // ris-to-griidc.ini
	

	private static final String RisToGriidcNotificationsSection = "ris-to-griidc"; // notifications.ini
	private static final String RisErrorsType = "riserrors";  // notifications.ini
	private static final String PrimaryLogType = "primarylog";  // notifications.ini

	//  property names
	private static final String PrimayLogFileNameProperty = "primaryLogName";
	private static final String PrimayLogFileDirectoryProperty = "logFileDir";
	private static final String RisErrorLogNameProperty = "risErrorLogName";
	private static final String DeveloperLogFileNameProperty = "developerLogName";
	private static final String RisWarningLogNameProperty = "risWarningLogName";
	private static final String FuzzyHeuristicPostalCodeMatchingProperty = "fuzzyHeuristicPostalCodeMatching";
	
	private static IniPropertyHandler DbIniHandlerInstance = null;
	private static IniPropertyHandler NotificationsIniHandlerInstance = null;
	private static IniPropertyHandler RisToGriidcIniHandlerInstance = null;

	private static boolean Debug = false;

	

	public static String getNotificationsFileName() {
		return NotificationsFileName;
	}

	private GriidcConfiguration() {
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
	 * @throws IniSectionNotFoundException 
	 */
	public static String getDbIniProp(String sectionName, String propertyName)
			throws PropertyNotFoundException, IniSectionNotFoundException {

		return GriidcConfiguration.getIniProp(getDbIniInstance(),
				sectionName, propertyName);
	}

	/**
	 * get a property within a section of the notifications ini file
	 * 
	 * @param sectionName
	 * @param propertyName
	 * @return
	 * @throws PropertyNotFoundException
	 * @throws IniSectionNotFoundException 
	 */
	public static String getNotificationIniProp(String sectionName,
			String propertyName) throws PropertyNotFoundException, IniSectionNotFoundException {

		return GriidcConfiguration.getIniProp(
				getNotificationsIniInstance(), sectionName, propertyName);
	}

	/**
	 * get a property within a section of the application specific ini file
	 * 
	 * @param sectionName
	 * @param propertyName
	 * @return
	 * @throws PropertyNotFoundException
	 * @throws IniSectionNotFoundException 
	 */
	public static String getRisToGriiidcIniProp(String sectionName,
			String propertyName) throws PropertyNotFoundException, IniSectionNotFoundException {
		return GriidcConfiguration.getIniProp(getRisToGriidcIniInstance(),
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
	 * @throws IniSectionNotFoundException 
	 */
	public static String getIniProp(IniPropertyHandler ini, String sectionName,
			String propertyName) throws PropertyNotFoundException, IniSectionNotFoundException {
		String prop = ini.getProp(sectionName,propertyName);
		return prop;
	}

	public static String getDbIniFileName() {
		return DbIniFileName;
	}

	public static String getRisToGriidcIniFileName() {
		return RisToGriidcIniFileName;
	}

	public static String getRisDbIniSection() {
		return RisDbIniSectionName;
	}

	public static String getGriidcDbIniSection() {
		return GriidcDbIniSectionName;
	}

	public static String getRisToGriidcNotificationsSection() {
		return RisToGriidcNotificationsSection;
	}

	public static String getRisToGriidcRisDbSection() {
		return RisDbSectionName;
	}

	public static String getRisToGriidcGriidcDbSection() {
		return GriidcDbSectionName;
	}

	public static String getRisToGriidcLogFilesSection() {
		return LogFilesSectionName;
	}

	public static String getRisToGriidcOtherSection() {
		return OtherSectionName;
	}

	public static String getRisToGriidcEmailSection() {
		return EmailSectionName;
	}

	public static IniPropertyHandler getDbIniInstance() {
		if (DbIniHandlerInstance == null) {
			try {
				DbIniHandlerInstance = new IniPropertyHandler();
				DbIniHandlerInstance.init(GriidcConfiguration.getDbIniFileName());
			} catch (InvalidFileFormatException e) {
				System.err.println("InvalidFileFormatException for file "
						+ GriidcConfiguration.getDbIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (FileNotFoundException e) {
				System.err.println("FileNotFoundException for file "
						+ GriidcConfiguration.getDbIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (IOException e) {
				System.err.println("IOException for file " + GriidcConfiguration.getDbIniFileName());
				e.printStackTrace();
				System.exit(-1);
			}
		}
		return DbIniHandlerInstance;
	}

	public static IniPropertyHandler getNotificationsIniInstance() {
		if (NotificationsIniHandlerInstance == null) {
			try {
				NotificationsIniHandlerInstance = new IniPropertyHandler();
				NotificationsIniHandlerInstance.init(GriidcConfiguration.getNotificationsFileName());
			} catch (InvalidFileFormatException e) {
				System.err.println("InvalidFileFormatException for file "
						+ GriidcConfiguration.getNotificationsFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (FileNotFoundException e) {
				System.err.println("FileNotFoundException for file "
						+ GriidcConfiguration.getNotificationsFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (IOException e) {
				System.err.println("IOException for file "
						+ GriidcConfiguration.getNotificationsFileName());
				e.printStackTrace();
				System.exit(-1);
			}
		}

		return NotificationsIniHandlerInstance;
	}

	public static IniPropertyHandler getRisToGriidcIniInstance() {
		if (RisToGriidcIniHandlerInstance == null) {
			try {
				RisToGriidcIniHandlerInstance = new IniPropertyHandler();
				RisToGriidcIniHandlerInstance.init(GriidcConfiguration.getRisToGriidcIniFileName());

			} catch (InvalidFileFormatException e) {
				System.err.println("InvalidFileFormatException for file "
						+ GriidcConfiguration.getRisToGriidcIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (FileNotFoundException e) {
				System.err.println("FileNotFoundException for file "
						+ GriidcConfiguration.getRisToGriidcIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (IOException e) {
				System.err.println("IOException for file "
						+ GriidcConfiguration.getRisToGriidcIniFileName());
				e.printStackTrace();
				System.exit(-1);
			}
		}
		return RisToGriidcIniHandlerInstance;
	}

	public static String getWorkingDirectory() {
		return System.getProperty("user.dir");
	}
	
	
	private static String getCriticalRisToGriidcProperty(String property) {
		String result = null;
		try {
			result =  GriidcConfiguration.getRisToGriiidcIniProp(
				GriidcConfiguration.getRisToGriidcLogFilesSection(),
				property);
		} catch (PropertyNotFoundException e) {
			
		} catch (IniSectionNotFoundException e) {
			System.err.println(e.getMessage());
			System.exit(-1);
		}
		return result;
	}
	public static String getLogFileDirectory() {
		return getCriticalRisToGriidcProperty(PrimayLogFileDirectoryProperty);
	}

	public static String getPrimaryLogFileName() {
		return getCriticalRisToGriidcProperty(PrimayLogFileNameProperty);
	}

	
	public static String getRisErrorLogFileName() {
		return getCriticalRisToGriidcProperty(RisErrorLogNameProperty);
	}
	public static String getRisWarningLogFileName() {
		return getCriticalRisToGriidcProperty(RisWarningLogNameProperty);
	}
	
	/*********
	 *  } catch (PropertyNotFoundException e) {
			propertyNotFoundError(FFFFF, XXXXX);
			System.exit(-1);
		}
		return result;
	*************/
	public static String getDeveloperReportFileName() {
		return getCriticalRisToGriidcProperty(DeveloperLogFileNameProperty);
	}

	public static boolean isFuzzyPostalCodeTrue() {
		String s = getCriticalRisToGriidcProperty(DeveloperLogFileNameProperty);
		return Boolean.getBoolean(s);
	}

	public static Properties getEmailProperties() throws IniSectionNotFoundException, PropertyNotFoundException {

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

	 */
	public static String[] getRisErrorMsgLogRecipients() {
		return getRecipients(RisErrorsType);
	}

	public static String[] getPrimaryMsgLogRecipients()
			throws PropertyNotFoundException {
		return getRecipients(PrimaryLogType);
	}

	private static String[] getRecipients(String messageType) {
		return getNotificationsIniInstance().
				getPropertiesWithinSectionThatContainValue(RisToGriidcNotificationsSection,messageType);
	}

	public static String getGriidcMailSender() throws IniSectionNotFoundException, PropertyNotFoundException {
		return GriidcConfiguration
				.getRisToGriiidcEmailIniProp(GriidcMailSender);
	}

	public static String getRisToGriiidcEmailIniProp(String property)
			throws PropertyNotFoundException, IniSectionNotFoundException {
		return GriidcConfiguration.getRisToGriiidcIniProp(
				GriidcConfiguration.EmailSectionName, property);
	}

	public static void main(String[] args) throws PropertyNotFoundException,
			InvalidFileFormatException, IOException, IniSectionNotFoundException {

		GriidcConfiguration.setDebug(true);
		String[] addrs = GriidcConfiguration.getPrimaryMsgLogRecipients();
		System.out.println("\nWho wants Primary log ???");
		for (String ad : addrs) {
			System.out.println("\t" + ad);
		}

		addrs = GriidcConfiguration.getRisErrorMsgLogRecipients();
		System.out.println("\nWho wants RIS Error log ???");
		for (String ad : addrs) {
			System.out.println("\t" + ad);
		}

		System.out.println("\nEmail properties");
		Properties eProps = GriidcConfiguration.getEmailProperties();
		Set<String> props = eProps.stringPropertyNames();
		Iterator<String> it = props.iterator();
		while (it.hasNext()) {
			String key = it.next();
			System.out.println(key + " -> " + eProps.getProperty(key));
		}

		System.out.println("\nWorking Directory - "
				+ GriidcConfiguration.getWorkingDirectory());

		System.out.println("\ngetLogFileDirectory - "
				+ GriidcConfiguration.getLogFileDirectory());

		System.out.println("\ngetPrimaryLogFileName - "
				+ GriidcConfiguration.getPrimaryLogFileName());

		System.out.println("\ngetRisErrorLogFileName - "
				+ GriidcConfiguration.getRisErrorLogFileName());
		System.out.println("\ngetDeveloperReportFileName - "
				+ GriidcConfiguration.getDeveloperReportFileName());

		System.out.println("\nisFuzzyPostalCodeTrue - "
				+ GriidcConfiguration.isFuzzyPostalCodeTrue());
		
		
		String dbIniSectionName = RisDbIniSectionName;
		String risToGriidcSectionName = RisDbSectionName;
		System.out.println("\nDB Ini Section Name: " + dbIniSectionName + ", risToGriidcSectionName: " + risToGriidcSectionName);
		String rdbmsType = GriidcConfiguration.getDbIniProp(dbIniSectionName,"type");
		String rdbmsJdbcDriverName = GriidcConfiguration.getDbIniProp(dbIniSectionName,"driverName");
		String rdbmsJdbcPrefix = GriidcConfiguration.getRisToGriiidcIniProp(dbIniSectionName,"jdbcPrefix");
		String rdbmsHost = GriidcConfiguration.getDbIniProp(dbIniSectionName,"host");
		String rdbmsPort = GriidcConfiguration.getDbIniProp(dbIniSectionName,"port");
		String rdbmsName = GriidcConfiguration.getDbIniProp(dbIniSectionName,"dbname");
		//String rdbmsSchemaName = RisToGriidcConfiguration.getRisToGriiidcIniProp(dbIniSectionName,"schema");
		String rdbmsUser = GriidcConfiguration.getDbIniProp(dbIniSectionName,"username");
		String rdbmsPassword = GriidcConfiguration.getDbIniProp(dbIniSectionName,"password");
		String rdbmsUrl = "joe.com";
		
		System.out.println("\nRdbmsConnection [rdbmsType=" + rdbmsType + ", rdbmsHost="
		+ rdbmsHost + ", rdbmsPort=" + rdbmsPort + ", rdbmsUrl="
		+ rdbmsUrl + ", rdbmsUser=" + rdbmsUser + ", rdbmsPassword="
		+ rdbmsPassword + ", rdbmsName=" + rdbmsName
		// + ", rdbmsSchemaName=" + rdbmsSchemaName
		+ ", rdbmsJdbcDriverName=" + rdbmsJdbcDriverName
		+ ", rdbmsJdbcPrefix=" + rdbmsJdbcPrefix + "]");

	}
}
