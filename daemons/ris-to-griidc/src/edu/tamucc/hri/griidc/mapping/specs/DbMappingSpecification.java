package edu.tamucc.hri.griidc.mapping.specs;

import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;
import java.util.Vector;
/**
 * An instance of this class represents the correspondence of
 * one table and it's columns to another table and it's columns
 * @author jvh
 *
 */
public class DbMappingSpecification implements
		Comparable<DbMappingSpecification> {

	
	public static boolean Noisy = false;
	private TableMappingPair tableMappingPair = null;
	
	private SortedSet<ColumnMappingPair> columnMappingSpecifications = Collections
			.synchronizedSortedSet(new TreeSet<ColumnMappingPair>());


	public DbMappingSpecification(String sourceTableName,String targetTableName, 
			                      String sourceCol1, String targetCol1, boolean key) {
		this(sourceTableName, targetTableName);
		this.addColumnMappingPair(sourceCol1, targetCol1, key);
	}
	
	public DbMappingSpecification(String sourceTableName,
			String targetTableName) {
       this.tableMappingPair = new TableMappingPair(sourceTableName,targetTableName);
	}
	
	public static boolean isNoisy() {
		return Noisy;
	}

	public static void setNoisy(boolean noisy) {
		Noisy = noisy;
	}

	public boolean addColumnMappingPair(final String sourceColumnName, final String targetColumnName, boolean key) {
       return this.columnMappingSpecifications.add(new ColumnMappingPair(sourceColumnName,targetColumnName,key));
	}
	
	public String toString() {
		StringBuffer sb = new StringBuffer("DbMappingSpecification: ");
		sb.append(this.tableMappingPair.getSourceName() + " --> " + this.tableMappingPair.getTargetName());
		ColumnMappingPair[] colMapArray = new ColumnMappingPair[columnMappingSpecifications.size()];
		colMapArray = this.columnMappingSpecifications.toArray(colMapArray);
		for(ColumnMappingPair cmp: colMapArray) {
			String keyString = " ";
			if(cmp.isKeyField()) keyString = "key";
			sb.append("\n\t" + cmp.getSourceName() + " >>> " + cmp.getTargetName() + " >>> " + keyString);
		}
		return sb.toString();
	}

	public TableMappingPair getTableMappingPair() {
		return tableMappingPair;
	}

	public ColumnMappingPair[] getColumnMappingPairArray() {
		ColumnMappingPair[] cmpArray = new ColumnMappingPair[columnMappingSpecifications.size()];
		return columnMappingSpecifications.toArray(cmpArray);
	}
	@Override
	public int compareTo(DbMappingSpecification other) {
		return this.tableMappingPair.compareTo(other.tableMappingPair);
	}
	
	public SourceSet getSourceSet() {
		return SourceSet.getInstance(this);
	}
	
	public TargetSet getTargetSet() {
		return TargetSet.getInstance(this);
	}
	
	public  ColumnMappingPair[] getKeyColumnMappingPairArray() {
		Vector<ColumnMappingPair> v = new Vector<ColumnMappingPair>();
		ColumnMappingPair[] cmps = getColumnMappingPairArray();
		for(ColumnMappingPair cmp : cmps) {
	      if(cmp.isKeyField()) {
	    	  v.add(cmp);
	    	  if(Noisy) System.out.println("DbMappingSpecification.getKeyColumnMappingPairArray() - found key col: " + cmp);
	      }
		}
		ColumnMappingPair[] keyColsOnly = new ColumnMappingPair[v.size()];
		return v.toArray(keyColsOnly);
	} 
	public  int[] getKeyColumnMappingPairNdx() {
		ColumnMappingPair[] cmps = getColumnMappingPairArray();
		int count = 0;
		//  count the key fields
		for(int i = 0; i < cmps.length;i++) {
	      if(cmps[i].isKeyField()) count++;
		}
		int[] ndxes = new int[count];
		int ndxesNdx = 0;
		for(int i = 0; i < cmps.length;i++) {
		  if(cmps[i].isKeyField()) 
			  ndxes[ndxesNdx++] = i;
		}
		return ndxes;
	}
	public String[] getTargetKeyColumnNames() {
		ColumnMappingPair[] keyColumnMappingPairs = getKeyColumnMappingPairArray();
		if(Noisy) {
			if ( keyColumnMappingPairs == null) {
			  System.out.println("DbMappingSpecification.getTargetKeyColumnNames() - columnMappingPairs is NULL");
			}
			else {
				System.out.println("DbMappingSpecification.getTargetKeyColumnNames() keyColumnMappingPairs is length: " + keyColumnMappingPairs.length);
			}
				
		}
		String[] targetKeyColumnNames = new String[keyColumnMappingPairs.length];
		
		for(int i = 0 ; i < keyColumnMappingPairs.length;i++) {
			ColumnMappingPair cmp = keyColumnMappingPairs[i];
			targetKeyColumnNames[i] = cmp.getTargetName();
			if(Noisy) System.out.println("DbMappingSpecification.getTargetKeyColumnNames() - found key col: " + cmp.getTargetName());
		}
		return targetKeyColumnNames;
	}
	
	public String[] getSourceKeyColumnNames() {
		ColumnMappingPair[] keyPairs = getKeyColumnMappingPairArray();
		String[] sourceKeyColumnNames = new String[keyPairs.length];
		int i = 0;
		for(ColumnMappingPair cmp : keyPairs) {
			sourceKeyColumnNames[i++] = cmp.getSourceName();
		}
		return sourceKeyColumnNames;
	}
	public boolean isSourceKeyColumn(String columnName) {
		String[] cns = this.getSourceKeyColumnNames();
		for(String cName : cns) {
			if(columnName.equals(cName)) return true;
		}
		return false;
	}
	
	public boolean isTargetKeyColumn(String columnName) {
		String[] cns = this.getTargetKeyColumnNames();
		for(String cName : cns) {
			if(columnName.equals(cName)) return true;
		}
		return false;
	}
	
	public boolean isKeyPair(String sourceColName, String targetColName) {
		MappingPair temp = new MappingPair(sourceColName,targetColName);
		ColumnMappingPair[] pairs = this.getColumnMappingPairArray();
		for(ColumnMappingPair  cmp : pairs) {
			if(temp.equals(cmp)) return true;
		}
		return false;
	}
}
