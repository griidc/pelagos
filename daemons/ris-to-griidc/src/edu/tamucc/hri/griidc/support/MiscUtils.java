package edu.tamucc.hri.griidc.support;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.Properties;

import edu.tamucc.hri.griidc.CountryTableCache;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TelephoneNumberException;

public class MiscUtils {

	private static ProjectNumberFundingCycleCache projectNumberFundingCycleCacheInstance = null;

	// Even if GRIIDC is still alive on Dec 31, 3000 this code won't be running.
	public final static String MaxDateString = "3000-12-31";
	public final static String MinDateString = "1970-01-01";
	public static java.sql.Date MaxDate = java.sql.Date.valueOf(MaxDateString);
	public static java.sql.Date MinDate = java.sql.Date.valueOf(MinDateString);
	public static boolean Noisy = false;

	public static int primaryLogMsgCount = 0;
	public static int risErrorLogCount = 0;

	private static String RisErrorMsgLogRecipients = "ris.error.msg.log.recipients";
	private static String PrimaryMsgLogRecipients = "primary.msg.log.recipients";
	private static String GriidcMailSender = "mail.from";

	private static boolean Debug = false;
	public static String BreakLine = "\n\n************************************************************************\n";

	public static ProjectNumberFundingCycleCache getProjectNumberFundingCycleCache() {
		if (MiscUtils.projectNumberFundingCycleCacheInstance == null) {
			MiscUtils.projectNumberFundingCycleCacheInstance = new ProjectNumberFundingCycleCache();
		}
		return MiscUtils.projectNumberFundingCycleCacheInstance;
	}

	public static boolean isValidPostalAreaData(String state, String city,
			String zip) throws MissingArgumentsException {

		StringBuffer errorMsg = new StringBuffer();
		boolean completeParms = true;
		if (MiscUtils.isStringEmpty(state)) {
			errorMsg.append("State is NULL or empty");
			completeParms = false;
		} else
			errorMsg.append("State=" + state);

		if (MiscUtils.isStringEmpty(city)) {
			errorMsg.append(", City is NULL or empty");
			completeParms = false;
		} else
			errorMsg.append(", City =" + city);

		if (MiscUtils.isStringEmpty(zip)) {
			errorMsg.append(", Zip is NULL or empty");
			completeParms = false;
		} else
			errorMsg.append(", Zip=" + zip);
		if (!completeParms) {
			MissingArgumentsException ex = new MissingArgumentsException(
					"Invalid or missing Postal Area information: "
							+ errorMsg.toString());
			throw ex;
		}
		return true;

	}

	/**
	 * used to turn a fully qualified class name like com.foo.bar.Xyzzy into
	 * Xyzzy
	 * 
	 * @param clName
	 * @return
	 */
	public static String simplifyClassName(String clName) {
		// find the last '.'
		int lastDot = clName.lastIndexOf('.');
		if (lastDot == -1)
			return clName;
		return clName.substring(lastDot + 1);
	}

	/**
	 * open for writing output - must have complete path file name
	 * 
	 * @param absoluteFileName
	 * @return
	 * @throws IOException
	 */
	public static BufferedWriter openOutputFile(String absoluteFileName)
			throws IOException {
		return MiscUtils.openOutputFile(absoluteFileName, false);
	}

	/**
	 * * open for writing output - must have complete path file name if append
	 * is true - append output to end of file
	 * 
	 * @param absoluteFileName
	 * @param append
	 * @return
	 * @throws IOException
	 */
	public static BufferedWriter openOutputFile(String absoluteFileName,
			boolean append) throws IOException {
		File file = new File(absoluteFileName);

		FileWriter fileWriter = new FileWriter(file.getAbsoluteFile(), append);

		file.createNewFile();

		BufferedWriter bw = new BufferedWriter(fileWriter);
		return bw;
	}

	public static String getPrimaryLogFileName() throws FileNotFoundException,
			PropertyNotFoundException {
		String fileName = RisPropertiesAccess.getInstance().getProperty(
				"log.file.name");
		return MiscUtils.getAbsoluteFileName(fileName);
	}

	public static String getRisErrorLogFileName() throws FileNotFoundException,
			PropertyNotFoundException {
		String fileName = RisPropertiesAccess.getInstance().getProperty(
				"ris.data.error.log.name");
		MiscUtils.debugOut("getRisErrorLogFileName() - " + fileName);
		return MiscUtils.getAbsoluteFileName(fileName);
	}

