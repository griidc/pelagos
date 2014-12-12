package edu.tamucc.hri.griidc.rdbms;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.utils.IntIntCache;

/**
 * A collector to record the correspondence of
 * GRIIDC Department_Number that correspond to RIS Departments_ID
 * Think of RIS Departments ID as key and GRIIDC Department Number as value
 * @author jvh
 *
 */
public class GriidcRisDepartmentMap extends IntIntCache {

	public GriidcRisDepartmentMap() {
		super();
	}
	
	public int getRisDepartmentId(int griidcDepartmentNumber) throws NoRecordFoundException {
		return this.getKey(griidcDepartmentNumber);
	}
	public int getGriidcDepartmentNumber(int risDepartmentId) throws NoRecordFoundException {
		return this.getValue(risDepartmentId);
	}
	
	public void put(int risDepartmentId,int griidcDepartmentNumber) {
		this.cacheValue(risDepartmentId,griidcDepartmentNumber);
	}
	public String getReportHeader() {
		return "RIS Departments ID  maps to GRIIDC Department Number: ";
	}
	public String toString() {
		return getReportHeader() + this.size() + " elements: " + super.toString();
	}
	
	@Override
	public void throwNoValueFoundException(int targetKey)
			throws NoRecordFoundException {
		throw new NoRecordFoundException("No  GRIIDC Department value found matching RIS Departments: " + targetKey);
	}
	@Override
	public void throwNoKeyFoundException(int targetValue)
			throws NoRecordFoundException {
		throw new NoRecordFoundException("No RIS Departmentskey found matching GRIIDC Department: " + targetValue);
	}
}
