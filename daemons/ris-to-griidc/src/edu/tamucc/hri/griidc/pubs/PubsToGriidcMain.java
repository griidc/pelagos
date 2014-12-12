package edu.tamucc.hri.griidc.pubs;

import java.io.IOException;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.rdbms.RisGriidcDataStoreInterface;
import edu.tamucc.hri.griidc.rdbms.RisGriidcRelationalDataStore;
import edu.tamucc.hri.griidc.rdbms.RisPubSerialNumberReader;
import edu.tamucc.hri.griidc.utils.Emailer;
import edu.tamucc.hri.griidc.utils.GriidcConfiguration;
import edu.tamucc.hri.griidc.utils.GriidcElapsedTimer;
import edu.tamucc.hri.griidc.utils.MiscUtils;

public class PubsToGriidcMain {

	// command line args switches
	public static final String EmailLogsParm = "Email";
	public static final String HelpParm = "Help";
	public static final String NotPubsParm = "NoPubs";

	public static final String[] HelpMessages = {
			"PubsToGriidcMain arguments usage: ",
			EmailLogsParm + ": send email to recipients in the "
					+ GriidcConfiguration.getRisToGriidcIniFileName(),
			NotPubsParm
					+ ": turn off the updating of publications in the DB (slow)",
			HelpParm + ": show this message", };

	private PublicationsGriidcDbInterface pubDbAgentAggregate = new PublicationsGriidcDbAgentAggregator();
	private RisGriidcDataStoreInterface risGriidcDataStore = new RisGriidcRelationalDataStore();
	private RisPubSerialNumberReader serialNumberReader = new RisPubSerialNumberReader();
	private Publication publication = null;

	private static boolean UpdatePublications = true; // this is the slowest
														// part of
	// the process
	private static boolean EmailLogsOn = false;

	public PubsToGriidcMain() {
	}

	public static boolean detail = false;
	public static boolean DeBug = false;

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	public void debugMessage(String msg) {
		if (isDeBug())
			System.out.println(msg);
	}