	public static String getDeveloperReportFileName()
			throws FileNotFoundException, PropertyNotFoundException {
		String fileName = RisPropertiesAccess.getInstance().getProperty(
				"dev.report.file.name");
		return MiscUtils.getAbsoluteFileName(fileName);
	}

	/**
	 * the email properties are in the application properties file
	 * 
	 * @return
	 * @throws FileNotFoundException
	 */
	public static Properties getEmailConfigProperties()
			throws FileNotFoundException {
		return RisPropertiesAccess.getInstance().getPropertiesInstance();
	}

	private static BufferedWriter primarylogFileWriter = null;

	private static BufferedWriter risErrorLogFileWriter = null;

	private static BufferedWriter developerReportWriter = null;

	public static void closePrimaryLogFile() throws IOException,
			PropertyNotFoundException {
		MiscUtils.getPrimaryLogFileWriter().close();
	}

	public static void closeRisErrorLogFile() throws IOException,
			PropertyNotFoundException {
		MiscUtils.getRisErrorLogFileWriter().close();
	}

	public static void closeDeveloperReportFile() throws IOException,
			PropertyNotFoundException {
		MiscUtils.getRisErrorLogFileWriter().close();
	}

	public static final String DashLine = "--------------------------------------------------";

	public static int mainLogEntryNumber = 1;

	public static String incrementMain() {
		String msg = " " + mainLogEntryNumber + " ";
		mainLogEntryNumber++;
		return msg;
	}

	public static int writeToPrimaryLogFile(String msg) throws IOException,
			PropertyNotFoundException {
		MiscUtils.getPrimaryLogFileWriter().write(
				"\n   " + incrementMain() + "\t" + msg);
		MiscUtils.getPrimaryLogFileWriter().write("\n" + DashLine);
		MiscUtils.primaryLogMsgCount++;
		return MiscUtils.primaryLogMsgCount;
	}

	public static int writeToPrimaryLogFile(Collection<String> msgs)
			throws IOException, PropertyNotFoundException {

		Iterator<String> it = msgs.iterator();
		boolean firstLine = true;
		while (it.hasNext()) {
			String msg = it.next();
			if (firstLine) {
				MiscUtils.getPrimaryLogFileWriter().write(
						"\n   " + incrementMain() + "\t" + msg);
			} else {
				MiscUtils.getPrimaryLogFileWriter().write("\n\t" + msg);
			}
			firstLine = false;
		}
		MiscUtils.getPrimaryLogFileWriter().write("\n" + DashLine);
		MiscUtils.primaryLogMsgCount++;
		return MiscUtils.primaryLogMsgCount;
	}

	public static BufferedWriter openPrimaryLogFile() throws IOException,
			PropertyNotFoundException {
		return MiscUtils.getPrimaryLogFileWriter();
	}

	public static BufferedWriter getPrimaryLogFileWriter() throws IOException,
			PropertyNotFoundException {

		if (MiscUtils.primarylogFileWriter == null) {

			MiscUtils.primarylogFileWriter = MiscUtils.openOutputFile(MiscUtils
					.getPrimaryLogFileName());
			MiscUtils.primarylogFileWriter
					.write("**********************************************************\n");
			MiscUtils.primarylogFileWriter
					.write("** Log File for SyncGriidcToRis \n");
			MiscUtils.primarylogFileWriter.write("** File opened: "
					+ MiscUtils.getDateAndTime());
			MiscUtils.primarylogFileWriter.write("  ** \n");
			MiscUtils.primarylogFileWriter.write("** \n");
			MiscUtils.primarylogFileWriter
					.write("**********************************************************");
		}
		return MiscUtils.primarylogFileWriter;
	}

	public static int RisEntryNumber = 1;

	public static String incrementRisEntryNumber() {
		String msg = " " + RisEntryNumber + " ";
		RisEntryNumber++;
		return msg;
	}

	public static int writeToRisErrorLogFile(String msg) throws IOException,
			PropertyNotFoundException {
		MiscUtils.getRisErrorLogFileWriter().write(
				"\n" + incrementRisEntryNumber() + "\t" + msg);
		MiscUtils.getRisErrorLogFileWriter().write("\n" + DashLine);
		MiscUtils.risErrorLogCount++;
		return MiscUtils.risErrorLogCount;
	}

