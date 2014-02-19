package edu.tamucc.hri.griidc;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.util.Enumeration;
import java.util.Properties;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

public class RisPropertiesAccess {

	private static String dbIniFileName = "/etc/griidc/db.ini";
	private static String notificationsFileName = "/etc/griidc/notifications.ini";
	private static String appIniFileName = "/etc/griidc/ris-to-griidc.ini";
	private static String PropertiesFilePath = dbIniFileName;
	
	private static String[] fileName = { dbIniFileName, notificationsFileName,appIniFileName };
	

	private static String RisIniSection = "RIS_RO";
	private static String GriidcIniSection = "GRIIDC_RW";
	private static String RisToGriidcNotifications = "ris-to-griidc";
	

	private Properties propertiesInstance = null;
	private boolean propertiesLoaded = false;
	private static boolean Debug = false;
	
    private static RisPropertiesAccess risPropertiesAccessInstance = null;
    
    
    /**
     * singleton implementation
     * @return
     * @throws FileNotFoundException
     */
	public static RisPropertiesAccess getInstance() throws FileNotFoundException {
		if(risPropertiesAccessInstance == null) {
			risPropertiesAccessInstance = new RisPropertiesAccess();
		}
		return risPropertiesAccessInstance;
	}
	
	
	private RisPropertiesAccess() {
		super();
		propertiesLoaded = false;
	}

	public static void setDebug(boolean trueOrFalse) {
		Debug = trueOrFalse;
	}

	private String[] getProperties() throws FileNotFoundException {
		// loadProperties();
		String[] props = new String[this.propertiesInstance.size()];
		Enumeration<Object> es = this.propertiesInstance.keys();
		int i = 0;
		while (es.hasMoreElements()) {
			String key = (String) es.nextElement();
			props[i++] = key + ": " + this.propertiesInstance.getProperty(key);
		}

		return props;
	}

	/**
	 * return the value of the property
	 * 
	 * @param propertyName
	 * @return
	 */
	public String getProperty(String propertyName)
			throws FileNotFoundException, PropertyNotFoundException {
		Properties p = this.getPropertiesInstance();
		String prop = p.getProperty(propertyName);
		if(prop == null)
			throw new PropertyNotFoundException("No property: " + propertyName + " found in file: " + PropertiesFilePath);
		return prop;
	}

	public Properties getPropertiesInstance() {
		if (this.propertiesInstance == null) {

			InputStream inputStream = null;

			try {

				inputStream = new FileInputStream(
						RisPropertiesAccess.PropertiesFilePath);
				this.propertiesInstance = new Properties();
				String s = (this.propertiesInstance == null) ? " properties is null "
						: "propertie is allocated";
				this.propertiesInstance.load(inputStream);
				return this.propertiesInstance;
			} catch (FileNotFoundException e1) {
				System.err
						.println("RisPropertiesAccess.getPropertiesInstance() properties file not found file : "
								+ this.PropertiesFilePath
								+ " "
								+ e1.getMessage());
				return null;
			} catch (IOException e2) {
				System.err
						.println("RisPropertiesAccess.getPropertiesInstance() IOException on properties file : "
								+ this.PropertiesFilePath
								+ " "
								+ e2.getMessage());
				return null;
			} catch (Exception e3) {

				return null;
			}
		}
		return this.propertiesInstance;
	}

	public static String getWorkingDirectory() {
		return System.getProperty("user.dir");
	}
	
	public void saveEmailProperties() {

    	/***
	    try {
	        Properties props = new Properties();
	        props.setProperty("ServerAddress", serverAddr);
	        props.setProperty("ServerPort", ""+serverPort);
	        props.setProperty("ThreadCount", ""+threadCnt);
	        File f = new File("server.properties");
	        OutputStream out = new FileOutputStream( f );
	        props.store(out, "This is an optional header comment string");
	    }
	    catch (Exception e ) {
	        e.printStackTrace();
	    }
	    ***/
	}
	
	

	public static void main(String[] args) throws FileNotFoundException, PropertyNotFoundException  {

		RisPropertiesAccess api = RisPropertiesAccess.getInstance();

		RisPropertiesAccess.setDebug(true);
		//api.init();

		String[] properties = api.getProperties();
		System.out.println("\n\n --- Properties ---");
		for (int i = 0; i < properties.length; i++)
			System.out.println(properties[i]);

		
		
	}
}