	public void run() {
		GriidcElapsedTimer timer = new GriidcElapsedTimer();
		System.out.println("-- Start PubsToGriidcMain --");
		int[] pubNumbers = null;

		try {
			pubNumbers = serialNumberReader.getAllRisPubSerialNumbers();
			if (PubsToGriidcMain.isUpdatePublications()) {
				debugMessage("Update Publication");
				pubDbAgentAggregate.updateAllPublications(pubNumbers);
				System.out.println("Elapsed time for Publication update:"
						+ timer.getFormatedElapsedTime());
				// this.reportPublications();
				timer = new GriidcElapsedTimer();
			}
			debugMessage("Update Person Publication");
			pubDbAgentAggregate.updatePersonPublication();
			System.out.println("Elapsed time for Publication-Person update:"
					+ timer.getFormatedElapsedTime());
			// this.reportPersonPublications();
			timer = new GriidcElapsedTimer();
			debugMessage("Update Project Publication");
			pubDbAgentAggregate.updateProjectPublication();
			System.out.println("Elapsed time for Project-Person update:"
					+ timer.getFormatedElapsedTime());
			// this.reportProjectPublications();
			this.reportAll();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		if (detail && PubsToGriidcMain.isDeBug())
			System.out.println(publication.toFormatedString());

		System.out.println("\nEND of PubsToGriidcMain");

	}

	/**
	 * read the command line args to control some behaviours
	 * 
	 * @param args
	 */
	public void processCommandLineArgs(String[] args) {
		PubsToGriidcMain.setUpdatePublications(true);
		PubsToGriidcMain.setEmailLogsOn(false);
		for (String arg : args) {
			String argTemp = arg.toUpperCase();
			if (argTemp.startsWith(HelpParm.toUpperCase())) {
				for (String s : HelpMessages) {
					System.out.println(s);
				}
				System.exit(1);
			} else if (argTemp.startsWith(EmailLogsParm.toUpperCase())) {
				System.out.println("Email notification is ON");
				PubsToGriidcMain.setEmailLogsOn(true);
			} else if (argTemp.startsWith(NotPubsParm.toUpperCase())) {
				PubsToGriidcMain.setUpdatePublications(false);
				System.out.println("Update Publications is OFF");
			}
		}
	}

	public static void main(String[] args) {
		GriidcElapsedTimer timer = new GriidcElapsedTimer();
		PubsToGriidcMain pubsToGriidcMain = new PubsToGriidcMain();
		pubsToGriidcMain.processCommandLineArgs(args);
		// PublicationsGriidcDbAgentAggregator.setDeBug(true);
		PubsToGriidcMain.setDeBug(true);
		// PubsJaxbHandler.setDeBug(true);
		// RefBaseWebService.setDeBug(true);
		// PublicationDbAgent.setDeBug(true);
		// PersonPublicationDbAgent.setDeBug(true);
		// ProjectPublicationDbAgent.setDeBug(true);
		// RisPeopleGriidcPersonMap.setDeBug(true);
		// XmlPreprocessor.setDeBug(true);
		pubsToGriidcMain.run();
		// System.out.println(MiscUtils.getProjectNumberFundingCycleCache().toString());
		try {
			MiscUtils.closePrimaryLogFile();
			MiscUtils.closePubsErrorLogFile();

			if (EmailLogsOn)
				pubsToGriidcMain.emailLogs();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IniSectionNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		System.out.println("END of pubsToGriidcMain");
		System.out.println("Overall Elapsed time:"
				+ timer.getFormatedElapsedTime());
	}

	private static final String pFormat = "%-34s %10d%n";

	public void reportAll() {
		this.reportPubNumbers();
		this.reportPublications();
		this.reportPersonPublications();
		this.reportProjectPublications();
	}

	public void reportPubNumbers() {
		String[] srReports = this.serialNumberReader.getReportStrings();
		for (String s : srReports) {
			System.out.printf(s);
		}
	}

	public void reportPublications() {
		reportHeader("RIS Publications to GRIIDC ");

		System.out.printf(pFormat, "Serial IDs processed",
				this.pubDbAgentAggregate.getPubSerialIdsProccessed());
		System.out.printf(pFormat, "Serial IDs not found in REF-BASE",
				this.pubDbAgentAggregate.getPubNumbersNotFoundInRefBase());
		System.out.printf(pFormat, "Publications with Errors",
				this.pubDbAgentAggregate.getPubsErrors());
		System.out.printf(pFormat, "Publications added",
				pubDbAgentAggregate.getPubsAdded());
		System.out.printf(pFormat, "Publications modified",
				pubDbAgentAggregate.getPubsModified());
		System.out.printf(pFormat, "Duplicate Publications",
				pubDbAgentAggregate.getDuplicatePubs());
	}

	public void reportPersonPublications() {
		reportHeader("RIS  Person / Publications to GRIIDC ");
		System.out.printf(pFormat, "Person-Publications read",
				pubDbAgentAggregate.getPersonPubsRead());
		System.out.printf(pFormat, "Person-Publications added",
				pubDbAgentAggregate.getPersonPubsAdded());
		System.out.printf(pFormat, "Duplicate Person-Publications",
				pubDbAgentAggregate.getDuplicatePersonPubs());
		System.out.printf(pFormat, "Person-Publications with Errors",
				this.pubDbAgentAggregate.getPersonPubsErrors());
	}

	public void reportProjectPublications() {
		reportHeader("RIS  Project / Publications to GRIIDC ");
		System.out.printf(pFormat, "Project-Publications read",
				pubDbAgentAggregate.getProjectPubsRead());
		System.out.printf(pFormat, "Project-Publications added",
				pubDbAgentAggregate.getProjectPubsAdded());
		System.out.printf(pFormat, "Duplicate Project-Publications",
				pubDbAgentAggregate.getDuplicateProjectPubs());
		System.out.printf(pFormat, "Project-Publications with Errors",
				this.pubDbAgentAggregate.getProjectPubsErrors());
	}

	public void emailLogs() throws PropertyNotFoundException, IOException,
			IniSectionNotFoundException {
		Emailer emailer = new Emailer();
		String rFormat = "\t%-40s%n";
		String from = GriidcConfiguration.getGriidcMailSender();
		String[] tos = GriidcConfiguration.getPrimaryMsgLogRecipients();
		String subject = "Primary Log File for PUBS to GRIIDC - "
				+ MiscUtils.getDateAndTime();
		String absoluteFileName = GriidcConfiguration.getPrimaryLogFileName();
		String msg = MiscUtils.readFileToBuffer(absoluteFileName);
		emailer.sendEmail(from, tos, subject, msg);
		System.out.println("\n" + subject + " : " + absoluteFileName
				+ " emailed to the following addresses:");
		for (String t : tos) {
			System.out.printf(rFormat, t);
		}

		tos = GriidcConfiguration.getPubsErrorMsgLogRecipients();
		subject = "PUBS Error Log File for PUBS to GRIIDC - "
				+ MiscUtils.getDateAndTime();
		absoluteFileName = GriidcConfiguration.getPubsErrorLogFileName();
		msg = MiscUtils.readFileToBuffer(absoluteFileName);
		emailer.sendEmail(from, tos, subject, msg);
		System.out.println("\n" + subject + " : " + absoluteFileName
				+ " emailed to the following addresses:");
		for (String t : tos) {
			System.out.printf(rFormat, t);
		}
	}

	public void reportHeader(String title) {
		String titleFormat = "%n*****************************  %-40s  ********************************%n";
		System.out.printf(titleFormat, title);
	}

	public static boolean isUpdatePublications() {
		return UpdatePublications;
	}

	public static void setUpdatePublications(boolean up) {
		UpdatePublications = up;
	}

	public static boolean isEmailLogsOn() {
		return EmailLogsOn;
	}

	public static void setEmailLogsOn(boolean emailLogsOn) {
		EmailLogsOn = emailLogsOn;
	}

}
