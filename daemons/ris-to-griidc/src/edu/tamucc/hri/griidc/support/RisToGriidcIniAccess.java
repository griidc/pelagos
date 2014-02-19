package edu.tamucc.hri.griidc.support;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStream;
import java.util.Enumeration;

import org.ini4j.Ini;
import org.ini4j.InvalidFileFormatException;
import org.ini4j.Profile.Section;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

public class RisToGriidcIniAccess {

	private static String dbIniFileName = "/etc/griidc/db.ini";
	private static String notificationsFileName = "/etc/griidc/notifications.ini";
	private static String appIniFileName = "/etc/griidc/ris-to-griidc.ini";
	private static String PropertiesFilePath = dbIniFileName;
	
	private static String[] fileName = { dbIniFileName, notificationsFileName,appIniFileName };
	

	private static String RisIniSection = "RIS_RO";
	private static String GriidcIniSection = "GRIIDC_RW";
	private static String RisToGriidcNotifications = "ris-to-griidc";
	
	private boolean propertiesLoaded = false;
	private static boolean Debug = false;
	
	
	
	private static String[] notifyKeys = {
		"risErrors",
		"primaryLog",
		"risErrorsSender",
		"primaryLogSender"
	};
    private static RisToGriidcIniAccess risToGriidcIniAccessInstance = null;
    
    public static final String DatabaseMappingFileName = "database.mapping.specification.file";
    
    /**
     * singleton implementation
     * @return
     * @throws FileNotFoundException
     */
	public static RisToGriidcIniAccess getInstance() throws FileNotFoundException {
		if(risToGriidcIniAccessInstance == null) {
			risToGriidcIniAccessInstance = new RisToGriidcIniAccess();
		}
		return risToGriidcIniAccessInstance;
	}
	
	
	private RisToGriidcIniAccess() {
		super();
		String fn = PropertiesFilePath;
		try {
			Ini ini = new Ini(new FileReader(fn));
		} catch (InvalidFileFormatException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		propertiesLoaded = false;
	}

	public static String getIniSourceFile() {
		return RisToGriidcIniAccess.PropertiesFilePath;
	}
	public static void setDebug(boolean trueOrFalse) {
		Debug = trueOrFalse;
	}


	/**
	 * return the value of the property
	 * 
	 * @param propertyName
	 * @return
	 */
	public String getProperty(String propertyName)
			throws FileNotFoundException, PropertyNotFoundException {
		String prop = null;
		//Ini p = this.getIniInstance();
		//String prop = p.getProperty(propertyName);
	//	if(prop == null)
	//		throw new PropertyNotFoundException("No property: " + propertyName + " found in file: " + PropertiesFilePath);
		return prop;
	}


	public static String getWorkingDirectory() {
		return System.getProperty("user.dir");
	}
	
	

	public static void main(String[] args) throws FileNotFoundException, PropertyNotFoundException  {

		RisToGriidcIniAccess api = RisToGriidcIniAccess.getInstance();

		RisToGriidcIniAccess.setDebug(true);
		//api.init();

		String[] properties = null;
	}

}
