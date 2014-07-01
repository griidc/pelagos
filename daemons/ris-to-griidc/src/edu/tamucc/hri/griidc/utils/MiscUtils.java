package edu.tamucc.hri.griidc.utils;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.PrintStream;
import java.nio.charset.Charset;
import java.text.SimpleDateFormat;
import java.util.Collection;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.TelephoneNumberException;
import edu.tamucc.hri.griidc.exception.TelephoneNumberMissingException;
import edu.tamucc.hri.griidc.exception.TelephoneNumberWrongFormatException;
import edu.tamucc.hri.griidc.rdbms.RdbmsConstants;
import edu.tamucc.hri.griidc.ris.CountryTableCache;

public class MiscUtils {

	private static ProjectNumberFundingCycleCache projectNumberFundingCycleCacheInstance = null;

	public final static String MaxDateString = "3000-12-31";
	public final static String MinDateString = "1970-01-01";
	public static java.sql.Date MaxDate = java.sql.Date.valueOf(MaxDateString);
	public static java.sql.Date MinDate = java.sql.Date.valueOf(MinDateString);

	public static int primaryLogMsgCount = 0;
	public static int errorLogCount = 0;
	public static int warningLogCount = 0;

	public static String developerReportFileName = PubsConstants.DeveloperReportFileName;
	public static String primaryLogFileName = PubsConstants.PrimaryLogFileName;
	public static String errorLogFileName = PubsConstants.ErrorLogFileName;
	public static String warningLogFileName = PubsConstants.WarningLogFileName;
	
	public static String BreakLine = "\n\n************************************************************************\n";


	public static boolean DeBug = false;

	public MiscUtils() {
		// TODO Auto-generated constructor stub
	}

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

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

	public static final String[] PhoneNumberValidationPatterns = { "\\d{10}",
			"\\d{3}[-\\.\\s]\\d{3}[-\\.\\s]\\d{4}",
			"\\d{3}-\\d{3}-\\d{4}\\s(x|(ext))\\d{3,5}",
			"\\(\\d{3}\\)-\\d{3}-\\d{4}", "\\(\\d{3}\\) \\d{3}-\\d{4}" };

