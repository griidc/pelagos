package edu.tamucc.hri.griidc.utils;

import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.Vector;

import org.ini4j.Ini;
import org.ini4j.InvalidFileFormatException;
import org.ini4j.Profile.Section;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

public class IniPropertyHandler {
	
	private static boolean Debug = false;

	private Ini iniInstance = null;
	private String fileName = null;
	
	public IniPropertyHandler() {
		// TODO Auto-generated constructor stub
	}
	


	public void init(String fileName) throws InvalidFileFormatException, FileNotFoundException, IOException {
		this.loadIniFile(fileName);
	}
	private  Ini loadIniFile(String fileName)
			throws InvalidFileFormatException, FileNotFoundException,
			IOException {
		this.fileName = fileName;
		this.iniInstance = new Ini(new FileReader(fileName));
		return iniInstance;
	}


	public static void setDebug(boolean trueOrFalse) {
		Debug = trueOrFalse;
	}

	public static boolean isDebug() {
		return Debug;
	}



	public Ini getIniInstance() {
		return iniInstance;
	}



	public String getFileName() {
		return fileName;
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
	public  String getProp(String sectionName, String propertyName)
			throws IniSectionNotFoundException {

		Section section = this.getSection(sectionName);
		String property =  section.get(propertyName);
		if(property == null) {
			throw new IniSectionNotFoundException("Property : " + propertyName + " not found in Section: " + sectionName + " in INI file : " + this.getFileName());
		}
		return property;
	}
	
	private Section getSection(String sectionName) throws IniSectionNotFoundException {
		Section section = this.iniInstance.get(sectionName);
		if(section == null) {
			throw new IniSectionNotFoundException("Section: " + sectionName + " not found in INI file : " + this.getFileName());
		}
		return section;
		
	}

	/**
	 * This turns ini properties on their head to some extent.
	 * In the ini file file within a section there is the form
	 * key = value. GRIIDC uses the notification ini file to 
	 * list email addresses that get messages of a type. So
	 * in the ini file there will be a list of email address (keys, properties)
	 * followed by a comma separated list of values i.e.
	 * [ris-to-griidc]
       ; types:
       ;     riserrors - a list of errors found in the RIS database
       ;     primarylog - a list of actions taken when updating the GRIIDC db from the RIS db
       ;
       joe.holland@tamucc.edu =riserrors,primarylog
       joevholland@gmail.com =riserrors
       jvh.smokeandmirrors@gmail.com=primarylog
	 * @param sectionName
	 * @return
	 */
	public String[] getPropertiesWithinSectionThatContainValue(String sectionName, String targetValue) {
		Vector<String> keys = new Vector<String>();
		Section section = this.iniInstance.get(sectionName);
		for (String key : section.keySet()) { // in the above example - email addresses
			String values = section.get(key);
			if (values.contains(targetValue)) {
				keys.add(key);
			}

		}
		String[] s = new String[keys.size()];
		return keys.toArray(s);
	}
	
	
	public static void main(String[] args) throws PropertyNotFoundException,
			InvalidFileFormatException, IOException {

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
		
			String rdbmsUrl = "joe.com";
		
		
	}

}
