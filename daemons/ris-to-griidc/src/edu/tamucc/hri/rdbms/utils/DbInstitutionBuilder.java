package edu.tamucc.hri.rdbms.utils;

import java.io.FileNotFoundException;
import java.sql.ResultSet;
import java.sql.SQLException;

import edu.tamucc.hri.griidc.altrep.Department;
import edu.tamucc.hri.griidc.altrep.Institution;
import edu.tamucc.hri.griidc.altrep.InstitutionCollection;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.exception.TableNotInDatabaseException;

public class DbInstitutionBuilder {

	public DbInstitutionBuilder() {
		// TODO Auto-generated constructor stub
	}

	private RdbmsConnection dbCon = null;

	private String institutionTableName = null;
	private String institutionIdColumn = null;
	private String institutionNameColumn = null;

	private String departmentTableName = null;
	private String departmentIdColumn = null;
	private String departmentNameColumn = null;
	private String departmentInstitutionIdColumn = null;

	private InstitutionCollection instCollection = null;

	public static boolean Debug = false;

	/**
	 * @param dbCon
	 * @param institutionTableName
	 * @param institutionIdColumn
	 * @param institutionNameColumn
	 */
	public DbInstitutionBuilder(InstitutionCollection instCollection,
			RdbmsConnection dbCon, String institutionTableName,
			String institutionIdColumn, String institutionNameColumn,
			String deptTableName, String deptIdColumn, String deptNameColumn,
			String deptInstitutionIdColumn) {
		super();
		this.instCollection = instCollection;
		this.dbCon = dbCon;
		this.institutionTableName = institutionTableName;
		this.institutionIdColumn = institutionIdColumn;
		this.institutionNameColumn = institutionNameColumn;
		this.departmentTableName = deptTableName;
		this.departmentIdColumn = deptIdColumn;
		this.departmentNameColumn = deptNameColumn;
		this.departmentInstitutionIdColumn = deptInstitutionIdColumn;
	}

	public void buildInstitutionCollectionFromDb()
			throws FileNotFoundException, SQLException, ClassNotFoundException, TableNotInDatabaseException {

		if (isDebug()) 
			System.out.println("DbInstitutionBuilder.buildInstitutionCollectionFromDb() - " +this.toString()); 


		// make all the institutions
		ResultSet results = this.dbCon
				.selectAllValuesFromTable(this.institutionTableName);
		while (results.next()) {
			int instId = results.getInt(this.institutionIdColumn);
			String name = results.getString(this.institutionNameColumn);
			Institution inst = new Institution(name, instId);
			this.instCollection.addInstitution(inst);
			if (isDebug()) {
				System.out.println("\tAdd institution: " + inst.toString());
			}
		}

		// make the Departments and connect to the institutions
		results = this.dbCon.selectAllValuesFromTable(this.departmentTableName);
		while (results.next()) {
			int deptId = results.getInt(this.departmentIdColumn);
			String name = results.getString(this.departmentNameColumn);
			int deptInstId = results.getInt(this.departmentInstitutionIdColumn);
			Department dept = new Department(name, deptId);
			try {
				Institution inst = this.instCollection.findInstitution(deptInstId);
				inst.addDepartment(dept);
				if (isDebug()) {
					System.out.println("\tAdd Department: " + dept.toString() + " to institution: " + inst.toString());
				}
			} catch (NoRecordFoundException e) {
				String msg = "DbInstitutionBuilder.buildInstitutionCollectionFromDb() could not find Institution: "
						+ deptInstId
						+ " in collection "
						+ this.instCollection.getName();
				System.out.println(msg);
			}
		}
	}

	@Override
	public String toString() {
		return "DbInstitutionBuilder [dbCon=" + dbCon + "\n\t" 
				+ ", institutionTableName=" + institutionTableName  + "\n\t" 
				+ ", institutionIdColumn=" + institutionIdColumn  + "\n\t" 
				+ ", institutionNameColumn=" + institutionNameColumn  + "\n\t" 
				+ ", departmentTableName=" + departmentTableName  + "\n\t" 
				+ ", departmentIdColumn=" + departmentIdColumn  + "\n\t" 
				+ ", departmentNameColumn=" + departmentNameColumn  + "\n\t" 
				+ ", departmentInstitutionIdColumn="
				+ departmentInstitutionIdColumn  + "\n\t"  
				+ ", instCollection=" + instCollection.toString() + "]";
	}

	public RdbmsConnection getDbCon() {
		return dbCon;
	}

	public String getInstitutionTableName() {
		return institutionTableName;
	}

	public String getInstitutionIdColumn() {
		return institutionIdColumn;
	}

	public String getInstitutionNameColumn() {
		return institutionNameColumn;
	}

	public String getDepartmentTableName() {
		return departmentTableName;
	}

	public String getDepartmentIdColumn() {
		return departmentIdColumn;
	}

	public String getDepartmentNameColumn() {
		return departmentNameColumn;
	}

	public InstitutionCollection getInstCollection() {
		return instCollection;
	}

	public String getDepartmentInstitutionIdColumn() {
		return departmentInstitutionIdColumn;
	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
	}
}