	public static int getPrimaryLogMsgCount() {
		return MiscUtils.primaryLogMsgCount;
	}

	public static void resetPrimaryLogMsgCount() {
		MiscUtils.primaryLogMsgCount = 0;
	}

	public static int getRisErrorLogCount() {
		return risErrorLogCount;
	}

	public static void resetRisErrorLogCount() {
		MiscUtils.risErrorLogCount = 0;
	}

	public static int writeToRisErrorLogFile(Collection<String> msgs)
			throws IOException, PropertyNotFoundException {

		Iterator<String> it = msgs.iterator();
		boolean firstLine = true;
		while (it.hasNext()) {

			String msg = it.next();
			if (firstLine) {
				MiscUtils.getRisErrorLogFileWriter().write(
						"\n" + incrementRisEntryNumber() + "\t" + msg);
			} else {
				MiscUtils.getRisErrorLogFileWriter().write("\n\t" + msg);
			}
			firstLine = false;
		}
		MiscUtils.getRisErrorLogFileWriter().write("\n" + DashLine);
		MiscUtils.risErrorLogCount++;
		return MiscUtils.risErrorLogCount;
	}

	public static BufferedWriter openRisErrorLogFile() throws IOException,
			PropertyNotFoundException {
		return getRisErrorLogFileWriter();
	}

	public static void debugOut(String s) {
		if (Debug) {
			System.out.println("MiscUtils: " + s);
		}
	}

	public static BufferedWriter getRisErrorLogFileWriter() throws IOException,
			PropertyNotFoundException {

		if (MiscUtils.risErrorLogFileWriter == null) {
			String relogFileName = MiscUtils.getRisErrorLogFileName();
			debugOut("ris error log file name = " + relogFileName);
			MiscUtils.risErrorLogFileWriter = MiscUtils
					.openOutputFile(relogFileName);
			MiscUtils.risErrorLogFileWriter
					.write("**********************************************************\n");
			MiscUtils.risErrorLogFileWriter
					.write("** RIS Data Error Log file\n");
			MiscUtils.risErrorLogFileWriter.write("** File opened: "
					+ MiscUtils.getDateAndTime());
			MiscUtils.risErrorLogFileWriter.write("  ** \n");
			MiscUtils.risErrorLogFileWriter.write("** \n");
			MiscUtils.risErrorLogFileWriter
					.write("**********************************************************");
		}
		return MiscUtils.risErrorLogFileWriter;
	}

	/**
	 * make a new container for Strings compatible with writeToLog above
	 * 
	 * @param s
	 * @return
	 */
	public static Collection<String> newStringCollection(String s) {
		ArrayList<String> al = new ArrayList<String>();
		al.add(s);
		return al;
	}

	public static void writeToDeveloperReport(String msg) throws IOException,
			PropertyNotFoundException {
		MiscUtils.getDeveloperReportFileWriter().write("\n" + msg);
		MiscUtils.getDeveloperReportFileWriter().write("\n" + DashLine);
	}

	public static BufferedWriter openDeveloperReportFile() throws IOException,
			PropertyNotFoundException {
		return getDeveloperReportFileWriter();
	}

	public static BufferedWriter getDeveloperReportFileWriter()
			throws IOException, PropertyNotFoundException {

		if (MiscUtils.developerReportWriter == null) {
			MiscUtils.developerReportWriter = MiscUtils
					.openOutputFile(MiscUtils.getDeveloperReportFileName());
			MiscUtils.developerReportWriter
					.write("**********************************************************\n");
			MiscUtils.developerReportWriter
					.write("** Developer Report for SyncGriidcToRis \n");
			MiscUtils.developerReportWriter.write("** File opened: "
					+ MiscUtils.getDateAndTime());
			MiscUtils.developerReportWriter.write("  ** \n");
			MiscUtils.developerReportWriter.write("** \n");
			MiscUtils.developerReportWriter
					.write("**********************************************************");
		}
		return MiscUtils.developerReportWriter;
	}

	public static void writeStringToFile(String fileName, String msg)
			throws IOException {
		BufferedWriter br = MiscUtils.openOutputFile(MiscUtils
				.getAbsoluteFileName(fileName));
		br.write(msg);
		;
		br.close();
	}

