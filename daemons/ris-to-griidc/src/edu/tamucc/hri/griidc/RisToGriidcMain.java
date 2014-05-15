package edu.tamucc.hri.griidc;

import java.io.IOException;
import java.sql.SQLException;

import org.ini4j.InvalidFileFormatException;

import edu.tamucc.hri.griidc.exception.IniSectionNotFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.Emailer;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.griidc.support.RisInstDeptPeopleErrorCollection;
import edu.tamucc.hri.griidc.support.RisToGriidcConfiguration;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;
import edu.tamucc.hri.rdbms.utils.RdbmsConnectionFactory;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;

public class RisToGriidcMain {

	private InstitutionSynchronizer instSynker = new InstitutionSynchronizer();
	private DepartmentSynchronizer deptSynker = new DepartmentSynchronizer();
	private PersonSynchronizer personSynker = new PersonSynchronizer();
	private FundingEnvelopeSynchronizer fundingEnvelopeSynker = new FundingEnvelopeSynchronizer();
	private ProjectSynchronizer projectSynker = new ProjectSynchronizer();
	private TaskSynchronizer taskSynker = new TaskSynchronizer();
	private RolesSynchronizer roleSynker = new RolesSynchronizer();

	private static boolean Debug = false;

	private static String InstDeptPeopleDetailFileName = "InstDeptPeopleDetail.txt";
	// command line args switches
	public static final String EmailLogsParm = "Email".toUpperCase();
	public static final String HelpParm = "Help".toUpperCase();

	private static boolean MainDebugOn = true;
	private static boolean EmailLogsOn = false;
	private static boolean RunThis = true;

	public RisToGriidcMain() {

	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
	}

	/**
	 * read the command line args to control some behaviours
	 * 
	 * @param args
	 */
	public static void processCommandLineArgs(String[] args) {

		for (String arg : args) {
			String argTemp = arg.toUpperCase();
			if (argTemp.startsWith(EmailLogsParm)) {
				EmailLogsOn = true;
			}
			if (argTemp.startsWith(HelpParm)) {
				System.out
						.println("Supply paramater \"Email\" to turn on email of logs to recepiants found in ini file "
								+ RisToGriidcConfiguration
										.getNotificationsFileName());
				System.exit(1);
			}
		}
	}

