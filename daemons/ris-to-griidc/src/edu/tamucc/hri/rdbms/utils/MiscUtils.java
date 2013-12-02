package edu.tamucc.hri.rdbms.utils;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

import edu.tamucc.hri.griidc.RisPropertiesAccess;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;

public class MiscUtils {

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

	public static BufferedWriter openOutputFile(String partialName)
			throws IOException {
		return MiscUtils.openFileForWriting(partialName, "Out.txt");
	}

	public static String getLogFileName() throws FileNotFoundException,
			PropertyNotFoundException {
		return MiscUtils.getRisFileName(RisPropertiesAccess.getInstance()
				.getProperty("log.file.name"));
	}

	public static String getRisDataErrorLogName() throws FileNotFoundException,
			PropertyNotFoundException {
		return MiscUtils.getRisFileName(RisPropertiesAccess.getInstance()
				.getProperty("ris.data.error.log.name"));
	}

	public static String getDeveloperReportFileName()
			throws FileNotFoundException, PropertyNotFoundException {
		return MiscUtils.getRisFileName(RisPropertiesAccess.getInstance()
				.getProperty("dev.report.file.name"));
	}

	private static BufferedWriter logWriter = null;

	private static BufferedWriter risDataErrorWriter = null;

	private static BufferedWriter developerReportWriter = null;

	public static void closeLogFile() throws IOException,
			PropertyNotFoundException {
		MiscUtils.getLogFileWriter().close();
	}

	public static void closeRisDataErrorFile() throws IOException,
			PropertyNotFoundException {
		MiscUtils.getRisDataErrorFileWriter().close();
	}

	public static void closeDeveloperReportFile() throws IOException,
			PropertyNotFoundException {
		MiscUtils.getRisDataErrorFileWriter().close();
	}

	public static final String DashLine = "--------------------------------------------------";

	public static int mainLogEntryNumber = 1;

	public static String incrementMain() {
		String msg = " " + mainLogEntryNumber + " ";
		mainLogEntryNumber++;
		return msg;
	}

	public static void writeToLog(String msg) throws IOException,
			PropertyNotFoundException {
		MiscUtils.getLogFileWriter().write("\n   " + incrementMain());
		MiscUtils.getLogFileWriter().write("\n\t" + msg);
		MiscUtils.getLogFileWriter().write("\n" + DashLine);
	}

	public static void writeToLog(Collection<String> msgs) throws IOException,
			PropertyNotFoundException {
		MiscUtils.getLogFileWriter().write("\n   " + incrementMain());
		Iterator<String> it = msgs.iterator();
		while (it.hasNext()) {
			String msg = it.next();
			MiscUtils.getLogFileWriter().write("\n\t" + msg);
		}
		MiscUtils.getLogFileWriter().write("\n" + DashLine);
	}

	public static BufferedWriter openLogFile() throws IOException,
			PropertyNotFoundException {
		return getLogFileWriter();
	}

	public static BufferedWriter getLogFileWriter() throws IOException,
			PropertyNotFoundException {

		if (MiscUtils.logWriter == null) {
			// System.out.println("MiscUtils.getLogFileInstance() BufferedWriter is null - oprn file "
			// + MiscUtils.getLogFileName());
			MiscUtils.logWriter = MiscUtils.openFileForWriting(MiscUtils
					.getLogFileName());
			MiscUtils.logWriter
					.write("**********************************************************\n");
			MiscUtils.logWriter.write("** Log File for SyncGriidcToRis \n");
			MiscUtils.logWriter.write("** File opened: "
					+ MiscUtils.getDateAndTime());
			MiscUtils.logWriter.write("  ** \n");
			MiscUtils.logWriter.write("** \n");
			MiscUtils.logWriter
					.write("**********************************************************");
		}
		return MiscUtils.logWriter;
	}

	public static int mainRisEntryNumber = 1;

	public static String incrementRisEntryNumber() {
		String msg = " " + mainRisEntryNumber + " ";
		mainRisEntryNumber++;
		return msg;
	}

	public static void writeToRisDataErrorLog(String msg) throws IOException,
			PropertyNotFoundException {
		MiscUtils.getRisDataErrorFileWriter().write(
				"\n    " + incrementRisEntryNumber());
		MiscUtils.getRisDataErrorFileWriter().write("\n\t" + msg);
		MiscUtils.getRisDataErrorFileWriter().write("\n" + DashLine);

	}

	public static void writeToRisDataErrorLog(Collection<String> msgs)
			throws IOException, PropertyNotFoundException {
		MiscUtils.getRisDataErrorFileWriter().write(
				"\n    " + incrementRisEntryNumber());
		Iterator<String> it = msgs.iterator();
		while (it.hasNext()) {
			String msg = it.next();
			MiscUtils.getRisDataErrorFileWriter().write("\n\t" + msg);
		}
		MiscUtils.getRisDataErrorFileWriter().write("\n" + DashLine);
	}

