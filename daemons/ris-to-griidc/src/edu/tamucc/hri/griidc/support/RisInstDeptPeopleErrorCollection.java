package edu.tamucc.hri.griidc.support;

import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;

/**
 * A collection of RIS institutions, departments and people which form a
 * hierarchy. The only known use is as a RIS error collection aid.
 * 
 * All "contains" and "find" functions update the "current" 
 * data members.
 * 
 * 
 * @see InstitutionDepartmentRep
 * @see DepartmentPeopleRep
 * @author jvh
 * 
 */
public class RisInstDeptPeopleErrorCollection {
	private InstitutionDepartmentRep currentInstitutionDepartmentRep = null;
	private DepartmentPeopleRep currentDepartmentPeopleRep = null;
	private int currentPeopleId = -1;

	private SortedSet<InstitutionDepartmentRep> risInstDept = Collections
			.synchronizedSortedSet(new TreeSet<InstitutionDepartmentRep>());

	public RisInstDeptPeopleErrorCollection() {
		// TODO Auto-generated constructor stub
	}

	/**
	 * adds Institution to this set of Institutions
	 * sets current Institution
	 * @param institutionId
	 * @return
	 */
	public synchronized InstitutionDepartmentRep addInstitution(
			int institutionId) {

		InstitutionDepartmentRep temp;
		try {
			temp = this.findInstitution(institutionId);
			this.currentInstitutionDepartmentRep = temp;
			return temp;
		} catch (NoRecordFoundException e) {

			temp = new InstitutionDepartmentRep(
					institutionId);
			this.risInstDept.add(temp);
			this.currentInstitutionDepartmentRep = temp;
			return temp;
		}
	}

	/**
	 * Adds Institution if needed
	 * Sets current Department
	 * returns current DepartmentPeopleRep
	 * @param institutionId
	 * @param departmentId
	 * @return
	 */
	public synchronized DepartmentPeopleRep addDepartment(int institutionId,
			int departmentId) {
		// is it there already - return it
		if (this.containsDepartmentWithinInstitution(institutionId,
				departmentId))
			return this.currentDepartmentPeopleRep;

		if (!this.containsInstitution(institutionId)) {
			this.addInstitution(institutionId);
		}
		DepartmentPeopleRep tempD = this.currentInstitutionDepartmentRep.addDepartment(departmentId);
		this.currentDepartmentPeopleRep = tempD;
		return tempD;
	}

	/**
	 * Adds Institution and Department if needed
	 * Adds Person and return Person Id as Integer
	 * Sets current Person Id
	 * @param institutionId
	 * @param departmentId
	 * @param personId
	 * @return
	 */
	public synchronized Integer addPerson(int institutionId, int departmentId,
			int personId) {

		InstitutionDepartmentRep tempInst;
		// is it in the list already?
		if (this.containsPersonWithinDepartmentWithinInstitution(institutionId,
				departmentId, personId))
			return personId; // it's already stored
		// NO institution - add it
		if (!containsInstitution(institutionId)) {
			this.addInstitution(institutionId);
		}
		
		// institution found 
		// ..... but NO Department - add it
		if (!this.currentInstitutionDepartmentRep.containsDepartment(departmentId)) {
			this.addDepartment(institutionId, departmentId);
		}

		// institution exists - department exists

		return this.currentDepartmentPeopleRep.addPerson(personId);
	}

	/***
	 * returns true if the Institution code is found in the
	 * set of institutions. this. currentInstitutionRep is
	 * changed via the invocation of findInstitution.
	 * @param institutionId
	 * @return
	 */
	public synchronized boolean containsInstitution(int institutionId) {
		try {
			this.findInstitution(institutionId);
			return true;
		} catch (NoRecordFoundException e) {
			return false;
		}
	}

	/**
	 * returns true if the institutionId is in the set and
	 * the departmentId is within the set of Institution.
	 * If the Institution is not found or the Department is not found 
	 * within, return false.
	 * If Institution is in the list and Department is the Institution set
	 * If true this.currentDepartmentPeopleRep will have been set the findDepartmentWithinInstitution
	 * function.
	 * 
	 * @param institutionId
	 * @param departmentId
	 * @return
	 */
	public synchronized boolean containsDepartmentWithinInstitution(
			int institutionId, int departmentId) {
	
		try {
			this.findDepartmentWithinInstitution(
					institutionId, departmentId);
			return true;
		} catch (NoRecordFoundException e) {
			return false;
		}
		
	}

