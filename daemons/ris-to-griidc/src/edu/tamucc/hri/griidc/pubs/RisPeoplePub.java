package edu.tamucc.hri.griidc.pubs;

/**
 * A Alternate representation of a row in the RIS PeoplePublication table 
 * @author jvh
 *
 */
public class RisPeoplePub {

	private int peoplePubId = -1;
	private int programId = -1;
	private int peopleId = -1;
	private int pubSerial = -1;
	
	public RisPeoplePub() {
	}

	/**
	 * @param peoplePubId
	 * @param programId
	 * @param peopleId
	 * @param pubSerial
	 */
	public RisPeoplePub(int peoplePubId, int programId, int peopleId,
			int pubSerial) {
		super();
		this.peoplePubId = peoplePubId;
		this.programId = programId;
		this.peopleId = peopleId;
		this.pubSerial = pubSerial;
	}

	public int getPeoplePubId() {
		return peoplePubId;
	}

	public void setPeoplePubId(int peoplePubId) {
		this.peoplePubId = peoplePubId;
	}

	public int getProgramId() {
		return programId;
	}

	public void setProgramId(int programId) {
		this.programId = programId;
	}

	public int getPeopleId() {
		return peopleId;
	}

	public void setPeopleId(int peopleId) {
		this.peopleId = peopleId;
	}

	public int getPubSerial() {
		return pubSerial;
	}

	public void setPubSerial(int pubSerial) {
		this.pubSerial = pubSerial;
	}

}
