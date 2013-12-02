package edu.tamucc.hri.griidc.mapping.specs;

import java.io.FileNotFoundException;
import java.sql.SQLException;
import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.exception.PropertyNotFoundException;
import edu.tamucc.hri.griidc.mapping.intake.DbMapping;
import edu.tamucc.hri.griidc.mapping.intake.DbMappingCollection;

public class DbMappingSpecCollection {

	private SortedSet<DbMappingSpecification> dbMappingSpecs = Collections
			.synchronizedSortedSet(new TreeSet<DbMappingSpecification>());

	private String currentSourceTableName = null;
	private String currentTargetTableName = null;

	public static boolean Noisy = false;
	
	private DbMappingSpecCollection() {
		// TODO Auto-generated constructor stub
	}

	private static DbMappingSpecCollection instance = null;

	public static DbMappingSpecCollection getInstance() {
		if (DbMappingSpecCollection.instance == null)
			DbMappingSpecCollection.instance = new DbMappingSpecCollection();
		return DbMappingSpecCollection.instance;
	}

	public DbMappingSpecification[] getDbMappingSpecificationArray() {
		DbMappingSpecification[] ds = new DbMappingSpecification[this.dbMappingSpecs
				.size()];
		return this.dbMappingSpecs.toArray(ds);
	}

	/**
	 * delegate the creation of the redundant four column db mapping to the
	 * DbMappingCollection. Then, read that collection and create a compact
	 * representation that is sourceTable -> targetTable and a list of sourceCol
	 * -> targetCol;
	 * 
	 * @throws FileNotFoundException
	 * @throws PropertyNotFoundException
	 * @throws SQLException
	 */
	public void createDbMappingSepcifications() throws FileNotFoundException,
			PropertyNotFoundException, SQLException {
		if(Noisy) System.out
				.println("DbMappingSpecCollection.createDbMappingSepcifications()");

		this.getDbMappingCollection().initialize();
		if(Noisy) System.out.println("\tCreated the redundant structure: ");
		this.getDbMappingCollection().report1();

		if(Noisy) System.out
				.println("DbMappingSpecCollection.createDbMappingSepcifications() now read it and compact it");
		DbMapping[] dbMapping = this.getDbMappingCollection()
				.getTableMappingArray();
		int counter = 1;
		DbMappingSpecification currentDbMappingSpecification = null;

		for (DbMapping dbm : dbMapping) {
			// if(Noisy) System.out.println("\n" + (counter++) + " DbMapping: " + dbm);
			if (isSourceOrTargetBreak(dbm)) {
				if (currentDbMappingSpecification != null)
					if(Noisy) System.out.println("DbMappingSpecification complete : \n\t"
							+ currentDbMappingSpecification.toString());
				this.currentSourceTableName = dbm.getSourceTableName();
				this.currentTargetTableName = dbm.getTargetTableName();

				currentDbMappingSpecification = new DbMappingSpecification(
						dbm.getSourceTableName(), dbm.getTargetTableName(),
						dbm.getSourceColumnName(), dbm.getTargetColumnName(),dbm.isKey());
				if(Noisy) System.out.println("\n\nCreated DbMappingSpecification : " + 
						currentDbMappingSpecification.getTableMappingPair().getSourceName() +
						" to " + 
						currentDbMappingSpecification.getTableMappingPair().getTargetName());
				this.dbMappingSpecs.add(currentDbMappingSpecification);

			} else { // same source and target table - add the columns
				currentDbMappingSpecification.addColumnMappingPair(
						dbm.getSourceColumnName(), dbm.getTargetColumnName(),dbm.isKey());
			}
		}
	}

	private boolean isSourceOrTargetBreak(final DbMapping dbmapping) {
		if (this.currentSourceTableName == null
				|| !this.currentSourceTableName.equals(dbmapping
						.getSourceTableName())) {
			return true;
		}
		if (this.currentTargetTableName == null
				|| !this.currentTargetTableName.equals(dbmapping
						.getTargetTableName())) {
			return true;
		}
		return false;
	}

	public void report1() {
		DbMappingSpecification[] mappingArray = this
				.getDbMappingSpecificationArray();

		for (DbMappingSpecification spec : mappingArray) {

			TableMappingPair tmp = spec.getTableMappingPair();
			if(Noisy) System.out.println("\n" + tmp.getSourceName() + " ---> "
					+ tmp.getTargetName());
			ColumnMappingPair[] colMapArray = spec.getColumnMappingPairArray();

			for (ColumnMappingPair colMap : colMapArray) {
				if(Noisy) System.out.println("\t" + colMap.getSourceName() + ">>> "
						+ colMap.getTargetName());
			}
		}
	}

	public DbMappingCollection getDbMappingCollection() {
		return DbMappingCollection.getInstance();
	}
	
	
	

	public static boolean isNoisy() {
		return Noisy;
	}

	public static void setNoisy(boolean noisy) {
		Noisy = noisy;
	}

	public static void main(String[] args) {
		try {
			DbMappingSpecCollection.getInstance()
					.createDbMappingSepcifications();
			if(Noisy) System.out.println("\n\n----------------------- main DbMappingSpecCollection finished ---------");
			DbMappingSpecCollection.getInstance().report1();
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

}