	/**
	 * make a new container for Strings compatible with writeToLog above
	 * 
	 * @param s
	 * @return
	 */
	public static Collection<String> newStringCollection() {
		ArrayList<String> al = new ArrayList<String>();
		return al;
	}

	public static void writeIt(BufferedWriter bw, String msg)
			throws IOException {
		if (bw == null) {
			System.out.println(msg);
		} else {
			bw.write(msg);
		}

	}

	public static String getDateAndTime() {
		Date date = new Date();
		SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd:HH-mm-ss");
		return sdf.format(date);
	}

	public static BufferedReader openInputFile(String fName) throws IOException {
		String fileName = MiscUtils.getAbsoluteFileName(fName);
		FileReader fr = new FileReader(new File(fileName));
		BufferedReader br = new BufferedReader(fr);
		return br;
	}

	public static String getAbsoluteFileName(final String fName) {
		return RisPropertiesAccess.getWorkingDirectory() + "/data/" + fName;
	}

	public static void printStringArray(String[] sa) {
		for (String s : sa) {
			System.out.println(s);
		}
	}

	public static String stringArrayToString(String[] sa) {
		StringBuffer sb = new StringBuffer();
		for (String s : sa) {
			sb.append(s + "\t");
		}
		return sb.toString();
	}

	static String[] testMessages = {
			"The quick Brown Fox",
			"Jumped the log",
			"Says ding ding ding\n\tSome WHITE SPACE HERE 1243::::987987",
			"centreofdocumentationresearchandexperimentationonaccidentalwaterpollution",
			"computerscienceslaboratoryformechanicsandengineeringscienceslimsicnrs",
			"universityoftexasatelpaso",
			"mediterraneaninstituteforadvancedstudies",
			"universidadefederaldocearabrazil", "stgeorgehighschool" };

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		String[] tables = null;
		try {
			for (String s : testMessages) {

				MiscUtils.writeToRisErrorLogFile(s);
				MiscUtils.writeToPrimaryLogFile(s);
				System.out.println("Before squeeze: " + s);
				String sq = squeeze(s);
				System.out.println("After   squeeze: " + sq);

			}
			MiscUtils.closePrimaryLogFile();
			MiscUtils.closeRisErrorLogFile();
			String[] rec = MiscUtils.getRisErrorMsgLogRecipients();

			System.out.println("RIS Error recipients:");
			for (String s : rec) {
				System.out.println("\t" + s);
			}

			rec = MiscUtils.getPrimaryMsgLogRecipients();

			System.out.println("Primary Log recipients:");
			for (String s : rec) {
				System.out.println("\t" + s);
			}
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

	}

	public static Map<String, String> countryMap = Collections
			.synchronizedMap(new HashMap());

	public static Map<String, String> getCountryMap() {
		if (MiscUtils.countryMap.isEmpty()) {
			MiscUtils.countryMap.put("USA", "United States");
			MiscUtils.countryMap.put("CAN", "Canada");
			MiscUtils.countryMap.put("FRA", "France");
			MiscUtils.countryMap.put("AUS", "Australia");
			MiscUtils.countryMap.put("NLD", "Netherlands");
			MiscUtils.countryMap.put("CHN", "China");
			MiscUtils.countryMap.put("DEU", "Germany");
			MiscUtils.countryMap.put("DNK", "Denmark");
			MiscUtils.countryMap.put("BRA", "Brazil");
			MiscUtils.countryMap.put("DEU", "Germany");
			MiscUtils.countryMap.put("PUR", "Peru");
			MiscUtils.countryMap.put("NOR", "Norway");
		}
		return countryMap;
	}

	public static String getRisCountryCorrection(String risCountry) {
		String correction = MiscUtils.getCountryMap().get(risCountry);
		if (correction == null)
			correction = risCountry;
		return correction;
	}

	/**
	 * return true if the string is null or length zero
	 * 
	 **/
	public static boolean isStringEmpty(String s) {
		if (s == null)
			return true;
		else if (s.length() == 0)
			return true;
		return false;
	}

	public static final String[] PhoneNumberValidationPatterns = { "\\d{10}",
			"\\d{3}[-\\.\\s]\\d{3}[-\\.\\s]\\d{4}",
			"\\d{3}-\\d{3}-\\d{4}\\s(x|(ext))\\d{3,5}",
			"\\(\\d{3}\\)-\\d{3}-\\d{4}", "\\(\\d{3}\\) \\d{3}-\\d{4}" };

