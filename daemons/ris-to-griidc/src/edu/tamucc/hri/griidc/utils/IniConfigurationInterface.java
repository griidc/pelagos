package edu.tamucc.hri.griidc.utils;

import java.util.Properties;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

public interface IniConfigurationInterface {

	public abstract String getDbIniFileName();

	public abstract String getAppIniFileName();
	
	public abstract String getNotificationsIniFileName();

	public abstract String getDbGriidcSection();

	public abstract String getNotificationsSection();

	public abstract String getDbRisSection();

	public abstract String getAppLogFilesSection();

	public abstract String getAppOtherSection();

	public abstract String getAppEmailSection();
	
	public abstract String getAppGriidcDbSection();
	
	public abstract String getAppRisDbSection();

	/**
	 * get a property within a section of the database ini file
	 * 
	 * @param sectionName
	 * @param propertyName
	 * @return
	 * @throws PropertyNotFoundException
	 * @throws IniSectionNotFoundException 
	 */
	public abstract String getDbIniProp(String sectionName, String propertyName)
			throws PropertyNotFoundException, IniSectionNotFoundException;

	/**
	 * get a property within a section of the notifications ini file
	 * 
	 * @param sectionName
	 * @param propertyName
	 * @return
	 * @throws PropertyNotFoundException
	 * @throws IniSectionNotFoundException 
	 */
	public abstract String getNotificationIniProp(String sectionName,
			String propertyName) throws PropertyNotFoundException,
			IniSectionNotFoundException;

	/**
	 * get a property within a section of the application specific ini file
	 * 
	 * @param sectionName
	 * @param propertyName
	 * @return
	 * @throws PropertyNotFoundException
	 * @throws IniSectionNotFoundException 
	 */
	public abstract String getAppIniProp(String sectionName,
			String propertyName) throws PropertyNotFoundException,
			IniSectionNotFoundException;

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
	public abstract String getIniProp(IniPropertyHandler ini,
			String sectionName, String propertyName)
			throws PropertyNotFoundException, IniSectionNotFoundException;

	public abstract IniPropertyHandler getDbIniInstance();

	public abstract IniPropertyHandler getNotificationsIniInstance();

	public abstract IniPropertyHandler getAppIniInstance();

	public abstract String getWorkingDirectory();

	public abstract String getLogFileDirectory();

	public abstract String getPrimaryLogFileName();

	public abstract String getErrorLogFileName();

	public abstract String getWarningLogFileName();

	public abstract String getDeveloperReportFileName();

	public abstract Properties getEmailProperties()
			throws IniSectionNotFoundException, PropertyNotFoundException;

	public abstract String[] getErrorMsgLogRecipients();

	public abstract String[] getPrimaryMsgLogRecipients()
			throws PropertyNotFoundException;

	public abstract String getMailSender()
			throws IniSectionNotFoundException, PropertyNotFoundException;

	public abstract String getEmailIniProp(String property)
			throws PropertyNotFoundException, IniSectionNotFoundException;

}