package edu.tamucc.hri.griidc.utils;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.Iterator;
import java.util.Properties;
import java.util.Set;

import org.ini4j.InvalidFileFormatException;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

/**
 * A Base class to do most of the work to set up the configuration
 * as presented by the db.ini, notifications.ini and your application.ini 
 * file. Instantiating classes should call
 * setNotificationsSection(String notificationsSection)
 * setErrorsType(String errorsType)
 * setPrimayLogFileNameProperty(String primayLogFileNameProperty) 
 * setErrorLogFileNameProperty(String errorLogNameProperty) 
 * setDeveloperLogFileNameProperty(String developerLogFileNameProperty) 
 * setWarningLogFileNameProperty(String warningLogFileNameProperty) 
 * @author jvh
 *
 */
public abstract class IniConfigurationBase implements IniConfigurationInterface {

	private static final String IniBaseDir = "/etc/griidc/";
	private static final String DbIniFileName = IniBaseDir + "db.ini";
	private static final String NotificationsFileName = IniBaseDir
			+ "notifications.ini";
	private static final String AppIniFileName = IniBaseDir
			+ "pub-to-griidc.ini";

	// section names found db.ini files
	private static final String DbRisSectionName = "RIS_RO"; // db.ini
	private static final String GriidcDbSectionName = "GRIIDC_RW"; // db.ini
	// section names found int application ini
	private static final String AppRisDbSection = "RIS_DB"; // pub-to-griidc.ini
	private static final String AppGriidcDbSection = "GRIIDC_DB"; // pub-to-griidc.ini
	private static final String AppLogFilesSection = "LOG_FILES"; // pub-to-griidc.ini
	private static final String AppOtherSection = "OTHER"; // pub-to-griidc.ini
	private static final String AppEmailSection = "EMAIL"; // pub-to-griidc.ini

	// email properties in application ini
	private static final String GriidcMailSender = "mail.from"; // pub-to-griidc.ini
	private static final String GriidcMailHost = "mail.host"; // pub-to-griidc.ini
	private static final String GriidcMailUser = "mail.user"; // pub-to-griidc.ini

	private static IniPropertyHandler DbIniHandlerInstance = null;
	private static IniPropertyHandler NotificationsIniHandlerInstance = null;
	private static IniPropertyHandler ApplicationIniHandlerInstance = null;

	private static boolean Debug = false;

	// section name found in notifications.ini
	private String NotificationsSection = "pub-to-griidc"; // notifications.ini
	// property type in notifications.ini in NotificationsSection section
	private String ErrorsType = "puberrors"; // notifications.ini
	private String PrimaryLogType = "primarylog"; // notifications.ini

	// property names found in application ini
	private String PrimayLogFileNameProperty = "pubsPrimaryLogName";// pub-to-griidc.ini
	private String PrimayLogFileDirectoryProperty = "logFileDir";// pub-to-griidc.ini
	private String ErrorLogNameProperty = "pubsErrorLogName";// pub-to-griidc.ini
	private String DeveloperLogFileNameProperty = "pubsDeveloperLogName";// pub-to-griidc.ini
	private String WarningLogFileNameProperty = "pubsWarningLogName";// pub-to-griidc.ini

	public IniConfigurationBase() {

	}

