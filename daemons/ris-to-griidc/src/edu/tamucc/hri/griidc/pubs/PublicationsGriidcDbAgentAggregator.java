package edu.tamucc.hri.griidc.pubs;

import java.sql.SQLException;

import edu.tamucc.hri.griidc.rdbms.RdbmsConnection;
import edu.tamucc.hri.griidc.rdbms.RisGriidcDataStoreInterface;
import edu.tamucc.hri.griidc.rdbms.RisGriidcRelationalDataStore;

public class PublicationsGriidcDbAgentAggregator implements PublicationsGriidcDbInterface  {

	private PublicationDbAgent publicationAgent = new PublicationDbAgent();
	private PersonPublicationDbAgent personPublicationAgent = new PersonPublicationDbAgent();
	private ProjectPublicationDbAgent projectPublicationAgent = new ProjectPublicationDbAgent();
	
	private static RdbmsConnection griidcDbConn = null;
	
	private static boolean DeBug = false;
	
	private RisGriidcDataStoreInterface risDataStore = new RisGriidcRelationalDataStore();
	
	public PublicationsGriidcDbAgentAggregator() {
		// TODO Auto-generated constructor stub
	}
	

	public static boolean isDeBug() {
		return DeBug;
	}


	public static void setDeBug(boolean deBug) {
		DeBug = deBug;
	}


	private void debugOut(String msg) {
		if(PublicationsGriidcDbAgentAggregator.isDeBug()) {
			System.out.println("PublicationDbAgent." + msg);
		}
	}
	
	
	/* (non-Javadoc)
	 * @see edu.tamucc.hri.griidc.pubs.PublicationDbAgentInterface#updatePublication(edu.tamucc.hri.griidc.pubs.Publication)
	 */
	@Override
	public boolean updatePublication(Publication pub) throws SQLException {
		return publicationAgent.updatePublication(pub);
	}

	@Override
	public void updateAllPublications(int[] allPubs) {
		publicationAgent.updateAllPublications(allPubs);
	}

	@Override
	public int getPubsNumbersRead() {
		return this.publicationAgent.getPubSerialIdsProccessed();
	}

	@Override
	public int getPubNumbersNotFoundInRefBase() {
		return this.publicationAgent.getPubNumbersNotFoundInRefBase();
	}

	/* (non-Javadoc)
	 * @see edu.tamucc.hri.griidc.pubs.PublicationDbAgentInterface#getPubsAdded()
	 */
	@Override
	public int getPubsAdded() {
		return this.publicationAgent.getPubsAdded();
	}


	/* (non-Javadoc)
	 * @see edu.tamucc.hri.griidc.pubs.PublicationDbAgentInterface#getPubsModified()
	 */
	@Override
	public int getPubsModified() {
		return this.publicationAgent.getPubsModified();
	}


	/* (non-Javadoc)
	 * @see edu.tamucc.hri.griidc.pubs.PublicationDbAgentInterface#getDuplicatePubs()
	 */
	@Override
	public int getDuplicatePubs() {
		return this.publicationAgent.getDuplicatePubs();
	}

	@Override
	public int getPubSerialIdsProccessed() {
		return this.publicationAgent.getPubSerialIdsProccessed();
	}

	@Override
	public int getPubsErrors() {
		return this.publicationAgent.getPubsErrors();
	}

//   Person Pubs functions
	
	@Override
	public int getPersonPubsRead() {
		return personPublicationAgent.getPersonPubsRead();
	}

	@Override
	public int getPersonPubsAdded() {
		return personPublicationAgent.getRecordsAdded();
	}

	@Override
	public int getDuplicatePersonPubs() {
		return personPublicationAgent.getDuplicateRecords();
	}

	@Override
	public int getPersonPubsErrors() {
		return personPublicationAgent.getErrors();
	}
	
	
	//  Project Publication functions

	@Override
	public int getProjectPubsRead() {
		return this.projectPublicationAgent.getProjectPubsRead();
	}

	@Override
	public int getProjectPubsAdded() {
		return this.projectPublicationAgent.getRecordsAdded();
	}

	@Override
	public int getDuplicateProjectPubs() {
		return this.projectPublicationAgent.getDuplicateRecords();
	}

	@Override
	public int getProjectPubsErrors() {
		return projectPublicationAgent.getErrors();
	}

	@Override
	public boolean updateProjectPublication()  throws SQLException {
		RisProjPub[] projPubs = risDataStore.getRisProjPubs();
		debugMessage("PublicationsGriidcDbAgentAggregator.updateProjectPublication() " + projPubs.length + " projPubs");
		projectPublicationAgent.updateGriidcProjectPublication(projPubs);
		return false;
	}

	@Override
	public boolean updatePersonPublication()  throws SQLException {
		RisPeoplePub[] peoplePubs = risDataStore.getRisPeoplePubs();
		debugMessage("PublicationsGriidcDbAgentAggregator.updatePersonPublication() " + peoplePubs.length + " peoplePubs");
		personPublicationAgent.updateGriidcPersonPublication(peoplePubs);
		return false;
	}

	private void debugMessage(String msg) {
		if(isDeBug()) System.out.println(msg);
	}
}
