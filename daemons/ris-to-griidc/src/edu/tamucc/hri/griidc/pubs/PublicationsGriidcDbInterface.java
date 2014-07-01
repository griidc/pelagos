package edu.tamucc.hri.griidc.pubs;

import java.sql.SQLException;

public interface PublicationsGriidcDbInterface {

	/**
	 * Using the information encapsulated in the Publication parm pub,
	 * write a new Publication database record or Modify an existing database record.
	 * If the information is a duplicate of an existing record do nothing.
	 * Return success or failure.
	 * @param pub
	 * @return
	 * @throws SQLException 
	 */
	public abstract void updateAllPublications(int[] allPubs);
	
	public abstract boolean updatePublication(Publication pub)
			throws SQLException;

	public abstract int getPubsNumbersRead();
	
	public abstract int getPubNumbersNotFoundInRefBase();
	
	public abstract int getPubsAdded();

	public abstract int getPubsModified();

	public abstract int getDuplicatePubs();
	
	public abstract int getPubsErrors();
	
	public abstract boolean updateProjectPublication()
			throws SQLException;
	
	public abstract int getProjectPubsRead();
	
	public abstract int getProjectPubsAdded();
	
	public abstract int getProjectPubsErrors();

	public abstract int getDuplicateProjectPubs();

	public abstract boolean updatePersonPublication()
			throws SQLException;
	
	public abstract int getPersonPubsRead();

	public abstract int getPersonPubsAdded();

	public abstract int getPersonPubsErrors();

	public abstract int getDuplicatePersonPubs();
	
	public abstract int getPubSerialIdsProccessed();
	
}