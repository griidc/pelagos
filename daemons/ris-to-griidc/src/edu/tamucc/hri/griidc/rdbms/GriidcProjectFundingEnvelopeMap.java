package edu.tamucc.hri.griidc.rdbms;

import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.utils.MiscUtils;


/**
 * GriidcProjectFundingEnvelopeCache provides a mapping between GRIIDC Project
 * and GRIIDC FundingEnveleope. Call getInstance() to instantiate and populate
 * the object.
 * 
 * It can then return the funding envelope for a given project number
 * @author jvh
 *
 */

public class GriidcProjectFundingEnvelopeMap extends IntStringDbCache {

	public static GriidcProjectFundingEnvelopeMap instance = null;
	
	public static GriidcProjectFundingEnvelopeMap getInstance()  {
		if(GriidcProjectFundingEnvelopeMap.instance == null) {
			GriidcProjectFundingEnvelopeMap.instance = new GriidcProjectFundingEnvelopeMap();
		}
		return GriidcProjectFundingEnvelopeMap.instance;
	}
	private GriidcProjectFundingEnvelopeMap()  {

		super(GriidcProjectFundingEnvelopeMap.getDbConnection(), RdbmsConstants.GriidcProjectTableName, "Project_Number", "FundingEnvelope_Cycle");
	}

	public String getFundingEnvelopeNumber(int projectNumber)
			throws NoRecordFoundException {
		return this.getValue(projectNumber);
	}
	
	public static RdbmsConnection getDbConnection() {
		RdbmsConnection conn = null;
		try {
			conn = RdbmsConnectionFactory.getInstance().getGriidcDbConnectionInstance();
		} catch (SQLException e) {
			MiscUtils.fatalError("GriidcProjectFundingEnvelopeCache", "getDbConnection", e.getMessage());
		}
		return conn;
	}

}
