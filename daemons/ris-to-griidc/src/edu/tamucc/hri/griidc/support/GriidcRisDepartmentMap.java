package edu.tamucc.hri.griidc.support;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.support.IntIntCache.IntegerPair;
import edu.tamucc.hri.rdbms.utils.RdbmsUtils;
/**
 * A collector to record the correspondence of
 * GRIIDC Department_Number that correspond to RIS Departments_ID
 * Think of RIS Departments ID as key and GRIIDC Department Number as value
 * @author jvh
 *
 */
public class GriidcRisDepartmentMap extends IntIntCache {

	private static GriidcRisDepartmentMap instance = null;
	
	public static GriidcRisDepartmentMap getInstance() {
		if(GriidcRisDepartmentMap.instance == null) {
			GriidcRisDepartmentMap.instance = new GriidcRisDepartmentMap();
		}
		return GriidcRisDepartmentMap.instance;
	}
	private GriidcRisDepartmentMap() {
		super();
	}
	
	public GriidcRisDepartmentMap initialize() {
		GriidcRisDepartmentMap.instance = null;
		return GriidcRisDepartmentMap.getInstance();
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
	public static void main(String[] args) {
		GriidcRisDepartmentMap m = GriidcRisDepartmentMap.getInstance();
		/***********
		int[] gNum = {1,2,3,5, 7,11,13,17};
		int[] rId =  {2,4,6,8,10,12,14,16};
		for(int i = 0; i < gNum.length;i++) {
			m.put(gNum[i],rId[i]);
		}
		/***
		System.out.println(m.toString());
		int griidcDeptNum = -1;
		for(int i = 0; i < rId.length;i++) {
			try {
				griidcDeptNum = m.getGriidcDepartmentNumber(rId[i]);
				System.out.println("For RIS DEPT ID : " + rId[i] + " found GRIIDC DEPT Num: " + griidcDeptNum);
			} catch (NoRecordFoundException e) {
				System.out.println("NoRecordFoundException: " + e.getMessage());
			}
		}
		***/
		m = RdbmsUtils.getGriidcRisDepartmentMap();
		System.out.println(m.columnerToString());
	}

}
