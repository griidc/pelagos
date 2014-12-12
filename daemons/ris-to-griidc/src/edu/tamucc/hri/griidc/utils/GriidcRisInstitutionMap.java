package edu.tamucc.hri.griidc.utils;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.rdbms.RdbmsUtils;

/**
 * A collector to record the correspondence of
 * GRIIDC Institution_Number that correspond to RIS Institutions_ID
 * Think of RIS Institutions ID as key and GRIIDC Institution Number as value
 * @author jvh
 *
 */
public class GriidcRisInstitutionMap extends IntIntCache {

	private static GriidcRisInstitutionMap instance = null;
	
	public static GriidcRisInstitutionMap getInstance() {
		if(GriidcRisInstitutionMap.instance == null) {
			GriidcRisInstitutionMap.instance = new GriidcRisInstitutionMap();
		}
		return GriidcRisInstitutionMap.instance;
	}
	private GriidcRisInstitutionMap() {
		super();
	}
	public int getRisInstitutionId(int griidcInstitutionNumber) throws NoRecordFoundException {
		return this.getKey(griidcInstitutionNumber);
	}
	public int getGriidcInstitutionNumber(int risInstitutionId) throws NoRecordFoundException {
		return this.getValue(risInstitutionId);
	}
	
	public void put(int risInstitutionId,int griidcInstitutionNumber) {
		this.cacheValue( risInstitutionId,griidcInstitutionNumber);
	}
	
	public String toString() {
		return getReportHeader() + this.size() + " elements: " + super.toString();
	}
	public String getReportHeader() {
		return "RIS Institutions ID to GRIIDC Institution Number: ";
	}
	
	@Override
	public void throwNoValueFoundException(int targetKey)
			throws NoRecordFoundException {
		throw new NoRecordFoundException("No GRIIDC Institution record found matching RIS Institutions ID: " + targetKey);
	}
	@Override
	public void throwNoKeyFoundException(int targetValue)
			throws NoRecordFoundException {
		throw new NoRecordFoundException("No  RIS Institutions  record found matching GRIIDC Institution: " + targetValue);
	}
	public static void main(String[] args) {
		GriidcRisInstitutionMap m = GriidcRisInstitutionMap.getInstance();
		/**
		int[] gNum = {1,2,3,5, 7,11,13,17};
		int[] rId =  {2,4,6,8,10,12,14,16};
		for(int i = 0; i < gNum.length;i++) {
			m.put(gNum[i],rId[i]);
		}
		System.out.println(m.toString());
		int griidcNum = -1;
		for(int i = 0; i < rId.length;i++) {
			try {
				griidcNum = m.getGriidcInstitutionNumber(rId[i]);
				System.out.println("For RIS INST ID : " + rId[i] + " found GRIIDC INST Num: " + griidcNum);
				
			} catch (NoRecordFoundException e) {
				System.out.println("NoRecordFoundException: " + e.getMessage());
			}
		}
		***/
		m = RdbmsUtils.getGriidcRisInstitutionMap();
		System.out.println(m.columnerToString());
	}
}
