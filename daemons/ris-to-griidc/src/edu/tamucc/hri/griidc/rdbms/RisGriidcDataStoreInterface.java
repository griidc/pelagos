package edu.tamucc.hri.griidc.rdbms;

import java.sql.SQLException;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.pubs.Publication;
import edu.tamucc.hri.griidc.pubs.RisPeoplePub;
import edu.tamucc.hri.griidc.pubs.RisProjPub;

/**
 * An interface that implements the database access for 
 * Pubs and maybe RIS-To-GRIIDC 
 * @author jvh
 *
 */
public abstract interface RisGriidcDataStoreInterface {
	
	/**
	 * return the GRIIDC Person number that corresponds to RIS People ID.
	 * This is from the GRIIDC database table GoMRIPerson-Department-RIS_ID
	 * @param risPeopleId
	 * @return
	 */
	public int getGriidcPersonNumberForRisPeopleId(int risPeopleId)  throws NoRecordFoundException;
	/**
	 * return GRIIDC Funding Cycle Number associated with the GRIIDC Project
	 * @param griidcProjectNum - a GRIIDC Project Number
	 * @return
	 */
	public String getGriidcFundingCycleForProjectNumber(int griidcProjectNum)  throws NoRecordFoundException; 
	
	public RisPeoplePub[] getRisPeoplePubs()  throws SQLException;
	
	public RisProjPub[] getRisProjPubs() throws SQLException;
}
