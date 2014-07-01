package edu.tamucc.hri.griidc.rdbms;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Vector;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.pubs.RisPeoplePub;
import edu.tamucc.hri.griidc.pubs.RisProjPub;

public class RisGriidcRelationalDataStore implements
		RisGriidcDataStoreInterface {

	private static RdbmsConnection griidcDbConn = null;
	private static RdbmsConnection risDbConn = null;
	
	public RisGriidcRelationalDataStore() {
		// TODO Auto-generated constructor stub
	}

	@Override
	public int getGriidcPersonNumberForRisPeopleId(int risPeopleId)  throws NoRecordFoundException {
		RisPeopleGriidcPersonMap cache = RisPeopleGriidcPersonMap.getInstance();
		return cache.getPersonNumber(risPeopleId);
	}

	@Override
	public String getGriidcFundingCycleForProjectNumber(int griidcProjectNum) throws NoRecordFoundException {
		GriidcProjectFundingEnvelopeMap cache = GriidcProjectFundingEnvelopeMap.getInstance();
		return cache.getValue(griidcProjectNum);		
	}
	
	

	@Override
	public RisPeoplePub[] getRisPeoplePubs() throws SQLException {
		RdbmsConnection con = RdbmsConnectionFactory.getRisDbConnectionInstance();
		Vector<RisPeoplePub> rppV = new Vector<RisPeoplePub>();
		String query = "SELECT * FROM " + RdbmsConstants.RisPeoplePublicationTableName;
		RisPeoplePub rpp = null;
	    ResultSet rs = con.executeQueryResultSet(query);
	    while(rs.next()) {
	    	rpp =  new RisPeoplePub();
	    	rpp.setPeoplePubId(rs.getInt("PeoplePub_ID"));
	    	rpp.setProgramId(rs.getInt("Program_ID"));
	    	rpp.setPeopleId(rs.getInt("People_ID"));
	    	rpp.setPubSerial(rs.getInt("Pub_Serial"));
	    	rppV.add(rpp);
	    }
	    RisPeoplePub[] rppArray = new RisPeoplePub[rppV.size()];
	    rppArray = rppV.toArray(rppArray);
		return rppArray;
	}

	@Override
	public RisProjPub[] getRisProjPubs()  throws SQLException {
		RdbmsConnection con = RdbmsConnectionFactory.getRisDbConnectionInstance();
		Vector<RisProjPub> rppV = new Vector<RisProjPub>();
		String query = "SELECT * FROM " + RdbmsConstants.RisProjPublicationTableName;
		RisProjPub rpp = null;
	    ResultSet rs = con.executeQueryResultSet(query);
	    while(rs.next()) {
	    	rpp =  new RisProjPub();
	    	rpp.setProjPubId(rs.getInt("ProjPub_ID"));
	    	rpp.setProgramId(rs.getInt("Program_ID"));
	    	rpp.setPublicatonSerialNum(rs.getInt("Pub_Serial"));
	    	rppV.add(rpp);
	    }
	    RisProjPub[] rppArray = new RisProjPub[rppV.size()];
	    rppArray = rppV.toArray(rppArray);
		return rppArray;
	}
}
