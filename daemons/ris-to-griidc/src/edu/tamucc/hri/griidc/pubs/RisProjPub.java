package edu.tamucc.hri.griidc.pubs;

/**
 * an alternate representation of a row in the RIS ProjPublication table.
 * Note that the RIS ProjPublication table stores program id
 * @author jvh
 *
 */
public class RisProjPub  {

	private int projPubId = -1;
	private int programId = -1;
	private int publicatonSerialNum = -1;
	
	public RisProjPub() {	
	}
	
	/**
	 * @param projPubId
	 * @param programId
	 * @param publicatonSerialNum
	 */
	public RisProjPub(int projPubId, int programId, int publicatonSerialNum) {
		super();
		this.projPubId = projPubId;
		this.programId = programId;
		this.publicatonSerialNum = publicatonSerialNum;
	}

	public int getProgramId() {
		return programId;
	}
	
	public void setProgramId(int programId) {
		this.programId = programId;
	}
	
	public int getPublicatonSerialNum() {
		return publicatonSerialNum;
	}
	
	public void setPublicatonSerialNum(int publicatonSerialNum) {
		this.publicatonSerialNum = publicatonSerialNum;
	}

	public int getProjPubId() {
		return projPubId;
	}

	public void setProjPubId(int projPubId) {
		this.projPubId = projPubId;
	}
}