	public static boolean isValidPhoneNumber(String phoneNo)
			throws TelephoneNumberException {
		if (phoneNo == null)
			throw new TelephoneNumberException("Telephone Number is empty");
		if (phoneNo.trim().length() == 0)
			throw new TelephoneNumberException("Telephone Number is blank");
		for (String regEx : PhoneNumberValidationPatterns) {
			if (phoneNo.trim().matches(regEx)) {
				return true;
			}
		}
		throw new TelephoneNumberException("Telephone Number " + phoneNo
				+ " is not a recognized format.");
	}

	/**
	 * for phone number that has extension on the end The ' x' is used in RIS
	 * records
	 */
	private static final String[] ExtensionPatterns = { " x", " ex", " EX",
			" X" };

	public static String[] separateTelephoneNumberExtension(
			String telephoneNumber) {
		return MiscUtils
				.separateTelephoneNumberExtension(telephoneNumber, null);
	}

	public static String[] separateTelephoneNumberExtension(
			String telephoneNumber, String[] extPatterns) {
		String[] patterns = ExtensionPatterns;
		if (extPatterns != null) {
			patterns = extPatterns;
		}
		String phoneNumber = telephoneNumber.trim();
		String[] phoneAndExt = new String[2];

		int ndx = -1;
		for (String pattern : patterns) {
			ndx = phoneNumber.indexOf(pattern);
			if (ndx > 0) { // found id
				phoneAndExt[0] = phoneNumber.substring(0, ndx).trim();
				ndx += pattern.length();
				phoneAndExt[1] = phoneNumber.substring(ndx).trim();
				return phoneAndExt;
			}
		}
		// no matches
		phoneAndExt[0] = phoneNumber;
		phoneAndExt[1] = null;
		return phoneAndExt;
	}

	public static boolean doesCountryExist(int targetCountry) {
		return CountryTableCache.getInstance().doesCountryExist(targetCountry);
	}

	/**
	 * squeeze out the whitespace and non-alphanumeric characters and convert to
	 * all lower case
	 */
	public static String squeeze(String s) {
		StringBuffer sb = new StringBuffer();
		char[] ca = s.toCharArray();
		char c = 0;
		for (int i = 0; i < ca.length; i++) {
			if (Character.isDigit(ca[i]) || Character.isLetter(ca[i]))
				sb.append(ca[i]);
		}
		return sb.toString().toLowerCase();
	}

	/**
	 * Department and Institution have Delivery Point Ris has Addr1 and Addr2
	 * 
	 * @param addr1
	 * @param addr2
	 * @return
	 */
	public static String makeDeliveryPoint(String addr1, String addr2) {
		String s = addr1 + " " + addr2;
		return s.trim();
	}

	public static String[] getRisErrorMsgLogRecipients()
			throws FileNotFoundException, PropertyNotFoundException {
		String rs = RisPropertiesAccess.getInstance().getProperty(
				MiscUtils.RisErrorMsgLogRecipients);
		String[] splits = rs.split(" ");
		return splits;
	}

	public static String[] getPrimaryMsgLogRecipients()
			throws FileNotFoundException, PropertyNotFoundException {
		String rs = RisPropertiesAccess.getInstance().getProperty(
				MiscUtils.PrimaryMsgLogRecipients);
		String[] splits = rs.split(" ");
		return splits;
	}

	public static String getGriidcMailSender() throws FileNotFoundException,
			PropertyNotFoundException {
		return RisPropertiesAccess.getInstance().getProperty(
				MiscUtils.GriidcMailSender);
	}

	public static String readFileToBuffer(String fileName) throws IOException {

		FileReader fr = new FileReader(new File(fileName));
		BufferedReader br = new BufferedReader(fr);
		String line = null;
		StringBuffer sb = new StringBuffer();
		boolean firstLine = true;
		while ((line = br.readLine()) != null) {
			if (!firstLine)
				sb.append("\n");
			sb.append(line);
			firstLine = false;
		}
		return sb.toString();
	}

	public static java.sql.Date getMaxDate() {
		return MaxDate;
	}

	public static java.sql.Date getMinDate() {
		return MinDate;
	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
	}

}
