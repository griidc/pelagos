package edu.tamucc.hri.griidc;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Arrays;
import java.util.Collection;
import java.util.Vector;

import edu.tamucc.hri.griidc.altrep.Department;
import edu.tamucc.hri.griidc.altrep.Institution;
import edu.tamucc.hri.griidc.altrep.InstitutionCollection;
import edu.tamucc.hri.griidc.exception.DuplicateRecordException;
import edu.tamucc.hri.griidc.exception.MissingArgumentsException;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.mapping.intake.DbMapping;
import edu.tamucc.hri.griidc.mapping.intake.DbMappingCollection;
import edu.tamucc.hri.griidc.mapping.intake.DbMappingSource;
import edu.tamucc.hri.griidc.mapping.intake.DbMappingTarget;
import edu.tamucc.hri.griidc.mapping.intake.MappingStructure;
import edu.tamucc.hri.griidc.mapping.specs.ColumnMappingPair;
import edu.tamucc.hri.griidc.mapping.specs.DbMappingSpecCollection;
import edu.tamucc.hri.griidc.mapping.specs.DbMappingSpecification;
import edu.tamucc.hri.griidc.mapping.specs.SourceSet;
import edu.tamucc.hri.griidc.mapping.specs.TargetSet;
import edu.tamucc.hri.rdbms.utils.DbInstitutionBuilder;
import edu.tamucc.hri.rdbms.utils.GarbageDetector;
import edu.tamucc.hri.rdbms.utils.IntIntCache;
import edu.tamucc.hri.rdbms.utils.MiscUtils;
import edu.tamucc.hri.rdbms.utils.RdbmsConnection;

public class DeadEndSyncGriidcToRis {

	private RdbmsConnection risDbConnection = null;
	private RdbmsConnection griidcDbConnection = null;
	private RdbmsConnection griidcShortTermDbConnection = null;

	private String wrappedGriidcSchemaName = null;

	GarbageDetector garbageDetector = new GarbageDetector();

	public static boolean Noisy = false;
	public static String And = " AND ";
	public static String SPACE = " ";
	public static String CommaSpace = ", ";
	public static String EqualSign = " = ";

	private int exceptionCount = 0;
	private int risDataErrorCount = 0;

    private InstitutionCollection risInstitutionCollection = new InstitutionCollection("RIS Institution Collection");	
    private InstitutionCollection griidcInstitutionCollection = new InstitutionCollection("GRIIDC Institution Collection");	
    
    private DbInstitutionBuilder risInstitutionBuilder = null;
    private DbInstitutionBuilder griidcInstitutionBuilder = null;
    
    private IntIntCache institutionIdCache = null;

	public DeadEndSyncGriidcToRis() {
		super();
	}

	
	public void initializeStartUp() throws IOException, PropertyNotFoundException, SQLException, ClassNotFoundException {
		MiscUtils.openLogFile();
		MiscUtils.openRisDataErrorFile();
		MiscUtils.openDeveloperReportFile();

		this.getRisDbConnection();
		this.getGriidcDbConnection();
	}
	public RdbmsConnection getRisDbConnection() throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException {
		if (this.risDbConnection == null)
			this.risDbConnection = MiscUtils.getRisDbConnection();
		return this.risDbConnection;
	}

