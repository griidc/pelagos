package edu.tamucc.hri.griidc.rdbms;

import java.sql.Date;

public class RisProgramStartEnd implements Comparable<RisProgramStartEnd> {

	private int fundSrcId = -1;
	private int programId = -1;
	private java.sql.Date startDate = null;
	private java.sql.Date endDate = null;
	
	public RisProgramStartEnd() {
		
	}

	

	/**
	 * @param fundSrcId
	 * @param programId
	 * @param start
	 * @param end
	 */
	public RisProgramStartEnd(int fundSrcId, int programId, Date start, Date end) {
		super();
		this.fundSrcId = fundSrcId;
		this.programId = programId;
		this.startDate = start;
		this.endDate = end;
	}

	public java.sql.Date getStartDate() {
		return startDate;
	}

	public void setStartDate(java.sql.Date start) {
		this.startDate = start;
	}

	public java.sql.Date getEndDate() {
		return endDate;
	}

	public void setEndDate(java.sql.Date end) {
		this.endDate = end;
	}

	public int getProgramId() {
		return programId;
	}

	public void setProgramId(int programId) {
		this.programId = programId;
	}

	public int getFundSrcId() {
		return fundSrcId;
	}

	public void setFundSrcId(int fundSrcId) {
		this.fundSrcId = fundSrcId;
	}



	@Override
	public String toString() {
		return "RisProgramStartEnd [fundSrcId=" + fundSrcId + ", programId="
				+ programId + ", start=" + startDate + ", end=" + endDate + "]";
	}

	public String toStringBrief() {
		return fundSrcId + "\t"
				+ programId + "\t" 
				+ startDate + "\t" + endDate ;
	}


	@Override
	public int compareTo(RisProgramStartEnd ref) {
		int status = this.fundSrcId - ref.fundSrcId;
		if(status != 0) return status;
		status = this.programId - ref.programId;
		if(status != 0) return status;
		return this.getStartDate().compareTo(ref.getStartDate());
	}

}
