package edu.tamucc.hri.rdbms.utils;

public class RdbmsConstants {

	public static final int NotFound = -1;
	public static final int MultipleFound = -99;
	public static final int IgnoreValue = -99999;
	public static final String And = " AND ";
	public static final String SPACE = " ";
	public static final String CommaSpace = ", ";
	public static final String EqualSign = " = ";

	public static final String GRIIDC = "GRIIDC";
	public static final String RIS = "RIS";
	public static final String TRUE = "TRUE";
	public static final String FALSE = "FALSE";

	public static final String DbNull = "null";
	public static final String NewLine = "\n";
	public static final String Tab = "\t";

	// database data types
	public static final String DbUserDefined = "USER-DEFINED";
	public static final String DbBoolean = "boolean";
	public static final String DbBytea = "bytea";
	public static final String DbCharacter = "character";
	public static final String DbCharacterVarying = "character varying";
	public static final String DbDate = "date";
	public static final String DbInteger = "integer";
	public static final String DbNumeric = "numeric";
	public static final String DbText = "text";
	
	//  database rdbms types
	public static final String DbTypePostgres = "postgresql";
	public static final String DbTypeMysql = "mysql";
	
	//  RIS database table names
	public static final String RisInstTableName = "Institutions";
	public static final String RisDeptTableName = "Departments";
	public static final String RisPeopleTableName = "People";
	
	//  GRIIDC database table names
	public static final String GriidcInstTableName = "Institution";
	public static final String GriidcDeptTableName = "Department";
	public static final String GriidcPersonTableName = "Person";
	public static final String GriidcPersonDepartmentRisPeopleIdTableName = "GoMRIPerson-Department-RIS_ID";
	public static final String GriidcGomriPersonTableName = "GoMRIPerson";
	
	private RdbmsConstants() {
	}

}
