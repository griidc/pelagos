package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.Emailer;
import edu.tamucc.hri.griidc.support.MiscUtils;
import edu.tamucc.hri.griidc.support.RisInstDeptPeopleErrorCollection;

public class RisToGriidcMain {

	private InstitutionSynchronizer instSynker = new InstitutionSynchronizer();
	private DepartmentSynchronizer deptSynker = new DepartmentSynchronizer();
	private PersonSynchronizer personSynker = new PersonSynchronizer();
	private FundingEnvelopeSynchronizer fundingEnvelopeSynker = new FundingEnvelopeSynchronizer();
	private ProjectSynchronizer projectSynker = new ProjectSynchronizer();

	private static boolean RunEverything = true;

	public RisToGriidcMain() {
		// TODO Auto-generated constructor stub
	}

	public static void main(String[] args) {
		RisToGriidcMain risToGriidcMain = new RisToGriidcMain();
		System.out.println("-- Start risToGriidcMain --");
		InstitutionSynchronizer.setDebug(false);
		DepartmentSynchronizer.setDebug(false);
		PersonSynchronizer.setDebug(false);
		EmailSynchronizer.setDebug(false);
		FundingEnvelopeSynchronizer.setDebug(false);
		ProjectSynchronizer.setDebug(false);

		RisInstDeptPeopleErrorCollection risInstitutionWithErrors = null;
		try {
			risInstitutionWithErrors = risToGriidcMain.instSynker
					.syncGriidcInstitutionFromRisInstitution();
			risInstitutionWithErrors = risToGriidcMain.deptSynker
					.syncGriidcDepartmentFromRisDepartment(risInstitutionWithErrors);
			risInstitutionWithErrors = risToGriidcMain.personSynker
					.syncGriidcPersonFromRisPeople(risInstitutionWithErrors);
			risToGriidcMain.fundingEnvelopeSynker
					.syncGriidcFundingEnvelopeFromRisFundingSource();
			risToGriidcMain.projectSynker.syncGriidcProjectFromRisPrograms();

			MiscUtils.closePrimaryLogFile();
			MiscUtils.closeRisErrorLogFile();

			risToGriidcMain.report();
			risToGriidcMain.emailLogs();
			MiscUtils.writeStringToFile("InstDeptPeopleDetail.txt",
					risInstitutionWithErrors.toString());

			System.out.println("END of risToGriidcMain");
			System.out.println(MiscUtils.getProjectNumberFundingCycleCache()
					.toString());

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
		}
	}

	public void emailLogs() throws PropertyNotFoundException, IOException {
		Emailer emailer = new Emailer();
		String from = MiscUtils.getGriidcMailSender();
		String[] tos = MiscUtils.getPrimaryMsgLogRecipients();
		String subject = "Primary Log File for RIS to GRIIDC - "
				+ MiscUtils.getDateAndTime();
		String absoluteFileName = MiscUtils.getPrimaryLogFileName();
		String msg = MiscUtils.readFileToBuffer(absoluteFileName);
		emailer.sendEmail(from, tos, subject, msg);

		tos = MiscUtils.getRisErrorMsgLogRecipients();
		subject = "RIS Error Log File for RIS to GRIIDC - "
				+ MiscUtils.getDateAndTime();
		msg = MiscUtils.readFileToBuffer(MiscUtils.getRisErrorLogFileName());
		emailer.sendEmail(from, tos, subject, msg);
	}

	public void report() throws FileNotFoundException,
			PropertyNotFoundException {

		String pFormat = "%-50s %10d%n";

		System.out.println("RisToGriidcMain finished");

		System.out
				.println("*************************************************************");
		System.out.printf(pFormat, "RIS Institutions records read:",
				instSynker.getRisRecordCount());
		System.out.printf(pFormat, "RISS Institutions errors:",
				instSynker.getRisRecordErrors());
		System.out.printf(pFormat, "RISS Institutions skipped:",
				instSynker.getRisRecordsSkipped());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Institutions added:",
				instSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Institutions modified:",
				instSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Institutions duplicates:",
				instSynker.getGriidcRecordDuplicates());
		System.out
				.println("*************************************************************");
		System.out.printf(pFormat, "RIS Departments records read:",
				deptSynker.getRisRecordCount());
		System.out.printf(pFormat, "RISS Departments errors:",
				deptSynker.getRisRecordErrors());
		System.out.printf(pFormat, "RISS Departments skipped:",
				deptSynker.getRisRecordsSkipped());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Department added:",
				deptSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Department modified:",
				deptSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Department duplicates:",
				deptSynker.getGriidcRecordDuplicates());
		System.out
				.println("*************************************************************");
		System.out.printf(pFormat, "RIS People records read:",
				personSynker.getRisRecordCount());
		System.out.printf(pFormat, "RISS People errors:",
				personSynker.getRisRecordErrors());
		System.out.printf(pFormat, "RISS People skipped:",
				personSynker.getRisRecordsSkipped());
		System.out.printf(pFormat, "RISS People telephone number errors:",
				personSynker.getRisTelephoneErrors());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Telephone records added ",
				personSynker.getTelephoneRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Person added:",
				personSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Person modified:",
				personSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Person duplicates:",
				personSynker.getGriidcRecordDuplicates());
		System.out
				.println("*************************************************************");
		System.out.printf(pFormat, "RIS Funding Source records read:",
				fundingEnvelopeSynker.getRisRecordCount());
		System.out.printf(pFormat, "RISS Funding Source errors:",
				fundingEnvelopeSynker.getRisRecordErrors());
		System.out.printf(pFormat, "RISS Funding Source skipped:",
				fundingEnvelopeSynker.getRisRecordsSkipped());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Funding Envelope records added:",
				fundingEnvelopeSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Funding Envelope records modified:",
				fundingEnvelopeSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Funding Envelope duplicates:",
				fundingEnvelopeSynker.getGriidcRecordDuplicates());
		System.out
				.println("*************************************************************");
		System.out.printf(pFormat, "RIS Programs records read:",
				projectSynker.getRisRecordCount());
		System.out.printf(pFormat, "RISS Programs errors:",
				projectSynker.getRisRecordErrors());
		System.out.printf(pFormat, "RISS Programs skipped:",
				projectSynker.getRisRecordsSkipped());
		System.out
				.println("- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -");
		System.out.printf(pFormat, "GRIIDC Project records added:",
				projectSynker.getGriidcRecordsAdded());
		System.out.printf(pFormat, "GRIIDC Project records modified:",
				projectSynker.getGriidcRecordsModified());
		System.out.printf(pFormat, "GRIIDC Project records duplicates:",
				projectSynker.getGriidcRecordDuplicates());
		System.out
				.println("*************************************************************");

		System.out.println("Activity reported to log file: "
				+ MiscUtils.getPrimaryLogFileName());
		System.out.println(MiscUtils.getRisErrorLogCount()
				+ " RIS Data Errors reported to log file: "
				+ MiscUtils.getRisErrorLogFileName());

	}

}
