package edu.tamucc.hri.griidc.rdbms;

import java.sql.Date;
import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.utils.MiscUtils;

public class RisFundSrcProgramsStartEndCollection {

	private java.sql.Date minStartDate = null;
	private java.sql.Date maxStartDate = null;
	private java.sql.Date minEndDate = null;
	private java.sql.Date maxEndDate = null;
	
	
	
	private SortedSet<RisProgramStartEnd> risProgramStartEnd = Collections
			.synchronizedSortedSet(new TreeSet<RisProgramStartEnd>());
	
	public RisFundSrcProgramsStartEndCollection() {
	}
	
	public boolean addRisProgramStartEnd(RisProgramStartEnd rpse) {
		return this.risProgramStartEnd.add(rpse);
	}
	public boolean addRisProgramStartEnd(int fundSrcId, int programId, Date start, Date end) {
		return this.addRisProgramStartEnd(new RisProgramStartEnd(fundSrcId, programId, start, end));
	}
	/**
	 * return the size of the RisProgramStartEnd collection
	 * @return
	 */
	public int size() {
		return this.risProgramStartEnd.size();
	}
	/**
	 * return true if there is at least one instance of RisProgramStartEnd
	 * in the collection that contains the fund src id.
	 * Return false if not.
	 * @param fundSrcId
	 * @return
	 */
	public boolean containsFundSrc(int fundSrcId) {
		RisProgramStartEnd temp = null;
		Iterator<RisProgramStartEnd> it = this.risProgramStartEnd.iterator();
		while(it.hasNext()) {
			temp = it.next();
			if(temp.getFundSrcId() == fundSrcId) return true;
		}
		return false;
	}
	/**
	 * return true if there is at least one instance of RisProgramStartEnd
	 * in the collection that contains both the fund src id and the program id.
	 * return false if not.
	 * @param fundSrcId
	 * @return
	 */
	public boolean containsFundSrcProgram(int fundSrcId,int programId) {
		RisProgramStartEnd temp = null;
		Iterator<RisProgramStartEnd> it = this.risProgramStartEnd.iterator();
		while(it.hasNext()) {
			temp = it.next();
			if(temp.getFundSrcId() == fundSrcId &&
				temp.getProgramId() == programId )return true;
		}
		return false;
	}
	/**
	 * if the RisProgramStartEnd collection contains objects
	 * with both the Fund Source ID and the Program ID return
	 * a RisProgramStartEnd in which the startDate is the earliest
	 * start date of the subset and the endDate is the latest end 
	 * date of the subset.
	 * Return null if the specified Fund Source ID and Program ID
	 * are not found in the collection.
	 * @return RisProgramStartEnd
	 */
	public RisProgramStartEnd getFundSourceProgramStartEndDate(int fundSrcId,int programId) {
		if(!this.containsFundSrcProgram(fundSrcId,programId))
			return null;
		RisProgramStartEnd temp = null;
		this.initializeMinMaxDates();
		RisProgramStartEnd returnPackage = new RisProgramStartEnd();
		returnPackage.setFundSrcId(fundSrcId);
		returnPackage.setProgramId(programId);
		Iterator<RisProgramStartEnd> it = this.risProgramStartEnd.iterator();
		while(it.hasNext()) {
			temp = it.next();
			if(temp.getFundSrcId() == fundSrcId && 
					temp.getProgramId() == programId) {
				setMinMaxDates(temp);
			}
		}
		returnPackage.setStartDate(this.minStartDate);
		returnPackage.setEndDate(this.maxEndDate);
		return returnPackage;
	}
	/**
	 * if the RisProgramStartEnd collection contains objects
	 * with the Fund Source ID return 
	 * a RisProgramStartEnd in which the startDate is the earliest
	 * start date of the subset and the endDate is the latest end 
	 * date of the subset.
	 * Return null if the specified Fund Source ID and Program ID
	 * are not found in the collection.
	 * @return RisProgramStartEnd
	 */
	public RisProgramStartEnd getFundSourceStartEndDate(int fundSrcId) {
		if(!this.containsFundSrc(fundSrcId))
			return null;
		RisProgramStartEnd temp = null;
		this.initializeMinMaxDates();
		RisProgramStartEnd returnPackage = new RisProgramStartEnd();
		returnPackage.setFundSrcId(fundSrcId);
		Iterator<RisProgramStartEnd> it = this.risProgramStartEnd.iterator();
		while(it.hasNext()) {
			temp = it.next();
			if(temp.getFundSrcId() == fundSrcId) {
				setMinMaxDates(temp);
			}
		}
		returnPackage.setStartDate(this.minStartDate);
		returnPackage.setEndDate(this.maxEndDate);
		return returnPackage;
	}
	
	private void setMinMaxDates(RisProgramStartEnd temp) {
		if(temp.getStartDate().compareTo(this.minStartDate) < 0) {
		    this.minStartDate = temp.getStartDate();
		}
		if(temp.getStartDate().compareTo(this.maxStartDate) > 0) {
		    this.maxStartDate = temp.getStartDate();
		}
		
		if(temp.getEndDate().compareTo(this.minEndDate) < 0) {
		    this.minEndDate = temp.getEndDate();
		}
		if(temp.getEndDate().compareTo(this.maxEndDate) > 0) {
		    this.maxEndDate = temp.getEndDate();
		}
	}
	private void initializeMinMaxDates() {
		this.maxStartDate = this.maxEndDate = MiscUtils.getMinDate();
		this.minStartDate = this.minEndDate = MiscUtils.getMaxDate();
		 
	}
	
	public String toString() {
		RisProgramStartEnd temp = null;
		StringBuffer sb = new StringBuffer();
		Iterator<RisProgramStartEnd> it = this.risProgramStartEnd.iterator();
		while(it.hasNext()) {
			temp = it.next();
			sb.append(temp.toString() + "\n");
		}
		return sb.toString();
	}
	public String toStringBrief() {
		RisProgramStartEnd temp = null;
		StringBuffer sb = new StringBuffer();
		Iterator<RisProgramStartEnd> it = this.risProgramStartEnd.iterator();
		while(it.hasNext()) {
			temp = it.next();
			sb.append(temp.toStringBrief() + "\n");
		}
		return sb.toString();
	}

}