	public static BufferedWriter openRisDataErrorFile() throws IOException,
			PropertyNotFoundException {
		return getRisDataErrorFileWriter();
	}

	public static BufferedWriter getRisDataErrorFileWriter()
			throws IOException, PropertyNotFoundException {

		if (MiscUtils.risDataErrorWriter == null) {
			MiscUtils.risDataErrorWriter = MiscUtils
					.openFileForWriting(MiscUtils.getRisDataErrorLogName());
			MiscUtils.risDataErrorWriter
					.write("**********************************************************\n");
			MiscUtils.risDataErrorWriter.write("** RIS Data Error Log file\n");
			MiscUtils.risDataErrorWriter.write("** File opened: "
					+ MiscUtils.getDateAndTime());
			MiscUtils.risDataErrorWriter.write("  ** \n");
			MiscUtils.risDataErrorWriter.write("** \n");
			MiscUtils.risDataErrorWriter
					.write("**********************************************************");
		}
		return MiscUtils.risDataErrorWriter;
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
					.openFileForWriting(MiscUtils.getDeveloperReportFileName());
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

	public static BufferedWriter openFileForWriting(String partialName,
			String extension) throws IOException {
		String fileName = MiscUtils.getRisFileName(partialName + extension);
		return openFileForWriting(fileName);
	}

	public static BufferedWriter openFileForWriting(
			String completePathAndFileName) throws IOException {
		return MiscUtils.openFileForWriting(completePathAndFileName, false);
	}

	public static BufferedWriter openFileForWriting(
			String completePathAndFileName, boolean append) throws IOException {
		File file = new File(completePathAndFileName);

		FileWriter fileWriter = new FileWriter(file.getAbsoluteFile(), append);

		file.createNewFile();

		BufferedWriter bw = new BufferedWriter(fileWriter);
		return bw;
	}

	public static void writeIt(BufferedWriter bw, String msg) throws IOException {
		if(bw == null) {
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
		String fileName = MiscUtils.getRisFileName(fName);
		FileReader fr = new FileReader(new File(fileName));
		BufferedReader br = new BufferedReader(fr);
		return br;
	}

	public static String getRisFileName(final String fName) {
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

	public static RdbmsConnection getRisDbConnection()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		RisPropertiesAccess risProperties = RisPropertiesAccess.getInstance();
		String jdbcDriverName = risProperties.getProperty("ris.db.driver.name");
		String jdbcPrefix = risProperties.getProperty("ris.db.jdbcPrefix");
		String dbType = risProperties.getProperty("ris.db.type");
		String dbHost = risProperties.getProperty("ris.db.host");
		String dbPort = risProperties.getProperty("ris.db.port");
		String dbName = risProperties.getProperty("ris.db.name");
		String dbSchema = risProperties.getProperty("ris.db.schema");
		String dbUser = risProperties.getProperty("ris.db.user");
		String dbPassword = risProperties.getProperty("ris.db.password");

		RdbmsConnection con = new RdbmsConnection();
		con.setConnection(dbType, jdbcDriverName, jdbcPrefix, dbHost, dbPort,
				dbName, dbSchema, dbUser, dbPassword);
		return con;
	}

	public static RdbmsConnection getGriidcDbConnection()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		RisPropertiesAccess risProperties = RisPropertiesAccess.getInstance();
		String jdbcDriverName = risProperties
				.getProperty("griidc.db.driver.name");
		String jdbcPrefix = risProperties.getProperty("griidc.db.jdbcPrefix");

		String dbType = risProperties.getProperty("griidc.db.type");
		String dbHost = risProperties.getProperty("griidc.db.host");
		String dbPort = risProperties.getProperty("griidc.db.port");
		String dbName = risProperties.getProperty("griidc.db.name");
		String dbSchema = risProperties.getProperty("griidc.db.schema");
		String dbUser = risProperties.getProperty("griidc.db.user");
		String dbPassword = risProperties.getProperty("griidc.db.password");

		RdbmsConnection con = new RdbmsConnection();
		con.setConnection(dbType, jdbcDriverName, jdbcPrefix, dbHost, dbPort,
				dbName, dbSchema, dbUser, dbPassword);
		return con;
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

				MiscUtils.writeToRisDataErrorLog(s);
				MiscUtils.writeToLog(s);
				System.out.println("Before squeeze: " + s);
				String sq = squeeze(s);
				System.out.println("After   squeeze: " + sq);

			}
			MiscUtils.closeLogFile();
			MiscUtils.closeRisDataErrorFile();
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

}
