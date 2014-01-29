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

	public InstitutionSynchronizer instSynker = new InstitutionSynchronizer();
	public DepartmentSynchronizer deptSynker = new DepartmentSynchronizer();
	public PersonSynchronizer personSynker = new PersonSynchronizer();

	
	public RisToGriidcMain() {
		// TODO Auto-generated constructor stub
	}

	public static void main(String[] args) {
		RisToGriidcMain risToGriidcMain = new RisToGriidcMain();
		try {
			RisInstDeptPeopleErrorCollection risInstitutionWithErrors = null;
			InstitutionSynchronizer.setDebug(false);
			DepartmentSynchronizer.setDebug(false);
			PersonSynchronizer.setDebug(false);
			EmailSynchronizer.setDebug(false);
			risInstitutionWithErrors = risToGriidcMain.instSynker.syncGriidcInstitutionFromRisInstitution();
			risInstitutionWithErrors = risToGriidcMain.deptSynker.syncGriidcDepartmentFromRisDepartment(risInstitutionWithErrors);
			risInstitutionWithErrors = risToGriidcMain.personSynker.syncGriidcPersonFromRisPeople(risInstitutionWithErrors);
			
			MiscUtils.closePrimaryLogFile();
			MiscUtils.closeRisErrorLogFile();
			risToGriidcMain.report();
			risToGriidcMain.emailLogs();
			MiscUtils.writeStringToFile("InstDeptPeopleDetail.txt",risInstitutionWithErrors.toString());
			System.out.println("END of risToGriidcMain");

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

	
	public void report() throws FileNotFoundException, PropertyNotFoundException {

		System.out.println("RisToGriidcMain finished");
		
		System.out.println("************************************************");
		System.out.println("Read " + instSynker.getRisInstitutionCount()
				+ " RIS Institutions records");
		System.out.println("RISS institutions            errors: "
				+ instSynker.getRisInstitutionsErrors());
		System.out.println("RISS institutions           skipped: "
				+ instSynker.getRisInstitutionsSkipped());
		System.out.println("GRIIDC Institutions           added: "
				+ instSynker.getGriidcInstitutionsAdded());
		System.out.println("GRIIDC Institutions        modified: "
				+ instSynker.getGriidcInstitutionsModified());
		System.out.println("GRIIDC Institutions      duplicates: "
				+ instSynker.getGriidcInstitutionsDuplicates());
		System.out.println("************************************************");
		System.out.println("Read " + deptSynker.getRisDepartmentCount() + 
				" RIS Department records");
		System.out.println("RISS departments            errors: "
				+ deptSynker.getRisDepartmentErrors());
		System.out.println("RISS departments           skipped: "
				+ deptSynker.getRisDepartmentRecordsSkipped());
		System.out.println("GRIIDC departments           added: "
				+ deptSynker.getGriidcDepartmentsAdded());
		System.out.println("GRIIDC departments        modified: "
				+ deptSynker.getGriidcDepartmentsModified());
		System.out.println("GRIIDC departments      duplicates: "
				+ deptSynker.getGriidcDepartmentsDuplicates());
		System.out.println("************************************************");
		System.out.println("Read " + personSynker.getRisPeopleCount() + 
				" RIS People records");
		System.out.println("RISS People            errors: "
				+ personSynker.getRisPeopleErrors());
		System.out.println("RISS People           skipped: "
				+ personSynker.getRisPeopleRecordsSkipped());
		System.out.println("GRIIDC Person           added: "
				+ personSynker.getGriidcPersonsAdded());
		System.out.println("GRIIDC Person        modified: "
				+ personSynker.getGriidcPersonsModified());
		System.out.println("GRIIDC Person      duplicates: "
				+ personSynker.getGriidcPersonsDuplicates());
		System.out.println("************************************************");
		System.out.println("Added " + personSynker.getTelephoneRecordsAdded() + 
				" GRIIDC Telephone records");
		System.out.println("RISS People telephone number errors: "
				+ personSynker.getRisTelephoneErrors());
		
		System.out.println("************************************************");
		
		System.out.println("Activity reported to log file: "
				+ MiscUtils.getPrimaryLogFileName());
		System.out.println(MiscUtils.getRisErrorLogCount()
				+ " RIS Data Errors reported to log file: "
				+ MiscUtils.getRisErrorLogFileName());
		
	}

}
