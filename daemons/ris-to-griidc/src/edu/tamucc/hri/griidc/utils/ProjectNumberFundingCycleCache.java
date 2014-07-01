package edu.tamucc.hri.griidc.utils;

import java.util.Collections;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.SortedMap;
import java.util.TreeMap;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;

public class ProjectNumberFundingCycleCache {

	public ProjectNumberFundingCycleCache() {
		// TODO Auto-generated constructor stub
	}
	
	private SortedMap<Integer, String> cacheMap = Collections
			.synchronizedSortedMap(new TreeMap<Integer, String>());

	

	/**
	 * cache a value with a key
	 * 
	 * @param projectNumber
	 * @param fundingCycle
	 */
	public String setValue(int griidcPprojectNumber_risFundId, String fundingCycle) {
		String oldV = this.cacheMap.put(griidcPprojectNumber_risFundId,fundingCycle);
		return oldV;
	}

	/**
	 * given the key return the value
	 * 
	 * @param key
	 * @return
	 */

	public String getValue(int projectNumber) throws NoRecordFoundException {
		String value = this.cacheMap.get(projectNumber);
		if (value == null)
			throw new NoRecordFoundException(" projectNumber is " + projectNumber);
		return value;
	}

	public int size() {
		return this.cacheMap.size();
	}
	
	public String toString() {
		int n = size();
		Integer[] keys = new Integer[n];
		keys = this.cacheMap.keySet().toArray(keys);
		String[] values = new String[n];
		values = this.cacheMap.values().toArray(values);
		StringBuffer sb = new StringBuffer();
		sb.append("Project Id / Funding Cycle map - ");
		for(int i = 0; i < keys.length;i++) {
			if(i > 0)  sb.append(", ");
			sb.append(keys[i] + ":" + values[i]);
		}
		return sb.toString();
	}

}