	@Override
	public String getNotificationsIniFileName() {
		return NotificationsFileName;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getDbIniFileName
	 * ()
	 */
	@Override
	public String getDbIniFileName() {
		return DbIniFileName;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsToGriidcIniFileName()
	 */
	@Override
	public String getAppIniFileName() {
		return AppIniFileName;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getPubsDbIniSection
	 * ()
	 */
	@Override
	public String getDbRisSection() {
		return DbRisSectionName;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsToGriidcGriidcDbSection()
	 */
	@Override
	public String getDbGriidcSection() {
		return GriidcDbSectionName;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsToGriidcNotificationsSection()
	 */
	@Override
	public String getNotificationsSection() {
		return NotificationsSection;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsToGriidcLogFilesSection()
	 */
	@Override
	public String getAppLogFilesSection() {
		return AppLogFilesSection;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsToGriidcOtherSection()
	 */
	@Override
	public String getAppOtherSection() {
		return AppOtherSection;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsToGriidcEmailSection()
	 */
	@Override
	public String getAppEmailSection() {
		return AppEmailSection;
	}

	public static void setDebug(boolean trueOrFalse) {
		Debug = trueOrFalse;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getDbIniProp(
	 * java.lang.String, java.lang.String)
	 */
	@Override
	public String getDbIniProp(String sectionName, String propertyName)
			throws PropertyNotFoundException, IniSectionNotFoundException {

		return this.getIniProp(getDbIniInstance(), sectionName, propertyName);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getNotificationIniProp(java.lang.String, java.lang.String)
	 */
	@Override
	public String getNotificationIniProp(String sectionName, String propertyName)
			throws PropertyNotFoundException, IniSectionNotFoundException {

		return this.getIniProp(getNotificationsIniInstance(), sectionName,
				propertyName);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsToGriiidcIniProp(java.lang.String, java.lang.String)
	 */
	@Override
	public String getAppIniProp(String sectionName, String propertyName)
			throws PropertyNotFoundException, IniSectionNotFoundException {
		return this.getIniProp(getAppIniInstance(), sectionName, propertyName);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getIniProp(edu
	 * .tamucc.hri.griidc.support.IniPropertyHandler, java.lang.String,
	 * java.lang.String)
	 */
	@Override
	public String getIniProp(IniPropertyHandler ini, String sectionName,
			String propertyName) throws PropertyNotFoundException,
			IniSectionNotFoundException {
		String prop = ini.getProp(sectionName, propertyName);
		return prop;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getDbIniInstance
	 * ()
	 */
	@Override
	public IniPropertyHandler getDbIniInstance() {
		if (DbIniHandlerInstance == null) {
			try {
				DbIniHandlerInstance = new IniPropertyHandler();
				DbIniHandlerInstance.init(this.getDbIniFileName());
			} catch (InvalidFileFormatException e) {
				System.err.println("InvalidFileFormatException for file "
						+ this.getDbIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (FileNotFoundException e) {
				System.err.println("FileNotFoundException for file "
						+ this.getDbIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (IOException e) {
				System.err.println("IOException for file "
						+ this.getDbIniFileName());
				e.printStackTrace();
				System.exit(-1);
			}
		}
		return DbIniHandlerInstance;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsToGriidcIniInstance()
	 */
	@Override
	public IniPropertyHandler getAppIniInstance() {
		if (ApplicationIniHandlerInstance == null) {
			try {
				ApplicationIniHandlerInstance = new IniPropertyHandler();
				ApplicationIniHandlerInstance.init(this.getAppIniFileName());

			} catch (InvalidFileFormatException e) {
				System.err.println("InvalidFileFormatException for file "
						+ this.getAppIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (FileNotFoundException e) {
				System.err.println("FileNotFoundException for file "
						+ this.getAppIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (IOException e) {
				System.err.println("IOException for file "
						+ this.getAppIniFileName());
				e.printStackTrace();
				System.exit(-1);
			}
		}
		return ApplicationIniHandlerInstance;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getNotificationsIniInstance()
	 */
	@Override
	public IniPropertyHandler getNotificationsIniInstance() {
		if (NotificationsIniHandlerInstance == null) {
			try {
				NotificationsIniHandlerInstance = new IniPropertyHandler();
				NotificationsIniHandlerInstance.init(this
						.getNotificationsIniFileName());
			} catch (InvalidFileFormatException e) {
				System.err.println("InvalidFileFormatException for file "
						+ this.getNotificationsIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (FileNotFoundException e) {
				System.err.println("FileNotFoundException for file "
						+ this.getNotificationsIniFileName());
				e.printStackTrace();
				System.exit(-1);
			} catch (IOException e) {
				System.err.println("IOException for file "
						+ this.getNotificationsIniFileName());
				e.printStackTrace();
				System.exit(-1);
			}
		}

		return NotificationsIniHandlerInstance;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getWorkingDirectory
	 * ()
	 */
	@Override
	public String getWorkingDirectory() {
		return System.getProperty("user.dir");
	}

	private String getCriticalPubsToGriidcProperty(String property) {
		String result = null;
		try {
			result = this.getAppIniProp(this.getAppLogFilesSection(), property);
		} catch (PropertyNotFoundException e) {

		} catch (IniSectionNotFoundException e) {
			System.err.println(e.getMessage());
			System.exit(-1);
		}
		return result;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getLogFileDirectory
	 * ()
	 */
	@Override
	public String getLogFileDirectory() {
		return getCriticalPubsToGriidcProperty(PrimayLogFileDirectoryProperty);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getPrimaryLogFileName
	 * ()
	 */
	@Override
	public String getPrimaryLogFileName() {
		return getCriticalPubsToGriidcProperty(PrimayLogFileNameProperty);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsErrorLogFileName()
	 */
	@Override
	public String getErrorLogFileName() {
		return getCriticalPubsToGriidcProperty(ErrorLogNameProperty);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsWarningLogFileName()
	 */
	@Override
	public String getWarningLogFileName() {
		return getCriticalPubsToGriidcProperty(WarningLogFileNameProperty);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getDeveloperReportFileName()
	 */
	@Override
	public String getDeveloperReportFileName() {
		return getCriticalPubsToGriidcProperty(DeveloperLogFileNameProperty);
	}

	public boolean isFuzzyPostalCodeTrue() {
		String s = getCriticalPubsToGriidcProperty(DeveloperLogFileNameProperty);
		return Boolean.getBoolean(s);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getEmailProperties
	 * ()
	 */
	@Override
	public Properties getEmailProperties() throws IniSectionNotFoundException,
			PropertyNotFoundException {

		Properties props = new Properties();
		props = new Properties();
		props.setProperty(GriidcMailSender, getEmailIniProp(GriidcMailSender));
		props.setProperty(GriidcMailHost, getEmailIniProp(GriidcMailHost));
		props.setProperty(GriidcMailUser, getEmailIniProp(GriidcMailUser));
		return props;
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsErrorMsgLogRecipients()
	 */
	@Override
	public String[] getErrorMsgLogRecipients() {
		return getRecipients(ErrorsType);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPrimaryMsgLogRecipients()
	 */
	@Override
	public String[] getPrimaryMsgLogRecipients()
			throws PropertyNotFoundException {
		return getRecipients(PrimaryLogType);
	}

	private String[] getRecipients(String messageType) {
		return getNotificationsIniInstance()
				.getPropertiesWithinSectionThatContainValue(
						NotificationsSection, messageType);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see
	 * edu.tamucc.hri.griidc.support.IniConfigurationInterface#getGriidcMailSender
	 * ()
	 */
	@Override
	public String getMailSender() throws IniSectionNotFoundException,
			PropertyNotFoundException {
		return this.getEmailIniProp(GriidcMailSender);
	}

	/*
	 * (non-Javadoc)
	 * 
	 * @see edu.tamucc.hri.griidc.support.IniConfigurationInterface#
	 * getPubsToGriiidcEmailIniProp(java.lang.String)
	 */
	@Override
	public String getEmailIniProp(String property)
			throws PropertyNotFoundException, IniSectionNotFoundException {
		return this.getAppIniProp(this.AppEmailSection, property);
	}

	public static void main(String[] args) throws PropertyNotFoundException,
			InvalidFileFormatException, IOException,
			IniSectionNotFoundException {

		PubsIniConfiguration.setDebug(true);
		PubsIniConfiguration iniConfig = new PubsIniConfiguration();

		String[] addrs = iniConfig.getPrimaryMsgLogRecipients();
		System.out.println("\nWho wants Primary log ???");
		for (String ad : addrs) {
			System.out.println("\t" + ad);
		}

		addrs = iniConfig.getErrorMsgLogRecipients();
		System.out.println("\nWho wants RIS Error log ???");
		for (String ad : addrs) {
			System.out.println("\t" + ad);
		}

		System.out.println("\nEmail properties");
		Properties eProps = iniConfig.getEmailProperties();
		Set<String> props = eProps.stringPropertyNames();
		Iterator<String> it = props.iterator();
		while (it.hasNext()) {
			String key = it.next();
			System.out.println(key + " -> " + eProps.getProperty(key));
		}

		System.out.println("\nWorking Directory - "
				+ iniConfig.getWorkingDirectory());

		System.out.println("\ngetLogFileDirectory - "
				+ iniConfig.getLogFileDirectory());

		System.out.println("\ngetPrimaryLogFileName - "
				+ iniConfig.getPrimaryLogFileName());

		System.out.println("\ngetPubsErrorLogFileName - "
				+ iniConfig.getErrorLogFileName());
		System.out.println("\ngetDeveloperReportFileName - "
				+ iniConfig.getDeveloperReportFileName());

		System.out.println("\nisFuzzyPostalCodeTrue - "
				+ iniConfig.isFuzzyPostalCodeTrue());

		String dbRisSectionName = iniConfig.getDbRisSection();
		String dbGriidcSectionName = iniConfig.getDbGriidcSection();
		String[] dbSections = { dbRisSectionName, dbGriidcSectionName };
		String rdbmsUrl = "joe.com";
		for (String sect : dbSections) {
			System.out.println("\nDB Ini Section Name: "
					+ iniConfig.getDbRisSection());
			String rdbmsType = iniConfig.getDbIniProp(sect, "type");
			String rdbmsJdbcDriverName = iniConfig.getDbIniProp(sect,
					"driverName");
			String rdbmsJdbcPrefix = iniConfig
					.getAppIniProp(sect, "jdbcPrefix");
			String rdbmsHost = iniConfig.getDbIniProp(sect, "host");
			String rdbmsPort = iniConfig.getDbIniProp(sect, "port");
			String rdbmsName = iniConfig.getDbIniProp(sect, "dbname");
			// String rdbmsSchemaName =
			// iniConfig.getPubsToGriiidcIniProp(dbIniSectionName,"schema");
			String rdbmsUser = iniConfig.getDbIniProp(sect, "username");
			String rdbmsPassword = iniConfig.getDbIniProp(sect, "password");
			System.out.println("\nRdbmsConnection [rdbmsType=" + rdbmsType
					+ ", rdbmsHost=" + rdbmsHost + ", rdbmsPort=" + rdbmsPort
					+ ", rdbmsUrl=" + rdbmsUrl + ", rdbmsUser=" + rdbmsUser
					+ ", rdbmsPassword=" + rdbmsPassword + ", rdbmsName="
					+ rdbmsName
					// + ", rdbmsSchemaName=" + rdbmsSchemaName
					+ ", rdbmsJdbcDriverName=" + rdbmsJdbcDriverName
					+ ", rdbmsJdbcPrefix=" + rdbmsJdbcPrefix + "]");
		}
	}

	@Override
	public String getAppGriidcDbSection() {
		return AppGriidcDbSection;
	}

	@Override
	public String getAppRisDbSection() {
		return AppRisDbSection;
	}

	public void setNotificationsSection(String notificationsSection) {
		NotificationsSection = notificationsSection;
	}

	public void setErrorsType(String errorsType) {
		ErrorsType = errorsType;
	}

	public void setPrimayLogFileNameProperty(String primayLogFileNameProperty) {
		PrimayLogFileNameProperty = primayLogFileNameProperty;
	}

	public void setErrorLogFileNameProperty(String errorLogNameProperty) {
		ErrorLogNameProperty = errorLogNameProperty;
	}

	public void setDeveloperLogFileNameProperty(String developerLogFileNameProperty) {
		DeveloperLogFileNameProperty = developerLogFileNameProperty;
	}

	public void setWarningLogFileNameProperty(String warningLogFileNameProperty) {
		WarningLogFileNameProperty = warningLogFileNameProperty;
	}
}
