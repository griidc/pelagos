package edu.tamucc.hri.griidc.pubs;

/**
 * and alternate representation of one row in the 
 * GRIIDC Project-Publication table
 * @author jvh
 *
 */
public class GriidcProjectPublication  {
	
	private int projectNumber = -1;
	private String fundingEnvelopeCycle = null;
	private int publicationNumber = -1;
	
	public GriidcProjectPublication() {
		super();
	}

	

	/**
	 * @param projectNumber
	 * @param fundingEnvelopeCycle
	 * @param publicationNumber
	 */
	public GriidcProjectPublication(int projectNumber,
			String fundingEnvelopeCycle, int publicationNumber) {
		super();
		this.projectNumber = projectNumber;
		this.fundingEnvelopeCycle = fundingEnvelopeCycle;
		this.publicationNumber = publicationNumber;
	}



	public String getFundingEnvelopeCycle() {
		return fundingEnvelopeCycle;
	}

	public void setFundingEnvelopeCycle(String fundingEnvelopeCycle) {
		this.fundingEnvelopeCycle = fundingEnvelopeCycle;
	}

	public int getProjectNumber() {
		return projectNumber;
	}

	public void setProjectNumber(int projectNumber) {
		this.projectNumber = projectNumber;
	}

	public int getPublicationNumber() {
		return publicationNumber;
	}

	public void setPublicationNumber(int publicationNumber) {
		this.publicationNumber = publicationNumber;
	}
	
}
