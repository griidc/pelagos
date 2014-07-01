package edu.tamucc.hri.griidc.rdbms;

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
	public static final String GriidcProjectTableName = "Project";
	public static final String GriidcFundingEnvelopeTableName = "FundingEnvelope";
	
	//  GRIIDC Publication tables
	public static final String GriidcPublicationTableName = "Publication";
	public static final String GriidcProjectPublicationTableName = "Project-Publication";
	public static final String GriidcPersonPublicationTableName = "Person-Publication";
	
	//  GRIIDC Publication Column Names
	public static final String GriidcPublication_Number_ColName =  "RIS_Publication_Number"; // type INTEGER  
	public static final String GriidcPublication_Abstract_ColName   =  "Publication_Abstract"; // type TEXT
	public static final String GriidcPublication_Authors_ColName   =  "Publication_Authors";  // type TEXT
	public static final String GriidcPublication_Year_ColName   =  "Publication_Year";        // type numeric
	public static final String GriidcPublication_DOI_ColName   =  "Publication_DOI";         // type  TEXT
	public static final String GriidcPublication_JournalName_ColName   =  "Publication_JournalName" ;  // type TEXT
	public static final String GriidcPublication_Title_ColName   =  "Publication_Title"; // type Text
	//  GRIIDC Person-Publication table column names
	public static final String GriidcPersonPublication_PersonNumber_ColName = "Person_Number"; // INTEGER
	public static final String GriidcPersonPublication_PublicationNumber_ColName = "RIS_Publication_Number"; // INTEGER
	// GRIIDC Project-Publication table column names
	public static final String GriidcProjectPublication_ProjectNumber_ColName = "Project_Number"; // NUMERIC(4,0)
	public static final String GriidcProjectPublication_FundingEnvelopeCycle_ColName =    "FundingEnvelope_Cycle"; // CHAR(3)
	public static final String GriidcProjectPublication_PublicationNumber_ColName = "RIS_Publication_Number";  // INTEGER 
	
	
	
	
	//  RIS Publication tables
	public static final String RisPubsInfoTableName = "pubsInfo";
	public static final String RisPeoplePublicationTableName = "PeoplePublication";
	public static final String RisProjPublicationTableName = "ProjPublication";
	
	private RdbmsConstants() {
	}

}