	public static boolean isValidPhoneNumber(String phoneNo)
			throws TelephoneNumberException {
		if (phoneNo == null || phoneNo.trim().length() == 0)
			throw new TelephoneNumberMissingException("Telephone Number is blank or empty");
		for (String regEx : PhoneNumberValidationPatterns) {
			if (phoneNo.trim().matches(regEx)) {
				return true;
			}
		}
		throw new TelephoneNumberWrongFormatException("Telephone Number " + phoneNo
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
	public static String getDeveloperReportFileName() {
		return developerReportFileName;
	}

	public static void setDeveloperReportFileName(String developerReportFileName) {
		MiscUtils.developerReportFileName = developerReportFileName;
	}

	public static String getPrimaryLogFileName() {
		return primaryLogFileName;
	}

	public static void setPrimaryLogFileName(String primaryLogFileName) {
		MiscUtils.primaryLogFileName = primaryLogFileName;
	}

	public static String getErrorLogFileName() {
		return errorLogFileName;
	}

	public static void setErrorLogFileName(String errorLogFileName) {
		MiscUtils.errorLogFileName = errorLogFileName;
	}

	public static String getWarningLogFileName() {
		return warningLogFileName;
	}

	public static void setWarningLogFileName(String warningLogFileName) {
		MiscUtils.warningLogFileName = warningLogFileName;
	}

	public static void writeStringToFile(String fileName, String msg)
			throws IOException {
		writeStringToFile(MiscUtils.getWorkingDirectory(), fileName, msg);
	}
	
	public static void writeStringToFile(String path,String fileName, String msg)
			throws IOException {
		BufferedWriter br = MiscUtils.openOutputFile(MiscUtils
				.prependPathToFileName(path,fileName));
		br.write(msg);
		br.close();
	}

	public static void writeStringToFilePathFile(String filePathName, String buffer) throws IOException {
		BufferedWriter br = MiscUtils.openOutputFile(filePathName);
		br.write(buffer);
		br.close();
	}
	public static String prependPathToFileName(final String path, final String fName) {
		return path + File.separator + fName;
	}
	public static String prependUserDirDataPrefixToFileName(final String fName) {
		return prependPathToFileName(MiscUtils.getWorkingDirectory(),fName);
	}
	//public static String removeUserDirDataPrefixfromFileName(final String fName) {
	//	return MiscUtils.getWorkingDirectory() + File.separator + fName;
	//}

	public static String getDateAndTime() {
		Date date = new Date();
		SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd:HH-mm-ss");
		return sdf.format(date);
	}

	public static String getWorkingDirectory() {
		return System.getProperty("user.dir");
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

	private static BufferedWriter primarylogFileWriter = null;

	private static BufferedWriter errorLogFileWriter = null;

	private static BufferedWriter developerReportWriter = null;

	private static BufferedWriter warningLogFileWriter = null;

	public static void closePrimaryLogFile() throws IOException {
		MiscUtils.getPrimaryLogFileWriter().close();
	}

	public static void closeErrorLogFile() throws IOException {
		MiscUtils.getErrorLogFileWriter().close();
	}

	public static void closeDeveloperReportFile() throws IOException {
		MiscUtils.getDeveloperReportFileWriter().close();
	}

	public static void closeWarningReportFile() throws IOException {
		MiscUtils.getWarningLogFileWriter().close();
	}

	public static final String DashLine = "--------------------------------------------------";

	public static int mainLogEntryNumber = 1;

	public static String incrementMain() {
		String msg = " " + mainLogEntryNumber + " ";
		mainLogEntryNumber++;
		return msg;
	}

	public static int writeToPrimaryLogFile(String msg) {
		try {
			MiscUtils.getPrimaryLogFileWriter().write(
					"\n   " + incrementMain() + "\t" + msg + "\n" + DashLine);
		} catch (IOException e) {
			System.err.println("MiscUtils.writeToPrimaryLogFile() "
					+ e.getMessage());
			e.printStackTrace();
			System.exit(-1);
		}
		MiscUtils.primaryLogMsgCount++;
		return MiscUtils.primaryLogMsgCount;
	}
	public static int writeToErrorLogFile(String msg) {
		try {
			MiscUtils.getErrorLogFileWriter().write(
					"\n" + incrementPubsEntryNumber() + "\t" + msg + "\n"
							+ DashLine);
		} catch (IOException e) {
			System.err.println("MiscUtils.writeToErrorLogFile() "
					+ e.getMessage());
			e.printStackTrace();
			System.exit(-1);
		}
		MiscUtils.errorLogCount++;
		return MiscUtils.errorLogCount;
	}
	public static int PubsEntryNumber = 1;

	public static String incrementPubsEntryNumber() {
		String msg = " " + PubsEntryNumber + " ";
		PubsEntryNumber++;
		return msg;
	}
	public static int RisEntryNumber = 1;

	public static String incrementRisEntryNumber() {
		String msg = " " + RisEntryNumber + " ";
		RisEntryNumber++;
		return msg;
	}

	public static int writeToWarningLogFile(String msg) {
		try {
			MiscUtils.getWarningLogFileWriter().write(
					"\n" + incrementRisEntryNumber() + "\t" + msg + "\n"
							+ DashLine);
		} catch (IOException e) {
			System.err.println("MiscUtils.writeToRisWarningLogFile() "
					+ e.getMessage());
			e.printStackTrace();
			System.exit(-1);
		}
		MiscUtils.warningLogCount++;
		return MiscUtils.warningLogCount;
	}
	public static int writeToPrimaryLogFile(Collection<String> msgs) {

		Iterator<String> it = msgs.iterator();
		boolean firstLine = true;
		try {
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
		} catch (IOException e) {
			System.err.println("MiscUtils.writeToPrimaryLogFile() "
					+ e.getMessage());
			e.printStackTrace();
			System.exit(-1);
		}
		MiscUtils.primaryLogMsgCount++;
		return MiscUtils.primaryLogMsgCount;
	}

	public static BufferedWriter openPrimaryLogFile() throws IOException {
		return MiscUtils.getPrimaryLogFileWriter();
	}

	public static BufferedWriter getPrimaryLogFileWriter() throws IOException {
		MiscUtils.primarylogFileWriter =  getLogFileWriter(MiscUtils.primarylogFileWriter,
                MiscUtils.getPrimaryLogFileName(),
                "Primary Log file");
		return MiscUtils.primarylogFileWriter;
	}

	public static void writeToDeveloperReport(String msg) {
		try {
			MiscUtils.getDeveloperReportFileWriter().write(
					"\n" + msg + "\n" + DashLine);
		} catch (IOException e) {
			e.printStackTrace();
			System.exit(-1);
		}

	}

	public static BufferedWriter openDeveloperReportFile() throws IOException {
		return getDeveloperReportFileWriter();
	}

	public static BufferedWriter getDeveloperReportFileWriter()
			throws IOException {
		
		MiscUtils.developerReportWriter =  getLogFileWriter(MiscUtils.developerReportWriter,
                MiscUtils.getDeveloperReportFileName(),
                "Developer Report Log file");
		return MiscUtils.developerReportWriter;
	}

	public static BufferedWriter openErrorLogFile() throws IOException {
		return getErrorLogFileWriter();
	}

	public static void debugOut(String s) {
		if (isDeBug()) {
			System.out.println("MiscUtils: " + s);
		}
	}

	public static BufferedWriter getLogFileWriter(BufferedWriter bw,
			String fileName, String title) throws IOException {

		if (bw == null) {
			bw = MiscUtils.openOutputFile(fileName);
			bw.write("**********************************************************\n");
			bw.write(title + " file\n");
			bw.write("** File opened: " + MiscUtils.getDateAndTime());
			bw.write("  ** \n");
			bw.write("** \n");
			bw.write("**********************************************************");
		}
		return bw;
	}

	public static BufferedWriter getErrorLogFileWriter() throws IOException {
		MiscUtils.errorLogFileWriter =  getLogFileWriter(MiscUtils.errorLogFileWriter,
				                                         MiscUtils.getErrorLogFileName(),
				                                         "** Data Error Log file");
		return MiscUtils.errorLogFileWriter;
	}

	public static BufferedWriter openWarningLogFile() throws IOException {
		return getWarningLogFileWriter();
	}

	public static BufferedWriter getWarningLogFileWriter() throws IOException {
		MiscUtils.warningLogFileWriter =  getLogFileWriter(MiscUtils.warningLogFileWriter,
                MiscUtils.getWarningLogFileName(),
                "Data Warning Log file");

		return MiscUtils.warningLogFileWriter;
	}
	
	public static String removeFileExtension(String fileName, String ext) {
		int ndx = fileName.indexOf(ext);
		return fileName.substring(0, ndx);
	}
	
	public static String removeXmlExtension(final String fileName) {
		return removeFileExtension(fileName,".xml");
	}
	
	public static String getFormattedString(String format, Object... args) {
		ByteArrayOutputStream outStream = new ByteArrayOutputStream();
		PrintStream ps = new PrintStream(outStream);
		ps.printf(format, args);
		return outStream.toString();
	}
	public static java.sql.Date getMaxDate() {
		return MaxDate;
	}

	public static java.sql.Date getMinDate() {
		return MinDate;
	}

	/**
	 * compare two strings either of which could be null or empty
	 * 
	 * @param s1
	 * @param s2
	 * @return true if they are equal
	 */
	public static boolean areStringsEqual(String s1, String s2) {
		// if both are empty they match
		if (isEmpty(s1) && isEmpty(s2))
			return true;
		// if one is empty and the other not they can't match
		if (logicalXOR(isEmpty(s1), isEmpty(s2)))
			return false;
		// at this point we know that both are non null
		if (s1.trim().length() != s2.trim().length())
			return false; // different lengths, can't be equal return false
		if (s1.trim().equals(s2.trim()))
			return true;
		return false;
	}

	// return true if and only if x or y is true
	public static boolean logicalXOR(boolean x, boolean y) {
		return ((x || y) // at least one is true
		&& !(x && y)); // at least one is false
	}

	public static boolean isEmpty(String s) {
		if (isStringEmpty(s))
			return true;
		if (s.trim().equals(RdbmsConstants.DbNull))
			return true;
		return false;
	}

	/**
	 * return true if the string is null or length zero
	 * 
	 **/
	public static boolean isStringEmpty(String s) {
		return MiscUtils.isStringEmptyOrBlank(s);
	}
	/**
	 * if a string is null, length zero or contains only white space return true
	 */
	public static boolean isStringEmptyOrBlank(String s) {
		if (s == null)
			return true;
		else if (s.trim().length() == 0)
			return true;
		return false;
	}
	

	public static void fatalError(String callingObjectClassName,
			String callingFunctionName, String message) {
		String format = "%n%20s: %-25s";
		System.err.printf("%n%20s", "Fatal Error");
		System.err.printf(format, "Class", callingObjectClassName);
		System.err.printf(format, "Function", callingFunctionName);
		System.err.printf(format, "Error", message);
		System.exit(-1);
	}

	public static String formatElapsedTime(long start, long finish) {
		long eTime = Math.abs(finish - start);
		return formatTimeMinSecMilSec(eTime);
	}
	public static String formatTimeMinSecMilSec(long t) {
		return new SimpleDateFormat("mm:ss:SSS").format(t);
	}
	public static BufferedReader openInputFile(String fName) throws IOException {
		String fileName = MiscUtils.getUserDirDataFileName(fName);
		FileReader fr = new FileReader(new File(fileName));
		BufferedReader br = new BufferedReader(fr);
		return br;
	}

	public static String getUserDirDataFileName(final String fName) {
		return GriidcConfiguration.getWorkingDirectory() + File.separator
				+ fName;
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
		boolean a1Empty = MiscUtils.isEmpty(addr1);
		boolean a2Empty = MiscUtils.isEmpty(addr2);
		String result = null;

		if(a1Empty && a2Empty) {
			result = null;
		}
		else if(!a1Empty && !a2Empty) {
			result =   addr1.trim() + ", " + addr2.trim();
		}
		else if(a2Empty) {
			result =  addr1.trim();
		}
		else {
			result =  addr2.trim();
		}
		if(result != null && result.contains(RdbmsConstants.DbNull)) {
			System.out.println("MiscUtils.makeDeliveryPoint() NULL in Delivery Point : " + result);
		}
		return result;
	}
	
	public static int getPrimaryLogMsgCount() {
		return MiscUtils.primaryLogMsgCount;
	}

	public static void resetPrimaryLogMsgCount() {
		MiscUtils.primaryLogMsgCount = 0;
	}

	public static int getErrorLogCount() {
		return errorLogCount;
	}

	public static void resetErrorLogCount() {
		MiscUtils.errorLogCount = 0;
	}

	public static int getWarningLogCount() {
		return warningLogCount;
	}

	public static void resetWarningLogCount() {
		MiscUtils.warningLogCount = 0;
	}
}
