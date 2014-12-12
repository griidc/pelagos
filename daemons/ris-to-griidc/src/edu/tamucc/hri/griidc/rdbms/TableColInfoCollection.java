package edu.tamucc.hri.griidc.rdbms;

import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

public class TableColInfoCollection {

	private SortedSet<TableColInfo> tableColInfoSet =  Collections
			.synchronizedSortedSet(new TreeSet<TableColInfo>());
	
	public TableColInfoCollection() {
		
	}

	public TableColInfo addTableColInfo(TableColInfo tci) {
		this.tableColInfoSet.add(tci);
		return tci;
	}
	public TableColInfo getTableColInfo(String tableName) {
		
		TableColInfo temp = null;
		TableColInfo dci = new TableColInfo(tableName);
		Iterator<TableColInfo> it = this.getTableColInfoIterator();
		while(it.hasNext()) {
			temp = it.next();
			if(dci.equals(temp)) {
				return temp;
			}	
		}
		return null;
	}
	public Iterator<TableColInfo> getTableColInfoIterator() {
		return this.tableColInfoSet.iterator();
	}
	
	public TableColInfo[] getTableColInfoArray() {
		TableColInfo[] tciArray = new TableColInfo[this.tableColInfoSet.size()];
		tciArray = this.tableColInfoSet.toArray(tciArray);
		return tciArray;
	}

	@Override
	public String toString() {
		Iterator<TableColInfo> tcSet =  this.getTableColInfoIterator();
		TableColInfo temp;
		StringBuffer sb = new StringBuffer();
		while(tcSet.hasNext()) {
			temp = tcSet.next();
			sb.append("\n<><>><><><><><><><><><><><><><><><><><><>><><><><><><><><><><><><><><><><>");
			sb.append("\n" + temp.toString());
			
		}
		return sb.toString();
	}
	
	public TableColInfoCollection getDefaultValuesTableColInfoCollection() {
		TableColInfoCollection subSet = new TableColInfoCollection();
		Iterator<TableColInfo> tcSet =  this.getTableColInfoIterator();
		TableColInfo tempTci = null;
		while(tcSet.hasNext()) {
			TableColInfo tci = tcSet.next();
			if(tci.containsDefaultValueColumn()) {
				tempTci = tci.getDefaultValueTableColInfo();
				subSet.addTableColInfo(tempTci);
			}
		}
		return subSet;
	}
	
	
}
