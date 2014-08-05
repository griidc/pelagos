package edu.tamucc.hri.griidc.utils;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.utils.IntIntCache.IntegerPair;

public class PeoplePersonMap extends IntIntCache {

	private static PeoplePersonMap instance = null;

	private static int NO_PERSON = -1;
	public static PeoplePersonMap getInstance() {
		if (PeoplePersonMap.instance == null) {
			PeoplePersonMap.instance = new PeoplePersonMap();
		}
		return PeoplePersonMap.instance;
	}

	private PeoplePersonMap() {
		super();
	}

	public int getRisPeopleId(int griidcPersonNumber)
			throws NoRecordFoundException {
		return this.getKey(griidcPersonNumber);
	}

	public int getGriidcPersonNumber(int risPeopleId)
			throws NoRecordFoundException {
		return this.getValue(risPeopleId);
	}

	public void put(int risPeopleId, int griidcPersonNumber) {
		this.cacheValue(risPeopleId, griidcPersonNumber);
	}

	public void put(int risPeopleId) {
		this.cacheValue(risPeopleId, NO_PERSON);
	}
	public String toString() {
		return getReportHeader() + this.size() + " elements: "
				+ super.toString();
	}
	public String getReportHeader() {
		return "RIS People ID -> GRIIDC Person Number: ";
	}
	
	@Override
	public String columnerToString() {
		IntegerPair[] ip = this.toIntArray();
		StringBuffer sb = new StringBuffer(getReportHeader());
		String format = "%n%6d  %6s";
		for(int i = 0; i < ip.length;i++) {
			String value = "" + ip[i].getValue();
			if(ip[i].getValue() == NO_PERSON)
				value = "No matching Person";
			sb.append(String.format(format,ip[i].getKey(),value));
		}
		return sb.toString();
	}
	@Override
	public void throwNoValueFoundException(int targetKey)
			throws NoRecordFoundException {
		throw new NoRecordFoundException(
				"No GRIIDC Person Number found matching RIS People ID: "
						+ targetKey);
	}

	@Override
	public void throwNoKeyFoundException(int targetValue)
			throws NoRecordFoundException {
		throw new NoRecordFoundException(
				"No  RIS People ID record found matching GRIIDC Person Number: "
						+ targetValue);
	}
}
