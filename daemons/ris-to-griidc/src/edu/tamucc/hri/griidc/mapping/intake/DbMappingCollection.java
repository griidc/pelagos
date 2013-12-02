package edu.tamucc.hri.griidc.mapping.intake;

import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.sql.SQLException;
import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.RisPropertiesAccess;
import edu.tamucc.hri.griidc.exception.DbMappingException;
import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.rdbms.utils.GarbageDetector;
import edu.tamucc.hri.rdbms.utils.MiscUtils;

public class DbMappingCollection {

	private SortedSet<DbMapping> dbMappingSet = Collections
			.synchronizedSortedSet(new TreeSet<DbMapping>());

	private static DbMappingCollection instance = null;

	private static boolean Noisy = false;
	
	public static DbMappingCollection getInstance() {
		if (DbMappingCollection.instance == null) {
			DbMappingCollection.instance = new DbMappingCollection();
		}
		return DbMappingCollection.instance;
	}

	private DbMappingCollection() {
		super();
	}

	public boolean FileData = true;

	public void initialize() throws FileNotFoundException,
			PropertyNotFoundException, SQLException {
		if (FileData) initializeFromDataFile();
		else	initializeFromStaticData();	
	}

	private void initializeFromStaticData() {
		if(isNoisy()) System.out.println("DbMappingCollection.initializeFromStaticData()");
		DbMapping dm = null;
		boolean key = false;
		for (int i = 0; i < sourcePeopleColumnNames.length
				&& i < targetPersonColumnNames.length; i++) {
			key = false;
			if(i <= 1) key = true;
			dm = new DbMapping(sourceTable, sourcePeopleColumnNames[i],
					targetTable, targetPersonColumnNames[i],key);
			
			try {
				this.add(dm);
			} catch (DbMappingException e) {
				printException(e);
			}
		}

		String FooBarTable = "FooBarTable";
		String BarFooTable = "BarFooTable";
		try {
			dm = new DbMapping(FooBarTable, "ColumnSource", BarFooTable,
					"ColumnTarget",false);
			this.add(dm);
		} catch (DbMappingException e) {
			printException(e);
		}
		try {
			dm = new DbMapping(FooBarTable, "DuplicateColumn", FooBarTable,
					"DuplicateColumn",false);
			this.add(dm);
		} catch (DbMappingException e) {
			printException(e);
		}
		try {
			dm = new DbMapping(FooBarTable, "ColumnSource", BarFooTable,
					"ColumnTarget",false);
			this.add(dm);
		} catch (DbMappingException e) {
			printException(e);
		}
	}

	private void printException(DbMappingException e) {
		System.err.println(e.getClass().getName() + " - " + e.getMessage());
	}

	public boolean add(DbMapping dbm) throws DbMappingException {
		if (this.isAllowable(dbm)) {
			return this.dbMappingSet.add(dbm);
		} else
			return false;
	}

	public boolean setContains(DbMapping dbm) {
		return this.dbMappingSet.contains(dbm);
	}

	/**
	 * check this DbMapping for un- allowable state 1. Source Table and Target
	 * Table can not be the same 2. must be unique in the collection - NO
	 * duplicates
	 * 
	 * @param dbm
	 * @return
	 */
	public boolean isAllowable(DbMapping dbm) throws DbMappingException {
		this.isProperTableMapping(dbm);
		this.isUniqueInCollection(dbm);
		return true;
	}

	/**
	 * can't have the same table as source and target
	 * 
	 * @return
	 */
	private boolean isProperTableMapping(DbMapping dbm)
			throws DbMappingException {
		if (dbm.getSourceTableName().equalsIgnoreCase(dbm.getTargetTableName())) {
			throw new DbMappingException(
					"Source table name can NOT be the same as Target table name"
							+ dbm.toString());
		}
		return true;
	}

	private boolean isUniqueInCollection(DbMapping dbm)
			throws DbMappingException {
		if (this.setContains(dbm)) {
			throw new DbMappingException("Duplicates are NOT allowed "
					+ dbm.toString());
		}
		return true;
	}

	public DbMapping[] getTableMappingArray() {
		DbMapping[] tm = new DbMapping[this.dbMappingSet.size()];
		tm = this.dbMappingSet.toArray(tm);
		return tm;
	}

	@Override
	public String toString() {
		DbMapping[] dbmArray = getTableMappingArray();
		StringBuffer sb = new StringBuffer();
		for (DbMapping dbm : dbmArray) {
			sb.append(dbm.toString() + ", ");
		}
		return sb.toString();
	}

	public void report1() {
		DbMapping[] dbmArray = getTableMappingArray();
		if(isNoisy()) System.out.println("--- DB Mapping ---");
		for (DbMapping dbm : dbmArray) {
			if(isNoisy()) System.out.println("\n" + dbm.toString());
		}
	}

	public String sourceTable = "People";
	public String targetTable = "Person";

	public final static String[] sourcePeopleColumnNames = { "People_LastName",
			"People_FirstName", "People_MiddleName", "People_Suffix",
			"People_Title" };

	public final static String[] targetPersonColumnNames = { "Person_LastName",
			"Person_FirstName", "Person_MiddleInitial", "Person_NameSuffix",
			"Person_HonorificTitle" };

	public static void main(String[] args) {
		DbMappingCollection mc = new DbMappingCollection();
		mc.report1();
		try {
			mc.initialize();
		} catch (FileNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (PropertyNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	/**
	 * read the file specified by properties and load the data
	 * 
	 * @throws SQLException
	 * @throws PropertyNotFoundException
	 * @throws FileNotFoundException
	 */
	private void initializeFromDataFile() throws FileNotFoundException,
			PropertyNotFoundException, SQLException {
		String inFileName = RisPropertiesAccess.getInstance()
				.getDatabaseMappingFileName();
		if(isNoisy()) System.out.println("DbMappingCollection.initializeFromDataFile() " + MiscUtils.getRisFileName(inFileName));
		
		try {
			BufferedReader reader = MiscUtils.openInputFile(inFileName);
		
			DbMapping dm = null;
			for (String line = reader.readLine(); line != null; line = reader
					.readLine()) {

				if (line.getBytes()[0] != '#') {
					String[] cols = line.split(",");
					//  eliminate the leading and trailing white spaces
					for(int i = 0;i < cols.length;i++) {
						cols[i] = cols[i].trim();
					}
					//if(isNoisy()) System.out.println("for line: " + line);
					//if(isNoisy()) System.out.println("\tsplit: " + this.inputSplitToString(cols));
					boolean key = false;
                    if(cols.length == 5) { // the fifth col is the key field
                    	key = true;
                    }
					dm = new DbMapping(cols[0], cols[1], cols[2], cols[3],key);
					try {
						this.add(dm);

					} catch (DbMappingException e) {
						printException(e);
					}
				}
			}

		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	
	public static boolean isNoisy() {
		return Noisy;
	}

	public static void setNoisy(boolean noisy) {
		Noisy = noisy;
	}

	private String inputSplitToString(String[] cols) {
		String r = "split string: " ;
		for(String s : cols) {
			r += "[" + s + "] ";
		}
		return r;
	}
	
	public Iterator<DbMapping> getDbMappingIterator() {
		return dbMappingSet.iterator();
	}
}
