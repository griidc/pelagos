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

	public static String propertiesFilePath = "/etc/griidc/db.ini";

	private Properties propertiesInstance = null;
	
	private static final int FAILURE = -1;
	private static boolean propertiesLoaded = false;

	private static boolean Debug = false;
	private static String DebugPrefix = ">>>>>  ";
	private static String DeamServiceHost = null;
	
    private static RisPropertiesAccess risPropertiesAccessInstance = null;
    
    public static final String DatabaseMappingFileName = "database.mapping.specification.file";
    
    /**
     * singleton implementation
     * @return
     * @throws FileNotFoundException
     */
	public static RisPropertiesAccess getInstance() throws FileNotFoundException {
		if(risPropertiesAccessInstance == null) {
			risPropertiesAccessInstance = new RisPropertiesAccess();
		    //risPropertiesAccessInstance.init();
		}
		return risPropertiesAccessInstance;
	}
	
	
	private RisPropertiesAccess() {
		super();
		propertiesLoaded = false;
	}

	public static String getPropertiesSourceFile() {
		return RisPropertiesAccess.propertiesFilePath;
	}
	public static void setDebug(boolean trueOrFalse) {
		Debug = trueOrFalse;
	}

	public String[] getProperties() throws FileNotFoundException {
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
			throw new PropertyNotFoundException("No property: " + propertyName + " found in file: " + propertiesFilePath);
		return prop;
	}

	public String getDatabaseMappingFileName() 
			throws FileNotFoundException, PropertyNotFoundException {
		return this.getProperty(DatabaseMappingFileName);
	}
	public Properties getPropertiesInstance() {
		if (this.propertiesInstance == null) {

			InputStream inputStream = null;

			try {

				inputStream = new FileInputStream(
						RisPropertiesAccess.propertiesFilePath);
				this.propertiesInstance = new Properties();
				String s = (this.propertiesInstance == null) ? " properties is null "
						: "propertie is allocated";
				this.propertiesInstance.load(inputStream);
				return this.propertiesInstance;
			} catch (FileNotFoundException e1) {
				System.err
						.println("RisPropertiesAccess.getPropertiesInstance() properties file not found file : "
								+ this.propertiesFilePath
								+ " "
								+ e1.getMessage());
				return null;
			} catch (IOException e2) {
				System.err
						.println("RisPropertiesAccess.getPropertiesInstance() IOException on properties file : "
								+ this.propertiesFilePath
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