	public static void main(String[] args) {
		RisToGriidcMain risToGriidcMain = new RisToGriidcMain();
		RisToGriidcMain.processCommandLineArgs(args);

		System.out.println("-- Start risToGriidcMain --");
		RdbmsUtils.setDebug(false);
		RdbmsConnectionFactory.setDeBug(false);
		RdbmsConnection.setDebug(false);
		InstitutionSynchronizer.setDebug(false);
		DepartmentSynchronizer.setDebug(false);
		PersonSynchronizer.setDebug(false);
		TaskSynchronizer.setDebug(false);
		TelephoneSynchronizer.setDebug(false);
		PersonTelephoneSynchronizer.setDebug(false);
		EmailSynchronizer.setDebug(false);
		FundingEnvelopeSynchronizer.setDebug(false);
		ProjectSynchronizer.setDebug(false);
		RolesSynchronizer.setDebug(false);

		// InstitutionSynchronizer.setDebug(true);
		// DepartmentSynchronizer.setDebug(true);
		// PersonSynchronizer.setDebug(true);
		// TelephoneSynchronizer.setDebug(true);
		// FundingEnvelopeSynchronizer.setDebug(true);
		// RolesSynchronizer.setDebug(true);

        risToGriidcMain.instSynker.setWarningsOn(false);
		RisInstDeptPeopleErrorCollection risInstitutionWithErrors = null;

		try {
			String emailMessage = "Email of logs not turned on. To do so supply \"Email\" on command line";
			if (EmailLogsOn)
				emailMessage = "Email Logs to recepients specified in "
						+ RisToGriidcConfiguration.getNotificationsFileName();
			System.out.println(emailMessage);
			if (RunThis) {
				if (RisToGriidcMain.MainDebugOn)
					System.out.println("Institutuion");
				risInstitutionWithErrors = risToGriidcMain.instSynker
						.syncGriidcInstitutionFromRisInstitution();

				if (RisToGriidcMain.MainDebugOn)
					System.out.println("Department");
				risInstitutionWithErrors = risToGriidcMain.deptSynker
						.syncGriidcDepartmentFromRisDepartment(risInstitutionWithErrors);
			
				if (RisToGriidcMain.MainDebugOn)
					System.out.println("Person");
				risInstitutionWithErrors = risToGriidcMain.personSynker
						.syncGriidcPersonFromRisPeople(risInstitutionWithErrors);
				
				if (RisToGriidcMain.MainDebugOn)
					System.out.println("Funding Envelope");
				risToGriidcMain.fundingEnvelopeSynker
						.syncGriidcFundingEnvelopeFromRisFundingSource();
				if (RisToGriidcMain.MainDebugOn)
					System.out.println("Project");
				risToGriidcMain.projectSynker
						.syncGriidcProjectFromRisPrograms();
			
				if (RisToGriidcMain.MainDebugOn)
					System.out.println("Task");
				risToGriidcMain.taskSynker.syncGriidcTaskFromProjects();

				if (RisToGriidcMain.MainDebugOn)
					System.out.println("TaskRole and ProjRole");
				risToGriidcMain.roleSynker.syncGriidcRolesFromRisRoles();
			}
			MiscUtils.closePrimaryLogFile();
			MiscUtils.closeRisErrorLogFile();

			risToGriidcMain.report();

			if (EmailLogsOn)
				risToGriidcMain.emailLogs();

			MiscUtils.writeStringToFile(InstDeptPeopleDetailFileName,
					risInstitutionWithErrors.toString());

			System.out.println("END of risToGriidcMain");
			// System.out.println(MiscUtils.getProjectNumberFundingCycleCache().toString());

		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (TableNotInDatabaseException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IniSectionNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	public void emailLogs() throws PropertyNotFoundException, IOException,
			IniSectionNotFoundException {
		Emailer emailer = new Emailer();
		String rFormat = "\t%-40s%n";
		String from = RisToGriidcConfiguration.getGriidcMailSender();
		String[] tos = RisToGriidcConfiguration.getPrimaryMsgLogRecipients();
		String subject = "Primary Log File for RIS to GRIIDC - "
				+ MiscUtils.getDateAndTime();
		String absoluteFileName = RisToGriidcConfiguration
				.getPrimaryLogFileName();
		String msg = MiscUtils.readFileToBuffer(absoluteFileName);
		emailer.sendEmail(from, tos, subject, msg);
		System.out.println("\n" + subject + " : " + absoluteFileName
				+ " emailed to the following addresses:");
		for (String t : tos) {
			System.out.printf(rFormat, t);
		}

		tos = RisToGriidcConfiguration.getRisErrorMsgLogRecipients();
		subject = "RIS Error Log File for RIS to GRIIDC - "
				+ MiscUtils.getDateAndTime();
		absoluteFileName = RisToGriidcConfiguration.getRisErrorLogFileName();
		msg = MiscUtils.readFileToBuffer(absoluteFileName);
		emailer.sendEmail(from, tos, subject, msg);
		System.out.println("\n" + subject + " : " + absoluteFileName
				+ " emailed to the following addresses:");
		for (String t : tos) {
			System.out.printf(rFormat, t);
		}
	}

	public void report() throws PropertyNotFoundException,
			InvalidFileFormatException, IOException {

		String pFormat = "%-44s %10d%n";
		String titleFormat = "%n*****************************  %-40s  ********************************%n";
		String title = "RIS Institutions to GRIIDC Institution";

		System.out.println("RisToGriidcMain finished");

		System.out.printf(titleFormat, title);

		System.out.printf(pFormat, "RIS Institutions records read:",
				instSynker.getRisRecordCount());
		System.out.printf(pFormat, "RIS Institutions errors:",
				instSynker.getRisRecordErrors());
		System.out.printf(pFormat, "RIS Institutions warnings:",
				instSynker.getRisRecordWarnings());

		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Institution added:",
				instSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Institution modified:",
				instSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Institution duplicates:",
				instSynker.getGriidcRecordDuplicates());

		title = "RIS Departments to GRIIDC Department";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "RIS Departments records read:",
				deptSynker.getRisRecordCount());
		System.out.printf(pFormat, "RIS Departments errors:",
				deptSynker.getRisRecordErrors());

		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Department added:",
				deptSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Department modified:",
				deptSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Department duplicates:",
				deptSynker.getGriidcRecordDuplicates());

		title = "RIS People to GRIIDC Person";

		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "RIS People records read:",
				personSynker.getRisRecordCount());
		System.out.printf(pFormat, "RIS People errors:",
				personSynker.getRisRecordErrors());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Person added:",
				personSynker.getGriidcPersonRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Person modified:",
				personSynker.getGriidcPersonRecordsModified());
		System.out.printf(pFormat, "GRIIDC Person duplicates:",
				personSynker.getGriidcPersonRecordDuplicates());
		
		title = "GoMRIPerson-Department-RIS_ID relationship ";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "GoMRIPerson-Department-RIS_ID added:",
				personSynker.getGriidcPersonDepartmentPeopleRecordsAdded());
		System.out.printf(pFormat, "GoMRIPerson-Department-RIS_ID modified:",
		        personSynker.getGriidcPersonDepartmentPeopleRecordsModified());

		title = "GoMRIPerson relationship ";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "GoMRIPerson added:",
				personSynker.getGomriPersonRecordsAdded());
		
		
		title = "RIS People to GRIIDC Telephone";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "RIS Telephone items read:",
				TelephoneSynchronizer.getInstance().getRisTelephoneRecords());
		System.out.printf(pFormat, "RIS Telephone information errors:",
				TelephoneSynchronizer.getInstance().getRisTelephoneErrors());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Telephone records added ",
				TelephoneSynchronizer.getInstance().getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC duplicate Telephone records",
				TelephoneSynchronizer.getInstance().getGriidcDuplicates());

		title = "RIS People to GRIIDC Person-Telephone";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "RIS Person-Telephone items read: ",
				PersonTelephoneSynchronizer.getInstance()
						.getRisPersonTelephoneRecords());
		System.out.printf(pFormat, "RIS Person-Telephone information errors:",
				PersonTelephoneSynchronizer.getInstance()
						.getRisPersonTelephoneErrors());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Person-Telephone records added ",
				PersonTelephoneSynchronizer.getInstance()
						.getGriidcPersonTelephoneRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Person-Telephone records modified ",
				PersonTelephoneSynchronizer.getInstance()
						.getGriidcPersonTelephoneRecordsModified());
		System.out.printf(pFormat, "GRIIDC Person-Telephone duplicates ",
				PersonTelephoneSynchronizer.getInstance()
						.getGriidcPersonTelephoneRecordDuplicates());

		title = "RIS People to GRIIDC Email";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "RIS Email items read:", EmailSynchronizer
				.getInstance().getEmailRecordsRead());
		System.out.printf(pFormat, "RIS Email information errors:",
				EmailSynchronizer.getInstance().getEmailRecordsErrors());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Email records added ",
				EmailSynchronizer.getInstance().getEmailRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Email records modified ",
				EmailSynchronizer.getInstance().getEmailRecordsModified());
		System.out.printf(pFormat, "GRIIDC Email record duplicates ",
				EmailSynchronizer.getInstance().getEmailRecordsDuplicates());

		
		title = "RIS Funding Source to GRIIDC Funding Envelope";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "RIS Funding Source records read:",
				fundingEnvelopeSynker.getRisRecordCount());
		System.out.printf(pFormat, "RIS Funding Source errors:",
				fundingEnvelopeSynker.getRisRecordErrors());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Funding Envelope records added:",
				fundingEnvelopeSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Funding Envelope records modified:",
				fundingEnvelopeSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Funding Envelope duplicates:",
				fundingEnvelopeSynker.getGriidcRecordDuplicates());

		title = "RIS Programs to GRIIDC Project";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "RIS Programs records read:",
				projectSynker.getRisRecordCount());
		System.out.printf(pFormat, "RIS Programs errors:",
				projectSynker.getRisRecordErrors());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Project records added:",
				projectSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Project records modified:",
				projectSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Project records duplicates:",
				projectSynker.getGriidcRecordDuplicates());

		title = "RIS Projects to GRIIDC Task";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "RIS Projects records read:",
				taskSynker.getRisRecordCount());
		System.out.printf(pFormat, "RIS Projects errors:",
				taskSynker.getRisRecordErrors());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Task records added:",
				taskSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Task records modified:",
				taskSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Task records duplicates:",
				taskSynker.getGriidcRecordDuplicates());

		title = "RIS Roles to GRIIDC TaskRole and ProjRole";
		System.out.printf(titleFormat, title);
		System.out.printf(pFormat, "RIS Roles records read:",
				roleSynker.getRisRecordCount());
		System.out.printf(pFormat, "RIS Role errors:",
				roleSynker.getRisRecordErrors());

		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");

		System.out.printf(pFormat, "GRIIDC TaskRole added:",
				roleSynker.getGriidcTaskRoleAdded());
		System.out.printf(pFormat, "GRIIDC TaskRole modified:",
				roleSynker.getGriidcTaskRoleModified());
		System.out.printf(pFormat, "GRIIDC TaskRole duplicates:",
				roleSynker.getGriidcTaskRoleDuplicates());
		System.out.printf(pFormat, "GRIIDC ProjRole added:",
				roleSynker.getGriidcProjRoleAdded());
		System.out.printf(pFormat, "GRIIDC ProjRole modified:",
				roleSynker.getGriidcProjRoleModified());
		System.out.printf(pFormat, "GRIIDC ProjRole duplicates:",
				roleSynker.getGriidcProjRoleDuplicates());

		title = "**************************************************";
		System.out.printf(titleFormat, title);

		System.out.println("All Activity reported to log file: "
				+ RisToGriidcConfiguration.getPrimaryLogFileName());
		System.out.println(MiscUtils.getRisErrorLogCount()
				+ " RIS Data Errors reported to log file: "
				+ RisToGriidcConfiguration.getRisErrorLogFileName());
		System.out
				.println("Institution/Deptartment/People error Tree reported in file: "
						+ MiscUtils
								.getUserDirDataFileName(InstDeptPeopleDetailFileName));
	}

}
