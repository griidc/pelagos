package edu.tamucc.hri.griidc.pubs;

/**
 * an alternate representation of one row in
 * GRIIDC Person-Publication table
 * @author jvh
 *
 */
public class GriidcPersonPublication {

	int personNumber = -1;
	int publicationNumber = -1;
	
	public GriidcPersonPublication() {
	}

	/**
	 * @param personNumber
	 * @param publicationNumber
	 */
	public GriidcPersonPublication(int personNumber, int publicationNumber) {
		super();
		this.personNumber = personNumber;
		this.publicationNumber = publicationNumber;
	}

	public int getPersonNumber() {
		return personNumber;
	}

	public void setPersonNumber(int personNumber) {
		this.personNumber = personNumber;
	}

	public int getPublicationNumber() {
		return publicationNumber;
	}

	public void setPublicationNumber(int publicationNumber) {
		this.publicationNumber = publicationNumber;
	}

}
