package edu.tamucc.hri.rdbms.utils;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;
import edu.tamucc.hri.griidc.support.MiscUtils;

public class RdbmsUtilsTest {

	public RdbmsUtilsTest() {
		// TODO Auto-generated constructor stub
	}
	
	public static final String GriidcTaskRolesSelect =  
			      "SELECT DISTINCT R.Role_ID,Role_Name FROM Roles R JOIN ProjPeople PP ON R.Role_ID = PP.Role_ID WHERE PP.Project_ID != 0";
	
	public static final String GriidcProjectRolesSelect = 
			     "SELECT DISTINCT R.Role_ID,Role_Name FROM Roles R JOIN ProjPeople PP ON R.Role_ID = PP.Role_ID WHERE PP.Project_ID = 0";
	
	public static void readGriidcRoles(String query) throws FileNotFoundException, SQLException, ClassNotFoundException, PropertyNotFoundException {
		
		int role_ID = -1;                                     
		String role_Name = null;
		  
		
		String format = "%2d  %-40s%n";
		ResultSet rs = RdbmsUtils.getRisDbConnectionInstance().executeQueryResultSet(query);
		while (rs.next()) {
			role_ID = rs.getInt("Role_ID");
			role_Name = rs.getString("Role_Name");
			
			System.out.printf(format,role_ID, role_Name);	
		}
	}
	public static void main(String[] args) {
		String[] risTableNames = {  
				// "FundingSource", "Programs" ,"Projects", 
				"Roles"};
		String[] griidcTableNames = { 
				//"FundingOrganization", "FundingEnvelope", "Project",'Task",
				"ProjRole", "TaskRole" };
		// or
		//griidcTableNames = RdbmsUtils.GriidcShortListTables;
		//risTableNames = RdbmsUtils.RisShortListTables;
		
		System.out.println("Rdbmsutils.main() - Start -");
		GriidcPgsqlEnumType gpet = new GriidcPgsqlEnumType();
		try {
			
			TableColInfo tci = RdbmsUtils.getMetaDataForTable(RdbmsUtils.getRisDbConnectionInstance(), "Roles");
			System.out.println("RIS Roles\n" + tci.toString());
			
			
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
			
			System.out.println("\nGriidc Task Roles\n");
			readGriidcRoles(GriidcTaskRolesSelect);
			
			System.out.println("\nGriidc Project Roles\n");
			readGriidcRoles(GriidcProjectRolesSelect);
			
			tci = RdbmsUtils.getMetaDataForTable(RdbmsUtils.getRisDbConnectionInstance(), "ProjPeople");
			System.out.println("RIS ProjPeople\n" + tci.toString());
			System.out.println("\nRIS ProjPeople\n");
			
			
			
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
