package edu.tamucc.hri.griidc.rdbms;

import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.utils.MiscUtils;

/**
 * RisPeopleGriidcPersonMap.getInstance() will return an object that correlates
 * RIS PeopleId to GRIIDC Person Number. These data are from the GRIIDC
 * GoMRIPerson-Department-RIS-PeopleID table.
 * 
 * @author jvh
 * 
 */
public class RisPeopleGriidcPersonMap extends IntIntDbCache {

	public static RisPeopleGriidcPersonMap instance = null;
	public static final String TableName = RdbmsConstants.GriidcPersonDepartmentRisPeopleIdTableName;

	public static RisPeopleGriidcPersonMap getInstance() {
		if (RisPeopleGriidcPersonMap.instance == null) {
			RisPeopleGriidcPersonMap.instance = new RisPeopleGriidcPersonMap();
		}
		return RisPeopleGriidcPersonMap.instance;
	}

	private RisPeopleGriidcPersonMap() {
		super(RisPeopleGriidcPersonMap.getDbConnection(), TableName,
				"RIS_People_ID", "Person_Number");
		if (isDeBug())
			System.out.println("RisPeopleGriidcPersonMap.constructor() size: "
					+ this.size());
	}

	public int getPersonNumber(int peopleId) throws NoRecordFoundException {
		if (isDeBug())
			System.out
					.println("RisPeopleGriidcPersonMap.getPersonNumber() looking for match to RIS people id: "
							+ peopleId);
		return this.getValue(peopleId);
	}

	public int getPeopleId(int personNumber) throws NoRecordFoundException {
		if (isDeBug())
			System.out
					.println("RisPeopleGriidcPersonMap.getPeopleId() looking for match to GRIIDC personNumber: "
							+ personNumber);
		return this.getKey(personNumber);
	}

	private static RdbmsConnection getDbConnection() {
		RdbmsConnection conn = null;
		try {
			conn = RdbmsConnectionFactory.getGriidcDbConnectionInstance();
		} catch (SQLException e) {
			MiscUtils.fatalError("RisPeopleGriidcPersonCache",
					"getDbConnection", e.getMessage());
		}
		return conn;
	}

	public static boolean DeBug = false;

	public static boolean isDeBug() {
		return DeBug;
	}

	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}

	public String toString() {
		StringBuffer sb = new StringBuffer();
		IntegerPair[] ipA = this.toIntArray();
		sb.append("\nRisPeopleGriidcPersonMap");
		sb.append("\n\tRIS Pep\tGRIIDC Person");
		for (IntegerPair ip : ipA) {
			sb.append("\n\t" + ip.getKey() + "\t" + ip.getValue());
		}
		return sb.toString();
	}

	public static void main(String[] args) {
			
		int[] pepIds = { 2764	,
				2765	,
				2766	,
				2769	,
				1698, 
				2771	,
				2776 };
		RisPeopleGriidcPersonMap.setDeBug(true);
		RisPeopleGriidcPersonMap map = RisPeopleGriidcPersonMap.getInstance();
		

		for (int pid : pepIds) {
			try {
				map.getPersonNumber(pid);

			} catch (NoRecordFoundException e) {
				System.out.println("Did not find matching person for RIS peopleId: " + pid);
			}
		}
	}

	@Override
	public void throwNoValueFoundException(int targetKey)
			throws NoRecordFoundException {
		throw new NoRecordFoundException("No GRIIDC Person Number found for RIS people ID " + targetKey);
	}

	@Override
	public void throwNoKeyFoundException(int targetValue)
			throws NoRecordFoundException {
		throw new NoRecordFoundException("No RIS people ID for GRIIDC Person Number " + targetValue);
	}

	@Override
	public String getReportHeader() {
		return "RisPeopleGriidcPersonMap report";
	}

}
