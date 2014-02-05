package edu.tamucc.hri.rdbms.utils;

import java.io.IOException;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.MiscUtils;

public class RdbmsUtilsTest {

	public RdbmsUtilsTest() {
		// TODO Auto-generated constructor stub
	}
	public static void main(String[] args) {
		String[] risTableNames = { "FundingSource", "Programs" ,"Projects", "Roles"};
		String[] griidcTableNames = { "FundingOrganization", "FundingEnvelope", "Project",
				"ProjRole", "Task","TaskRole" };
		// or
		//griidcTableNames = RdbmsUtils.GriidcShortListTables;
		//risTableNames = RdbmsUtils.RisShortListTables;
		System.out.println("Rdbmsutils.main() - Start -");
		GriidcPgsqlEnumType gpet = new GriidcPgsqlEnumType();
		try {
			String s = RdbmsUtils.getColumnNamesAndDataTypesFromTables(
					RdbmsUtils.getRisDbConnectionInstance(),risTableNames);
			String fileName = RdbmsUtils.getRisDbConnectionInstance()
					.getDbName() + "RisTableColTypeReport.txt";
			MiscUtils.writeStringToFile(fileName, s);
			System.out
			.println("Report written to: " + MiscUtils.getAbsoluteFileName(fileName));
			
			s = RdbmsUtils.getColumnNamesAndDataTypesFromTables(
					RdbmsUtils.getGriidcDbConnectionInstance(),griidcTableNames);
			fileName = RdbmsUtils.getGriidcDbConnectionInstance()
					.getDbName() + "GriidcTableColTypeReport.txt";
			MiscUtils.writeStringToFile(fileName, s);
			System.out
			.println("Report written to: " + MiscUtils.getAbsoluteFileName(fileName));
			
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (TableNotInDatabaseException e) {
			System.err.println(e.getMessage());
		}

		System.out.println("Rdbmsutils.main() - END -");
	}
}