	// mutator
	public synchronized boolean containsPersonWithinDepartmentWithinInstitution(
			int institutionId, int departmentId, int personId) {
		try {
			this.findPersonWithinDepartmentWithinInstitution(
					institutionId, departmentId, personId);
			return true;
		} catch (NoRecordFoundException e) {
			return false;
		}
	}

	public InstitutionDepartmentRep findInstitution(int institutionId)
			throws NoRecordFoundException {
		Iterator<InstitutionDepartmentRep> it = risInstDept.iterator();
		InstitutionDepartmentRep temp = null;
		while (it.hasNext()) {
			temp = it.next();
			if (temp.getInstitutionNumber() == institutionId) {
				this.currentInstitutionDepartmentRep = temp;
				return this.currentInstitutionDepartmentRep;
			}
		}
		this.currentInstitutionDepartmentRep = null;
		throw new NoRecordFoundException("No Institution " + institutionId
				+ " found in Collection");
	}

	public DepartmentPeopleRep findDepartmentWithinInstitution(int institutionId,
			int departmentId) throws NoRecordFoundException {
		InstitutionDepartmentRep tempInst = null;
		tempInst = findInstitution(institutionId);
		this.currentDepartmentPeopleRep = tempInst.findDepartment(departmentId);
		return this.currentDepartmentPeopleRep;

	}

	public Integer findPersonWithinDepartmentWithinInstitution(int institutionId,
			int departmentId, int personId) throws NoRecordFoundException {
		InstitutionDepartmentRep tempInst = null;
		DepartmentPeopleRep tempDept = null;
		Integer tempPeople = null;
		tempInst = findInstitution(institutionId);
		tempDept = tempInst.findDepartment(departmentId);
		tempPeople = tempDept.findPerson(personId);
		this.currentPeopleId = tempPeople;
		return this.currentPeopleId;
	}

	public String toStringCurrent() {
		String tab = "\t";

		return tab
				+ "Inst: "
				+ this.getCurrentInstitutionDepartmentRep()
						.getInstitutionNumber() + tab + "Dept: "
				+ this.getCurrentDepartmentPeopleRep().getDepartmentId() + tab
				+ "People: " + this.getCurrentPeopleId();
	}

	public InstitutionDepartmentRep getCurrentInstitutionDepartmentRep() {
		return currentInstitutionDepartmentRep;
	}

	public DepartmentPeopleRep getCurrentDepartmentPeopleRep() {
		return currentDepartmentPeopleRep;
	}

	public int getCurrentPeopleId() {
		return currentPeopleId;
	}

	public String toString() {
		String tab = "\t\t";
		String newLine = "\n";
		StringBuffer sb = new StringBuffer();
		sb.append("RIS Institution, Department and People with errors" + newLine);
		sb.append(tab + "Inst" + tab + "Dept" + tab + "People" + newLine);
		InstitutionDepartmentRep instDept = null;
		DepartmentPeopleRep deptPeople = null;
		Integer pepId = null;
		
		int lastInstNum = -1;
		int lastDeptNum = -1;
		
		String di = " ";
		String dd = " ";
		String dp = " ";
		Iterator<InstitutionDepartmentRep> intstitutions = this.risInstDept
				.iterator();
		while (intstitutions.hasNext()) {
			di = dd = dp = " ";
			instDept = intstitutions.next();
			if(instDept.getInstitutionNumber() != lastInstNum) {
				lastInstNum = instDept.getInstitutionNumber();
				di = "" + lastInstNum;
			}
			if(instDept.getDepartmentSize() == 0) {
				sb.append(tab + di + tab + dd +  tab + ((pepId == null) ? "0" : pepId.intValue())
						+ newLine);
			}
			lastDeptNum = -1;
			Iterator<DepartmentPeopleRep> departments = instDept
					.getDepartmentSet().iterator();
			while (departments.hasNext()) {
				deptPeople = departments.next();
				if(deptPeople.getDepartmentId() != lastDeptNum) {
					lastDeptNum = deptPeople.getDepartmentId();
					dd = "" + lastDeptNum;
				}
				if(deptPeople.getPeopleSize() == 0) {
					sb.append(tab + di + tab + dd +  tab + dp
							+ newLine);
				}
				Iterator<Integer> peopleIds = deptPeople.getPeople().iterator();
				while (peopleIds.hasNext()) {
					pepId = peopleIds.next();
					dp =  "" + pepId.intValue();
					sb.append(tab + di + tab + dd +  tab + dp
							+ newLine);
					di = " ";
					dd = " ";
				}
			}
			sb.append(tab + "---------------------------------------\n");
		}
		return sb.toString();
	}

	public void reportToStdOut() {
		System.out.println(this.toString());
	}
}