	public RdbmsConnection getGriidcDbConnection()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		if (this.griidcDbConnection == null)
			this.griidcDbConnection = MiscUtils.getGriidcDbConnection();
		this.wrappedGriidcSchemaName = doubleQuote(this.griidcDbConnection
				.getDbSchemaName());
		return this.griidcDbConnection;
	}

	public RdbmsConnection getGriidcShortTermDbConnection()
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException {
		if (this.griidcShortTermDbConnection == null) {
			this.griidcShortTermDbConnection = MiscUtils
					.getGriidcDbConnection();
		}
		this.wrappedGriidcSchemaName = doubleQuote(this.griidcShortTermDbConnection
				.getDbSchemaName());
		return this.griidcShortTermDbConnection;
	}

	public String getWrappedGriidcShemaName() {
		return wrappedGriidcSchemaName;
	}

	/**
	 * read the RIS people table and update the griidc person table get the last
	 * name, first name and middle initial of each entry in the RIS People table
	 * and look for a match in the GRIIDC Person table. Modify GRIIDC to match
	 * or add as needed. Don't delete anyone from GRIIDC. Make NO changes to RIS
	 * 
	 * @throws ClassNotFoundException
	 * @throws SQLException
	 */
	private void risPeopleToGriidcPerson() throws SQLException,
			ClassNotFoundException {
		String query = "SELECT * FROM People";

		// System.out.println("Looking for RIS People " + query);
		ResultSet rrs = this.risDbConnection.executeQueryResultSet(query);
		String lName = null;
		String fName = null;
		String mInitial = null;
		String suffix = null;
		String title = null;
		while (rrs.next()) {
			lName = rrs.getString("People_LastName");
			fName = rrs.getString("People_FirstName");
			mInitial = rrs.getString("People_MiddleName");
			suffix = rrs.getString("People_Suffix");
			title = rrs.getString("People_Title");
			if (Noisy)
				System.out.println("Found RIS People " + title + " " + lName
						+ ", " + fName + " " + mInitial + " " + suffix);
			ResultSet grs = this.findGriidcPerson(lName, fName, mInitial);
			int grsSize = 0;
			while (grs.next()) {
				grsSize++;
			}
			if (grsSize == 0) { // no matches found in GRIIDC database - must
								// add this name
				if (Noisy)
					System.out
							.println("No match found in GRIIDC database - add this person");
				this.addPerson(fName, title, lName, mInitial, suffix);
			} else if (grsSize > 1) {
				if (Noisy)
					System.out
							.println("More than one match found in GRIIDC database");
			} else {
				if (Noisy)
					System.out.println("One match found in GRIIDC database");
			}
		}

	}

	private void validateRisDepartments() throws ClassNotFoundException,
			PropertyNotFoundException, SQLException, IOException {
		String risTableName = "Departments";

		String risDptName = null;
		int risDptDepartmentId = -1;
		int risDptInstitutionId = -1;
		String risDptAddr1 = null;
		String risDptAddr2 = null;
		String risDptCity = null;
		String risDptState = null;
		String risDptZip = null;
		String risDptCountry = null;
		String risDptURL = null;
		double risDptLat = 0.0;
		double risDptLong = 0.0;

		String griidcDptName = null;
		int griidcDptNumber = -1;
		int griidcDptInstitutionNumber = -1;
		// get all the values from the RIS Departments table

		ResultSet rset = null;
		try {
			rset = this.getRisDbConnection().selectAllValuesFromTable(
					risTableName);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

		Vector<Integer> missingRISDepartmentInstitutionCodes = new Vector<Integer>();
		Vector<Integer> missingRISInstitutionCodes = new Vector<Integer>();
		Vector<Integer> matchingInstitutionCodes = new Vector<Integer>();

		try {
			while (rset.next()) {
				risDptName = rset.getString("Department_Name");
				risDptInstitutionId = rset.getInt("Institution_ID");
				risDptDepartmentId = rset.getInt("Department_ID");
				risDptAddr1 = rset.getString("Department_Addr1");
				risDptAddr2 = rset.getString("Department_Addr2");
				risDptCity = rset.getString("Department_City");
				risDptState = rset.getString("Department_State");
				risDptZip = rset.getString("Department_Zip");
				risDptCountry = rset.getString("Department_Country");
				risDptURL = rset.getString("Department_URL");
				risDptLat = rset.getDouble("Department_Lat");
				risDptLong = rset.getDouble("Department_Long");

				int griidcInstitutionNumber = -1;
				String risInstitutionName = null;
				String griidcInstitutionName = null;
				String cachedRisInstitutionName = null;

				try {
					// get the GRIIDC institution number corresponding to the
					// Institution id obtained from the RIS department info
					griidcInstitutionNumber = this.institutionIdCache
							 .getValue(risDptInstitutionId);
					// System.out
					// .println("\nIn Institution Id Cache - Found GRIIDC Institution ID : "
					// + griidcInstitutionNumber + "  for risDptInstitutionId: "
					// + risDptInstitutionId);

					Integer xx = Integer.valueOf(risDptInstitutionId);
					if (!matchingInstitutionCodes.contains(xx)) {
						matchingInstitutionCodes.add(xx);
					}

				} catch (NoRecordFoundException e) {
					String msg = "In RIS Depatment  (" + risDptDepartmentId
							+ ": " + risDptName + ") the RIS Institution ID "
							+ risDptInstitutionId
							+ " does not match an existing GRIIDC Institution";

					Integer xx = Integer.valueOf(risDptInstitutionId);
					if (!missingRISDepartmentInstitutionCodes.contains(xx)) {
						missingRISDepartmentInstitutionCodes.add(xx);
						// System.out.println("\n" + msg);
						// this.writeToRisDataErrorLog(msg);
					}
					continue;
				}

				// find and update the GRIIDC Department table with these values
				String query = "SELECT * FROM "
						// + this.getWrappedGriidcShemaName() + "."
						+ doubleQuote("Department") + " WHERE "
						+ doubleQuote("Department_Name") + EqualSign
						+ singleQuote(risDptName) + And
						+ doubleQuote("Institution_Number") + EqualSign
						+ griidcInstitutionNumber;
				ResultSet griidcRset = this.getGriidcDbConnection()
						.executeQueryResultSet(query);

				int griidcDptCount = 0;
				while (griidcRset.next()) {
					griidcDptCount++;
					griidcDptName = griidcRset.getString("Department_Name");
					griidcDptNumber = griidcRset.getInt("Department_Number");
					griidcDptInstitutionNumber = griidcRset
							.getInt("Institution_Number");
				}
			}
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		System.out.println("\n Institution ID matches found: "
				+ matchingInstitutionCodes.size());

		System.out.println("\n Institution ID matches NOT found: "
				+ missingRISDepartmentInstitutionCodes.size());

		StringBuffer sb = new StringBuffer(
				"The following GRIIDC Institution Numbers are referenced by RIS Department - Institution ID but are not in the GRIIDC Institution table\n");
		Integer[] codes = new Integer[missingRISInstitutionCodes.size()];
		int count = 0;
		codes = missingRISInstitutionCodes.toArray(codes);
		Arrays.sort(codes);
		for (Integer c : codes) {
			count++;
			sb.append(c);
			if ((count % 20) == 0)
				sb.append("\n");
			else
				sb.append(", ");
		}

		this.writeToErrorLog(sb.toString());
		System.out.println("\n" + sb.toString());

		sb = new StringBuffer(
				"The following RIS Institution IDs are referenced by RIS Department but do not exist in the RIS Institutions table.\n");
		codes = new Integer[missingRISDepartmentInstitutionCodes.size()];
		codes = missingRISDepartmentInstitutionCodes.toArray(codes);
		Arrays.sort(codes);
		count = 0;
		for (Integer c : codes) {

			sb.append(c);
			count++;
			if ((count % 20) == 0)
				sb.append("\n");
			else
				sb.append(", ");
		}

		this.writeToErrorLog(sb.toString());
		System.out.println("\n" + sb.toString());
	}

	private void modifyGriidcDepartment(String tableName, String colName,
			String value) throws FileNotFoundException, SQLException,
			ClassNotFoundException, PropertyNotFoundException {

		String query = "SELECT * FROM " + tableName + " WHERE " + colName
				+ EqualSign + value;
		ResultSet rset = this.getGriidcDbConnection().executeQueryResultSet(
				query);
		while (rset.next()) {

		}
	}

	/**
	 * Department and Institution have Delivery Point Ris has Addr1 and Addr2
	 * 
	 * @param addr1
	 * @param addr2
	 * @return
	 */
	private String makeDeliveryPoint(String addr1, String addr2) {
		String s = addr1 + " " + addr2;
		return s.trim();
	}

	public void allocateInstitutionBuilders() {

		this.risInstitutionBuilder = new DbInstitutionBuilder(this.risInstitutionCollection,
				this.risDbConnection, "Institutions","Institution_ID","Institution_Name",
				"Departments", "Department_ID","Department_Name","Institution_ID");
				
		this.griidcInstitutionBuilder = new 
		DbInstitutionBuilder(this.griidcInstitutionCollection,this.griidcDbConnection,
				"Institution","Institution_Number","Institution_Name", 
				"Department", "Department_Number","Department_Name","Institution_Number");
				
				
	}

	public void buildAllCaches() throws FileNotFoundException, SQLException,
			ClassNotFoundException, PropertyNotFoundException {
		this.allocateInstitutionBuilders();
	    this.risInstitutionBuilder.buildInstitutionCollectionFromDb();
	    this.griidcInstitutionBuilder.buildInstitutionCollectionFromDb();
	}
	
	public void reportCaches() {
		System.out.println("\nRIS Institution cache\n" +
	                                 this.risInstitutionBuilder.toString() + "\n" + 
	                                  this.risInstitutionCollection.report( ));

		System.out.println("\nGRIIDC Institution cache\n" + 
		    this.griidcInstitutionBuilder.toString() + "\n" +
		   this.griidcInstitutionCollection.report( ));

	}

	/**
	 * This method uses the cached RIS Institutions data (risInstitutionCollection)
	 * and the cached GRIIDC Institution data (griidcInstitutionCollection)
	 * and validates the RIS Institution Name against the GRIIDC Institution name
	 * It presumes the GRIIDC Country and PostalArea tables being complete and accurate.
	 * @throws PropertyNotFoundException 
	 * @throws IOException 
	 ****/
	
	private void validateRisInstitutionsCollection() throws IOException, PropertyNotFoundException  {
		 Institution[] risInstitutions = this.risInstitutionCollection.getInstitutionArray();
		 for(Institution risInst : risInstitutions) {
			 
			 try {
				Institution griidcInst = this.griidcInstitutionCollection.findInstitution(risInst);
			} catch (NoRecordFoundException e) {
				 String msg = "RIS Institution id: " + risInst.getId() +
						 ", name: " + risInst.getOriginalName() +
						 " not found in GRIIDC Institution";
				 System.out.println("validateRisInstitutionsCollection - " + msg);
				 this.writeToErrorLog(msg);
			}
		 }
	}
	
	private void validateRisDepartmentCollection() throws IOException, PropertyNotFoundException {
		
		Institution griidcInst = null;
		Department griidcDepartment = null;
		Institution[] risInstitutions = this.risInstitutionCollection.getInstitutionArray();
		 for(Institution risInst : risInstitutions) {
			 try {
				griidcInst = this.griidcInstitutionCollection.findInstitution(risInst);
				Department[] risDepartments = risInst.getDepartmentArray();
				 for(Department risDpt : risDepartments) {
					 try {
						griidcDepartment = griidcInst.findDepartment(risDpt);
					} catch (NoRecordFoundException e) {
						String msg = "RIS Department id: " + risDpt.getId() +
								 ", name: " + risDpt.getOriginalName() + " in RIS Institution " + risInst.getId() + ":" + risInst.getOriginalName() +
								 " not found in GRIIDC Institution " + griidcInst.getIntegerId() + " - " + griidcInst.getOriginalName();
						 System.out.println("validateRisDepartmentCollection - " + msg);
						 this.writeToErrorLog(msg);
					}
				 }
			} catch (NoRecordFoundException e) {
				String msg = "RIS Institution id: " + risInst.getId() +
						 ", name: " + risInst.getOriginalName() +
						 " not found in GRIIDC Institutions";
				 this.writeToErrorLog(msg);
			}	  
		 }
	}
	/*****
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws IOException
	 * @throws NoRecordFoundException
	 * @throws DuplicateRecordException
	 */
	private void validateRisInstution() throws ClassNotFoundException,
			PropertyNotFoundException, IOException {
		String risTableName = "Institutions";
		String griidcTableName = "Institution";

		int risInstitutionCount = 0;
		int risInstId = -1;
		String risInstName = null;
		String risInstAddr1 = null;
		String risInstAddr2 = null;
		String risInstCity = null;
		String risInstState = null;
		String risInstZip = null;
		String risInstCountry = null;
		String risInstURL = null;
		double risInstLat = 0.0;
		double risInstLong = 0.0;
		// String risInstKeywords = null;
		// String risInstVerified = null;

		// GRIIDC Institution stuff
		int griidcInstPostalAreaNumber = -1;
		String griidcInstName = null;
		int griidcInstNumber = -1;
		String query = null;

		// get all the values from the RIS Departments table

		ResultSet rset = null;
		ResultSet griidcRset = null;
		try {
			rset = this.getRisDbConnection().selectAllValuesFromTable(
					risTableName);
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		try {
			
			while (rset.next()) { // continue statments branch back to here
				risInstitutionCount++;
				risInstId = rset.getInt("Institution_ID");
				risInstName = rset.getString("Institution_Name");
				risInstAddr1 = rset.getString("Institution_Addr1");
				risInstAddr2 = rset.getString("Institution_Addr2");
				risInstCity = rset.getString("Institution_City");
				risInstState = rset.getString("Institution_State");
				risInstZip = rset.getString("Institution_Zip");
				risInstCountry = rset.getString("Institution_Country");
				risInstURL = rset.getString("Institution_URL");
				risInstLat = rset.getDouble("Institution_Lat");
				risInstLong = rset.getDouble("Institution_Long");
				// risInstKeywords = rset.getString("Institution_Keywords");
				// risInstVerified = rset.getString("Institution_Verified");

				risInstName = risInstName.trim();
				risInstAddr1 = risInstAddr1.trim();
				risInstAddr2 = risInstAddr2.trim();
				risInstCity = risInstCity.trim();
				risInstState = risInstState.trim();
				risInstZip = risInstZip.trim();
				risInstCountry = risInstCountry.trim();
				risInstURL = risInstURL.trim();

				// ??????? the following should come out for production
				// change code to use the 3 character abbreviation when new
				// schema is available
				String correctedCountry = MiscUtils
						.getRisCountryCorrection(risInstCountry);
				int countryNumber = -1;
				try {
					countryNumber = this
							.findCountryNumberFromName(correctedCountry);
				} catch (DuplicateRecordException e2) {
					this.writeToErrorLog(e2.getMessage());
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e2) {
					this.writeToErrorLog(e2.getMessage());
					continue; // branch back to while (rset.next())
				}

				/****
				 * find and update the GRIIDC Institution table with these
				 * values
				 */
				griidcInstPostalAreaNumber = -1;

				try {
					griidcInstPostalAreaNumber = this
							.findGriidcPostalAreaNumber(countryNumber,
									risInstState, risInstCity, risInstZip);
				} catch (DuplicateRecordException e) {
					this.writeToErrorLog(e.getMessage());
					continue; // branch back to while (rset.next())
				} catch (NoRecordFoundException e) {
					this.writeToErrorLog(e.getMessage());
					continue; // branch back to while (rset.next())
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				} catch (MissingArgumentsException e) {
					this.writeToRisDataErrorLog("Error In RIS Institutions record: "
							+ risInstId + "\n\t" + e.getMessage());
					continue; // branch back to while (rset.next())
				}
				/*
				 * if the data in RIS is unusable - skip this record - go to the
				 * next record *
				 */
				/**                                                            **/
				String deliveryPoint = this.makeDeliveryPoint(risInstAddr1,
						risInstAddr2);

				try {
					query = "SELECT * FROM "
							// + this.getWrappedGriidcShemaName() + "."
							+ doubleQuote(griidcTableName) + " WHERE "
							+ doubleQuote("Institution_Name") + EqualSign
							+ singleQuote(risInstName);
					/*********************************************
					 * + And + doubleQuote("PostalArea_Number") + EqualSign +
					 * griidcInstPostalAreaNumber;
					 * 
					 * + And + doubleQuote("Institution_DeliveryPoint") +
					 * EqualSign + singleQuote(deliveryPoint);
					 *********************************************/
					griidcRset = this.getGriidcDbConnection()
							.executeQueryResultSet(query);

				} catch (SQLException e1) {
					System.err
							.println("SQL Error: Find Institution in GRIIDC - Query: "
									+ query);
					e1.printStackTrace();
				}

				int count = 0;
				try {
					while (griidcRset.next()) {
						count++;
						griidcInstName = griidcRset
								.getString("Institution_Name");
						griidcInstNumber = griidcRset
								.getInt("Institution_Number");
						// this.compareDeliveryPoint(griidcInstName,
						// griidcRset.getString("Institution_DeliveryPoint"),deliveryPoint);
					}
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
				if (count == 0) {
					// We don't add Institution Records

					String msg = "\nMissing GRIIDC Institution table record "
							+ "Institution_Name: " + risInstName
							+ ", PostalArea_Number: "
							+ griidcInstPostalAreaNumber
							+ ", Institution_DeliveryPoint: " + deliveryPoint;
					System.out.println(msg);
					this.writeToErrorLog(msg);

				} else if (count == 1) {
					// cache the GRIIDC Institution number with the RIS
					// institution number as key
					Integer oldValue = this.institutionIdCache.cacheValue(
							risInstId, griidcInstNumber);
					if (oldValue != null)
						System.err.println("Previous cache value for key "
								+ risInstId + " was " + oldValue);

					//
					// We don't modify Institution records !!!
					//
					/***
					 * String msg = "Modify GRIIDC Institution table matching "
					 * + "\n\tgriidcInstNumber: " + griidcInstNumber +
					 * ", Institution_Name: " + risInstName +
					 * ", PostalArea_Number: " + griidcInstPostalAreaNumber +
					 * ", Institution_DeliveryPoint: " + deliveryPoint;
					 * System.out.println(msg);
					 * 
					 * String modifyQuery = null; try { modifyQuery =
					 * this.getModifyInstitutionQuery( griidcInstNumber,
					 * risInstName, griidcInstPostalAreaNumber, deliveryPoint,
					 * risInstURL, risInstLong, risInstLat);
					 * this.griidcDbConnection
					 * .executeQueryBoolean(modifyQuery); } catch (SQLException
					 * e) { System.err .println(
					 * "SQL Error: Modify Institution in GRIIDC - Query: " +
					 * modifyQuery); e.printStackTrace(); }
					 ****************/
				} else if (count > 1) { // duplicates
					String msg = "There are "
							+ count
							+ " records in the  GRIIDC Institution table matching "
							+ "Institution_Name: " + risInstName
							+ ", PostalArea_Number: "
							+ griidcInstPostalAreaNumber
							+ ", Institution_DeliveryPoint: " + deliveryPoint;

					this.writeToErrorLog(msg);
				}
			}
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		if (this.getExceptionCount() == 0) {
			this.writeToErrorLog("No errors found ");
			this.resetExceptionCount();
		}

		System.out.println("Read " + risInstitutionCount
				+ " RIS Institutions records");
		System.out.println("RIS records with data errors: "
				+ this.getRisDataErrorCount());
	}

	public boolean compareDeliveryPoint(String instName, String gp, String rp) {
		int gLen = gp.length();
		int rLen = rp.length();
		if (gLen != rLen) {
			System.out.println("\nNon matching delivery point - institutuion: "
					+ instName);
			System.out.println("\n" + gp + "\n" + rp);
			System.out.println("G: " + gp + ", L: " + gLen);
			System.out.println("R: " + rp + ", L: " + rLen);
			return false;
		}
		// compare byte by byte
		for (int i = 0; i < gLen; i++) {
			int gn = gp.charAt(i);
			int rn = rp.charAt(i);
			if (gn != rn) {
				System.out
						.println("\nNon matching delivery point\n" + instName);
				System.out.println("\n" + gp + "\n" + rp);
				System.out.println("Length: " + gLen + " at position: " + i
						+ " g->" + gn + " p->" + rn);
				return false;
			}
		}

		return true;
	}

	private String getAddInstitutionQuery(String risInstName,
			int griidcPostalAreaNumber, String deliveryPoint,
			String risInstURL, double risInstLon, double risInstLat)
			throws SQLException, ClassNotFoundException {
		StringBuffer sb = new StringBuffer("INSERT INTO ");
		sb.append(doubleQuote("Institution") + SPACE + "(");
		sb.append(doubleQuote("Institution_Name"));
		sb.append(CommaSpace + doubleQuote("PostalArea_Number"));
		sb.append(CommaSpace + doubleQuote("Institution_DeliveryPoint"));
		sb.append(CommaSpace + doubleQuote("Institution_URL"));
		sb.append(CommaSpace + doubleQuote("Institution_GeoCoordinate"));
		sb.append(") VALUES (");
		// the values are here
		sb.append(singleQuote(risInstName));
		sb.append(CommaSpace + griidcPostalAreaNumber);
		sb.append(CommaSpace + singleQuote(deliveryPoint));
		sb.append(CommaSpace + singleQuote(risInstURL));
		sb.append(CommaSpace + makeGeometryString(risInstLon, risInstLat));
		sb.append(" )");
		return sb.toString();
	}

	private String makeGeometryString(double lon, double lat) {
		return " " + "ST_SetSRID(ST_MakePoint(" + lon + "," + lat + "), 4326)";
	}

	private String getModifyInstitutionQuery(int griidcInstNumber,
			String risInstName, int griidcPostalAreaNumber,
			String deliveryPoint, String risInstURL, double risInstLon,
			double risInstLat) throws SQLException, ClassNotFoundException {
		StringBuffer sb = new StringBuffer("UPDATE  ");
		sb.append(doubleQuote("Institution") + SPACE + " SET ");
		sb.append(doubleQuote("PostalArea_Number") + EqualSign
				+ griidcPostalAreaNumber);
		sb.append(CommaSpace + doubleQuote("Institution_DeliveryPoint")
				+ EqualSign + singleQuote(deliveryPoint));
		sb.append(CommaSpace + doubleQuote("Institution_URL") + EqualSign
				+ singleQuote(risInstURL));
		sb.append(CommaSpace + doubleQuote("Institution_GeoCoordinate")
				+ EqualSign + makeGeometryString(risInstLon, risInstLat));
		sb.append(" WHERE " + doubleQuote("Institution_Number") + EqualSign
				+ griidcInstNumber);
		return sb.toString();
	}

	public boolean validatePostalAreaData(String state, String city, String zip)
			throws MissingArgumentsException {

		StringBuffer errorMsg = new StringBuffer();
		boolean completeParms = true;
		if (MiscUtils.isStringEmpty(state)) {
			errorMsg.append("State is NULL or empty");
			completeParms = false;
		} else
			errorMsg.append("State=" + state);

		if (MiscUtils.isStringEmpty(city)) {
			errorMsg.append(", City is NULL or empty");
			completeParms = false;
		} else
			errorMsg.append(", City =" + city);

		if (MiscUtils.isStringEmpty(zip)) {
			errorMsg.append(", Zip is NULL or empty");
			completeParms = false;
		} else
			errorMsg.append(", Zip=" + zip);
		if (!completeParms) {
			MissingArgumentsException ex = new MissingArgumentsException(
					"Invalid or missing Postal Area information: "
							+ errorMsg.toString());
			throw ex;
		}
		return true;

	}

	/**
	 * looking in the PostalArea table for a match from a RIS record
	 * 
	 * @param city
	 * @param state
	 * @param zip
	 * @param countryName
	 * @return
	 * @throws FileNotFoundException
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws DuplicateRecordException
	 * @throws NoRecordFoundException
	 * @throws MissingArgumentsException
	 */
	public int findGriidcPostalAreaNumber(int countryNumber, String state,
			String city, String zip) throws FileNotFoundException,
			SQLException, ClassNotFoundException, PropertyNotFoundException,
			DuplicateRecordException, NoRecordFoundException,
			MissingArgumentsException {

		this.validatePostalAreaData(state, city, zip);

		String query = "SELECT * FROM "
				// + this.getWrappedGriidcShemaName() + "."
				+ doubleQuote("PostalArea") + " WHERE "
				+ doubleQuote("Country_Number") + EqualSign + countryNumber
				+ And + doubleQuote("PostalArea_AdministrativeAreaAbbr")
				+ EqualSign + singleQuote(state) + And
				+ doubleQuote("PostalArea_City") + EqualSign
				+ singleQuote(city) + And
				+ doubleQuote("PostalArea_PostalCode") + EqualSign
				+ singleQuote(zip);

		ResultSet rset = null;
		try {
			rset = this.getGriidcShortTermDbConnection().executeQueryResultSet(
					query);
		} catch (Exception e) {
			System.out.println("SQL Exception on query" + query
					+ "\n message: " + query);
		}
		int postalAreaNumber = -1; // this is the key
		int count = 0;
		while (rset.next()) {
			count++;
			postalAreaNumber = rset.getInt("PostalArea_Number");
		}
		if (count == 0) {
			String msg = "NO record found in the GRIIDC PostalArea table for  country number: "
					+ countryNumber
					+ ",  state: "
					+ state
					+ ", city: "
					+ city
					+ ", zip: " + zip;
			throw new NoRecordFoundException(msg + "\n" + query);
		} else if (count > 1) { // duplicates
			String msg = "There are "
					+ count
					+ " records in the  GRIIDC PostalArea table which match  country number: "
					+ countryNumber + ",  state: " + state + ", city: " + city
					+ ". zip: " + zip;
			throw new DuplicateRecordException(msg);
		}
		//
		// only one match found - return the number
		//
		return postalAreaNumber;
	}

	/**
	 * TODO modify this for switching between full name, two char abbreviation
	 * and three char abbreviation
	 * 
	 * @param countryName
	 * @return
	 * @throws FileNotFoundException
	 * @throws SQLException
	 * @throws ClassNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws DuplicateRecordException
	 * @throws NoRecordFoundException
	 */
	public int findCountryNumberFromName(String countryName)
			throws FileNotFoundException, SQLException, ClassNotFoundException,
			PropertyNotFoundException, DuplicateRecordException,
			NoRecordFoundException {
		int num = -1; // this is the key in Country table
		this.getGriidcShortTermDbConnection();
		String query = "SELECT * FROM  "
				// + getWrappedGriidcShemaName() + "."
				+ doubleQuote("Country") + "  WHERE  "
				+ doubleQuote("Country_Name") + EqualSign
				+ singleQuote(countryName);

		// System.out.println("Query: " + query);
		ResultSet rset = this.getGriidcShortTermDbConnection()
				.executeQueryResultSet(query);
		int count = 0;
		while (rset.next()) {
			count++;
			num = rset.getInt("Country_Number");

		}
		if (count == 0) {
			String msg = "NO record found in the GRIIDC Country table with the Country_Name: "
					+ countryName;
			throw new NoRecordFoundException(msg);
		} else if (count > 1) { // duplicates
			String msg = "There are "
					+ count
					+ " records in the GRIIDC Country table with the Country_Name: "
					+ countryName;
			throw new DuplicateRecordException(msg);
		}
		return num;

	}

	public void reportValuesWithColHeaders(String[] colNames,
			String[] valueLines) {
		// print headers
		StringBuffer sb = new StringBuffer();
		for (String colN : colNames) {
			sb.append(colN + "\t");
		}
		System.out.println(sb.toString());

		for (String l : valueLines) {
			if (this.garbageDetector.hasHtmlCode(l))
				;
			else
				System.out.println(l);
		}
	}

	/**
	 * Using the DbMappingCollection ...
	 * 
	 * @see DbMappingCollection
	 * @see DbMapping
	 * @see DbMappingTarget
	 * @see DbMappingSource
	 * @see MappingStructure ... reading the mapping directives, get data from
	 *      the source table and columns and add or modify as needed to the
	 *      target table and columns Don't delete anyone from GRIIDC. Make NO
	 *      changes to RIS
	 * 
	 *      The data for the mapping directives is in a file in the Data
	 *      directory and specified in the ris.properties file.
	 * @throws ClassNotFoundException
	 * @throws SQLException
	 * @throws PropertyNotFoundException
	 * @throws IOException
	 */
	private void mapRisToGriidc() throws ClassNotFoundException,
			PropertyNotFoundException, SQLException, IOException {
		DbMappingSpecCollection.getInstance().createDbMappingSepcifications();
		if (Noisy)
			System.out
					.println("\n\n----------------------- SyncGriidcToRis ---------------");
		DbMappingSpecCollection.getInstance().report1();

		/*****************************
		 * each DbMappingSpecification describes source Table and Columns and
		 * the corresponding target Table and Columns query the Source table for
		 * the specified columns and
		 * 
		 */

		DbMappingSpecification[] mappingSpecs = DbMappingSpecCollection
				.getInstance().getDbMappingSpecificationArray();
		ResultSet srcResults = null;
		ResultSet targetResults = null;
		String lastQuery = null;
		MiscUtils.getLogFileWriter();
		int recordsModified = 0;
		int recordsAdded = 0;
		int duplicatesFound = 0;
		int totalRecords = 0;

		for (DbMappingSpecification dms : mappingSpecs) {
			String risTable = dms.getSourceSet().getTableName();
			String griidcTable = dms.getTargetSet().getTableName();
			// check - do both tables exist? if one or both don't exist skip
			// this mapping
			if (!(this.risDbConnection.doesTableExist(risTable) && this.griidcDbConnection
					.doesTableExist(griidcTable))) {
				System.out
						.println("\nSyncGriidcToRis.mapRisToGriidc() one or both of these tables does not exist");
				System.out.println("RIS table: " + risTable
						+ " or GRIIDC table: " + griidcTable);

				continue;
			}
			String sourceQuery = this.formatFindSourceQuery(dms);

			System.out.println("\n\n**************************************\n"
					+ " Map table: " + dms.getSourceSet().getTableName()
					+ " to table: " + dms.getTargetSet().getTableName());
			if (Noisy)
				System.out.println(formatSourceHeader(dms));
			try {
				//
				// find the set of records matching the mapping specifications
				//
				lastQuery = sourceQuery;
				if (Noisy)
					System.out.println("Query from specification: "
							+ sourceQuery);
				srcResults = this.risDbConnection
						.executeQueryResultSet(sourceQuery);
				/** don't forget ResultSet indexing starts from 1 not 0 **/
				int colCount = srcResults.getMetaData().getColumnCount();
				if (Noisy)
					System.out.println("Columns in result set: " + colCount);
				/**
				 * above is meta data stuff. Below this is mostly about the data
				 * returned from the query
				 */
				String temp = null;
				String[] sourceResultValues = new String[colCount];

				recordsModified = 0;
				recordsAdded = 0;
				duplicatesFound = 0;
				totalRecords = 0;
				//
				// read each row in the result set
				//

				while (srcResults.next()) {
					StringBuffer sourceValuesString = new StringBuffer();
					StringBuffer sbResult = new StringBuffer();
					for (int i = 1; i <= colCount; i++) {
						try {
							sourceResultValues[i - 1] = temp = srcResults
									.getString(i);
							sourceValuesString.append(srcResults.getString(i)
									+ ", ");
						} catch (SQLException e) {
							temp = "UNKNOWN";
							Collection<String> msgs = MiscUtils
									.newStringCollection("SQL Exception on query: col name: "
											+ srcResults.getMetaData()
													.getColumnName(i));
							msgs.add(e.getMessage());
							this.writeToErrorLog(msgs);
						}
						sbResult.append(temp);
						sbResult.append("\t");
					}
					if (Noisy)
						System.out.println("\nSource Query found: "
								+ sbResult.toString());
					//
					// for each item in the source result set
					// create a target query. If the target is found, update it.
					// if the target is not found add it.
					//
					String findTargetQuery = this.formatFindTargetQuery(dms,
							sourceResultValues);
					lastQuery = findTargetQuery;
					if (Noisy)
						System.out.println("Target Query: " + findTargetQuery);
					String[] targetResultValues;
					int matchesFound = 0;
					try {
						targetResults = this.griidcDbConnection
								.executeQueryResultSet(findTargetQuery);
						colCount = targetResults.getMetaData().getColumnCount();
						targetResultValues = new String[colCount];
						matchesFound = 0;
						while (targetResults.next()) {
							matchesFound++;
							StringBuffer sbTargetResult = new StringBuffer();
							for (int i = 1; i <= colCount; i++) {
								targetResultValues[i - 1] = temp = targetResults
										.getString(i);
								sbTargetResult.append(temp);
								sbTargetResult.append("\t");
							}
							if (Noisy)
								System.out.println("Target Query found: "
										+ sbTargetResult.toString());
						}

						totalRecords++;
						if (matchesFound == 0) { // add this information to the
													// target database
							// format an insert statement
							String insertTargetStatement = this
									.formatTargetInsert(dms, sourceResultValues);
							try {
								int success = this.griidcDbConnection
										.executeUpdate(insertTargetStatement);
								recordsAdded += success;
								if (success == 1) {
									Collection<String> msgs = MiscUtils
											.newStringCollection("Table: "
													+ dms.getTargetSet()
															.getTableName());
									msgs.add("added record: "
											+ sourceValuesString.toString());
									this.writeToErrorLog(msgs);
								}
							} catch (SQLException e) {
								Collection<String> msgs = MiscUtils
										.newStringCollection("SQLException: "
												+ e.getMessage());
								msgs.add("Unable to insert into target DB");
								msgs.add("Statement: " + insertTargetStatement);
								this.writeToErrorLog(msgs);
							}

						} else if (matchesFound == 1) { // update this record
							String modifyStatement = this
									.formatTargetModify(dms,
											sourceResultValues,
											targetResultValues);
							if (Noisy)
								System.out.println("Modify this "
										+ dms.getTargetSet().getTableName()
										+ " " + modifyStatement);
							recordsModified++;
							// this.griidcDbConnection.executeUpdate(modifyStatement);
						} else { // if (matchesFound > 1)
							// duplicates in the database
							Collection<String> msgs = MiscUtils
									.newStringCollection("Duplicates found in "
											+ dms.getTargetSet().getTableName());
							msgs.add(matchesFound + " matches found for "
									+ findTargetQuery);
							this.writeToErrorLog(msgs);
							duplicatesFound++;
						}
					} catch (Exception e1) {
						// TODO Auto-generated catch block
						e1.printStackTrace();
					}

				}
			} catch (SQLException e) {

				Collection<String> msgs = MiscUtils
						.newStringCollection("SQLException in SyncGriidcToRis.mapRisToGriidc()");
				String eMsg = e.getMessage();
				msgs.add(eMsg);

				String position = "Position:";
				int ndx = eMsg.indexOf(position);
				msgs.add(lastQuery);
				if (ndx > -1) {
					String ss = eMsg.substring(ndx + position.length());
					msgs.add("error in col: " + ss);
					ss = ss.trim();
					int errorColumn = Integer.valueOf(ss).intValue();
					StringBuffer sb = new StringBuffer();
					for (int i = 1; i < errorColumn; i++) {
						if ((i % 10) == 0)
							sb.append('|');
						else
							sb.append('-');
					}
					sb.append('^');
					msgs.add(sb.toString());
				}
				this.writeToErrorLog(msgs);
			}
			/*********
			 * System.out.println("Hit return to continue");
			 * 
			 * try { System.in.read(); } catch (IOException e) { // TODO
			 * Auto-generated catch block e.printStackTrace(); }
			 */
			Collection<String> msgs = MiscUtils.newStringCollection();
			msgs.add("Total records read from: "
					+ dms.getSourceSet().getTableName() + EqualSign
					+ totalRecords);
			msgs.add("For table " + dms.getTargetSet().getTableName());
			msgs.add("Records added: " + recordsAdded);
			msgs.add("Records modified: " + recordsModified);
			msgs.add("Duplicate records found: " + duplicatesFound);
			this.writeToErrorLog(msgs);
		} // end loop for (DbMappingSpecification dms : mappingSpecs)

		MiscUtils.closeLogFile();
		MiscUtils.closeRisDataErrorFile();
	}

	private String formatSourceHeader(DbMappingSpecification dms) {
		SourceSet sourceSet = dms.getSourceSet();
		StringBuffer sb = new StringBuffer("Table: " + sourceSet.getTableName());
		sb.append("\n");
		String[] cols = sourceSet.getColumnNames();
		for (String colName : cols) {
			sb.append("\t" + colName);
		}
		return sb.toString();
	}

	/**
	 * find the matching row in the target database
	 * 
	 * @param spec
	 * @return
	 */
	private String formatFindSourceQuery(DbMappingSpecification spec) {
		StringBuffer sb = new StringBuffer("SELECT ");
		SourceSet sourceSet = spec.getSourceSet();
		String[] cols = sourceSet.getColumnNames();

		for (String s : cols) {
			sb.append(s);
			sb.append(", ");
		}
		String q = sb.toString().trim();
		int ndx = q.lastIndexOf(',');
		q = q.substring(0, ndx);
		q += " FROM " + sourceSet.getTableName();
		return q;
	}

	/**
	 * this function takes the results from source table that identifies a
	 * record in the target database and formats a query to find that record in
	 * the target.
	 * 
	 * @param dms
	 * @param resultValues
	 * @return
	 */
	private String formatFindTargetQuery(DbMappingSpecification dms,
			String[] resultValues) {
		TargetSet targetSet = dms.getTargetSet();
		String[] cols = targetSet.getColumnNames();

		StringBuffer sb = new StringBuffer("SELECT ");
		for (String s : cols) {
			sb.append(doubleQuote(s));
			sb.append(", ");
		}
		String q = sb.toString().trim();
		int ndx = q.lastIndexOf(',');
		q = q.substring(0, ndx);
		// from clause
		q += " FROM " + doubleQuote(targetSet.getTableName());
		q += formatWhereClause(dms, resultValues);
		return q;
	}

	/**
	 * the source Query resultValues sourceQueryResultValues should have a
	 * one-to-one correspondence with the collumnMappingPair column Names
	 * 
	 * @param dms
	 * @param resultValues
	 * @return
	 */
	private String formatWhereClause(DbMappingSpecification dms,
			String[] sourceQueryResultValues) {
		ColumnMappingPair[] cmps = dms.getColumnMappingPairArray();
		int[] keyIndexes = dms.getKeyColumnMappingPairNdx();
		StringBuffer sb = new StringBuffer(" WHERE ");
		boolean previousValue = false;
		for (int i = 0; i < keyIndexes.length; i++) {
			int n = keyIndexes[i];
			// if the sourceQueryResultValues has a blank or null field don't
			// include it
			String tempValue = sourceQueryResultValues[n];
			if (!(tempValue == null || tempValue.trim().length() <= 0)) { // write
																			// this
																			// one
				if (previousValue) {
					sb.append(And);
				}
				sb.append(doubleQuote(cmps[n].getTargetName()));
				sb.append(EqualSign);
				sb.append(RdbmsConnection
						.wrapInSingleQuotes(sourceQueryResultValues[n]));
				previousValue = true;
			}
		}
		return sb.toString();
	}

	private String formatTargetInsert(DbMappingSpecification dms,
			String[] sourceResultValues) {

		// INSERT INTO table_name (column1,column2,column3,...)
		// VALUES (value1,value2,value3,...);
		TargetSet targetSet = dms.getTargetSet();
		String[] colNames = targetSet.getColumnNames();

		StringBuffer sb = new StringBuffer("INSERT INTO ");
		sb.append(doubleQuote(targetSet.getTableName().trim()) + SPACE + "(");
		boolean previousItem = false;
		for (String s : colNames) {
			if (previousItem)
				sb.append(", ");
			sb.append(doubleQuote(s));
			previousItem = true;
		}
		sb.append(") VALUES (");

		String temp = null;
		previousItem = false;
		for (String s : sourceResultValues) {
			if (previousItem)
				sb.append(", ");
			temp = s.trim();
			if (temp.length() == 0)
				sb.append("NULL");
			else
				sb.append(singleQuote(temp));
			previousItem = true;
		}
		sb.append(" )");
		return sb.toString();
	}

	private String formatTargetModify(DbMappingSpecification dms,
			String[] sourceResultValues, String[] targetValues) {

		if (Noisy) {
			System.out.println("SyncGriidcToRis.formatTargetModify()");
			System.out.println("\tsourceResultValues");
			for (String s : sourceResultValues) {
				System.out.println("\t" + s);
			}

			System.out.println("\ttargetValues");
			for (String s : targetValues) {
				System.out.println("\t" + s);
			}

		}
		// UPDATE table_name SET
		// column1=value1,column2=value2,column3=value3,...
		// WHERE key=value;
		TargetSet targetSet = dms.getTargetSet();
		String[] targetColNames = targetSet.getColumnNames();

		StringBuffer sb = new StringBuffer("UPDATE ");
		sb.append(doubleQuote(targetSet.getTableName().trim()) + SPACE
				+ " SET ");
		boolean previousItem = false;
		String temp = null;
		for (int i = 0; i < targetColNames.length
				&& i < sourceResultValues.length; i++) {
			if (previousItem)
				sb.append(", ");
			temp = targetColNames[i].trim();
			sb.append(doubleQuote(temp));
			sb.append("=");
			temp = sourceResultValues[i].trim();
			if (temp.length() == 0)
				sb.append("NULL");
			else
				sb.append(singleQuote(temp));
			previousItem = true;
		}
		sb.append(" WHERE ");
		String[] targetKeyColumnNames = dms.getTargetKeyColumnNames();
		// find each target key col in the list of all target columns
		// and match the value. targetValues correspond to targetKeyColumnNames
		String targetKeyCol = null;
		String targetKeyValue = null;
		previousItem = false;

		for (int j = 0; j < targetKeyColumnNames.length; j++) {
			targetKeyCol = targetKeyColumnNames[j].trim();
			for (int i = 0; i < targetColNames.length; i++) {
				targetKeyValue = targetValues[i];
				if (targetKeyCol.equals(targetColNames[i].trim())) {
					if (previousItem)
						sb.append(", ");
					sb.append(doubleQuote(targetKeyCol));
					sb.append("=");
					sb.append(RdbmsConnection
							.wrapInSingleQuotes(targetKeyValue));
					previousItem = true;
				}

			}
		}

		return sb.toString();
	}

	private boolean addPerson(String firstName, String title, String lastName,
			String middleInitial, String suffix) throws SQLException,
			ClassNotFoundException {

		Vector<String> values = new Vector<String>();
		Vector<String> colNames = new Vector<String>();
		if (firstName.length() > 0) {
			colNames.add(doubleQuote("Person_FirstName"));
			values.add(RdbmsConnection.wrapInDollarQuotes(firstName));
		}
		if (title.length() > 0) {
			colNames.add(doubleQuote("Person_HonorificTitle"));
			values.add(RdbmsConnection.wrapInDollarQuotes(title));
		}
		if (lastName.length() > 0) {
			colNames.add(doubleQuote("Person_LastName"));
			values.add(RdbmsConnection.wrapInDollarQuotes(lastName));
		}
		if (middleInitial.length() > 0) {
			colNames.add(doubleQuote("Person_MiddleInitial"));
			values.add(singleQuote(RdbmsConnection.getFirstAlpha(middleInitial)));
		}
		if (suffix.length() > 0) {
			colNames.add(doubleQuote("Person_NameSuffix"));
			values.add(RdbmsConnection.wrapInDollarQuotes(suffix));
		}
		StringBuffer query = new StringBuffer("INSERT INTO "
				+ getWrappedGriidcShemaName() + "." + doubleQuote("Person")
				+ " ( ");
		// add collumn names
		for (int i = 0; i < colNames.size(); i++) {
			query.append(colNames.elementAt(i));
			if ((colNames.size() - 1) > i)
				query.append(", ");
		}
		query.append(" ) VALUES ( ");
		// add values
		String temp = null;
		for (int i = 0; i < values.size(); i++) {
			temp = values.elementAt(i);
			temp = (temp);
			query.append(temp);
			if ((values.size() - 1) > i)
				query.append(", ");
		}
		query.append(" )");
		String q = query.toString();
		// q = RdbmsConnection.wrapInDollarQuotes(q);
		if (Noisy)
			System.out.println("SyncGriidcToRis.addPerson() - query: " + q);

		this.griidcDbConnection.executeQueryBoolean(query.toString());
		return true;

	}

	private String griidcTableNameWrap(final String tableName) {
		return doubleQuote(griidcDbConnection.getDbSchemaName()) + "."
				+ doubleQuote(tableName);
	}

	private String doubleQuote(final String s) {
		return RdbmsConnection.wrapInDoubleQuotes(s);
	}

	private String singleQuote(final String s) {
		return RdbmsConnection.wrapInSingleQuotes(s);
	}

	private String dollarQuotes(final String s) {
		return RdbmsConnection.wrapInDollarQuotes(s);
	}

	private ResultSet findGriidcPerson(String lastName, String firstName,
			String middleName) throws SQLException, ClassNotFoundException {
		String query = "SELECT * FROM " + this.getWrappedGriidcShemaName()
				+ "." + doubleQuote("Person") + "  WHERE "
				+ doubleQuote("Person_LastName") + EqualSign
				+ singleQuote(lastName) + " AND  "
				+ doubleQuote("Person_FirstName") + EqualSign
				+ singleQuote(firstName);

		ResultSet rs = this.griidcDbConnection.executeQueryResultSet(query);

		return rs;
	}

	private String getLogFileName() throws FileNotFoundException,
			PropertyNotFoundException, SQLException {
		return MiscUtils.getLogFileName();
	}

	private String getRisDataErrorLogFileName() throws FileNotFoundException,
			PropertyNotFoundException, SQLException {
		return MiscUtils.getRisDataErrorLogName();
	}

	public static boolean isNoisy() {
		return Noisy;
	}

	public static void setNoisy(boolean noisy) {
		Noisy = noisy;
	}

	private int writeToErrorLog(Collection<String> errMessages)
			throws IOException, PropertyNotFoundException {
		MiscUtils.writeToLog(errMessages);
		this.exceptionCount++;
		return this.exceptionCount;
	}

	private int writeToErrorLog(String msg) throws IOException,
			PropertyNotFoundException {
		MiscUtils.writeToLog(msg);
		this.exceptionCount++;
		return this.exceptionCount;
	}

	private int writeToRisDataErrorLog(Collection<String> errMessages)
			throws IOException, PropertyNotFoundException {
		MiscUtils.writeToRisDataErrorLog(errMessages);
		this.risDataErrorCount++;
		return this.risDataErrorCount;
	}

	private int writeToRisDataErrorLog(String msg) throws IOException,
			PropertyNotFoundException {
		MiscUtils.writeToRisDataErrorLog(msg);
		this.risDataErrorCount++;
		return this.risDataErrorCount;
	}

	private int getExceptionCount() {
		return this.exceptionCount;
	}

	private int resetExceptionCount() {
		return this.exceptionCount = 0;
	}

	private int getRisDataErrorCount() {
		return this.risDataErrorCount;
	}

	public static void main(String[] args) {

		DeadEndSyncGriidcToRis synker = new DeadEndSyncGriidcToRis();
		DeadEndSyncGriidcToRis.setNoisy(false);

		try {
			synker.initializeStartUp();
			synker.getRisDbConnection()
					.reportTableAndColumnNames(risTableNames,MiscUtils.getDeveloperReportFileWriter());
			
			DbInstitutionBuilder.setDebug(false);
			InstitutionCollection.setDebug(false);
			synker.buildAllCaches();
			synker.reportCaches();

			synker.validateRisInstitutionsCollection();
			//synker.validateRisDepartmentCollection();
			//synker.validateRisInstution();
			//synker.validateRisDepartments();
			//synker.risPeopleToGriidcPerson();

			MiscUtils.closeLogFile();
			MiscUtils.closeRisDataErrorFile();
			System.out.println("SyncGriidcToRis finished");
			System.out.println("log file is: " + synker.getLogFileName());
			System.out.println("Errors reported to log file: "
					+ synker.getExceptionCount());
			System.out.println("RIS Data Error log file is: "
					+ synker.getRisDataErrorLogFileName());
			System.out.println("RIS Data Errors reported to log file: "
					+ synker.getRisDataErrorCount());
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (ClassNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	public static String[] risTableNames = {
			// "ConfReg",
			// "Country",
			"Departments",
			// "FundingSource",
			// "G_Project",
			// "GulfBaseInstitutions",
			// "GulfBasePeople",
			"Institutions",
			// "Keywords",
			// "Log",
			"People",
			// "PeoplePublication",
			// "Programs",
			// "ProjKeywords",
			// "ProjPeople",
			// "ProjPublication",
			// "ProjThemes",
			"Projects"
	// "Roles",
	// "State",
	// "Students",
	// "Themes",
	// "pubsInfo"
	};
}
