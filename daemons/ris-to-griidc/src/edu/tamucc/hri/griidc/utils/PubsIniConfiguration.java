package edu.tamucc.hri.griidc.utils;

import java.io.IOException;
import java.util.Iterator;
import java.util.Properties;
import java.util.Set;

import org.ini4j.InvalidFileFormatException;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

/**
 * This class manages the configuration files in the ini format. There are three
 * ini files of interest all to be found in the base directory /etc/griidc
 * "db.ini", "notifications.ini", "pusToGriidc.ini"
 * 
 * PubsConstants also has a bunch of constants that are used for database
 * connections
 * 
 * @see IniConfigurationBase
 */
public class PubsIniConfiguration extends IniConfigurationBase {

	// section name found in notifications.ini
	private static final String PubsNotificationsSection = "pub-to-griidc"; // notifications.ini
	// property type in notifications.ini in NotificationsSection section
	private static final String PubsErrorsType = "puberrors"; // notifications.ini

	// property names found in application ini
	private static final String PubsPrimayLogFileNameProperty = "pubsPrimaryLogName";// pub-to-griidc.ini
	private static final String PubsErrorLogFileNameProperty = "pubsErrorLogName";// pub-to-griidc.ini
	private static final String PubsDeveloperLogFileNameProperty = "pubsDeveloperLogName";// pub-to-griidc.ini
	private static final String PubsWarningLogFileNameProperty = "pubsWarningLogName";// pub-to-griidc.ini

	public PubsIniConfiguration() {
       this.setNotificationsSection(PubsNotificationsSection);
       this.setErrorsType(PubsErrorsType);
       this.setDeveloperLogFileNameProperty(PubsDeveloperLogFileNameProperty);
       this.setErrorLogFileNameProperty(PubsErrorLogFileNameProperty);
       this.setPrimayLogFileNameProperty(PubsPrimayLogFileNameProperty);
       this.setWarningLogFileNameProperty(PubsWarningLogFileNameProperty);
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

}
