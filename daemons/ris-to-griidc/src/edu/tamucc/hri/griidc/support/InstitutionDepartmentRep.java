package edu.tamucc.hri.griidc.support;

import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;

/**
 * an alternate representation of the Institution and it's departments. This is
 * subordinate to the DB representation and is only used as a convenience for
 * remembering connections
 * 
 * @author jvh
 * 
 */
public class InstitutionDepartmentRep implements
		Comparable<InstitutionDepartmentRep> {

	private Integer institutionNumber = -1;
	private SortedSet<DepartmentPeopleRep> department = Collections
			.synchronizedSortedSet(new TreeSet<DepartmentPeopleRep>());

	/**
	 * make a new InstitutionDepartmentRep with the institution number. The
	 * department collection will be empty initially
	 * 
	 * @param intsNumber
	 */
	public InstitutionDepartmentRep(int intsNumber) {
		this.institutionNumber = intsNumber;
	}

	/**
	 * add a department to this institution
	 * 
	 * @param dptNumber
	 * @return
	 */
	public DepartmentPeopleRep addDepartment(int dptNumber) {
		DepartmentPeopleRep dptPepRep = new DepartmentPeopleRep(dptNumber);
		this.department.add(dptPepRep);
		return dptPepRep;
	}

	// mutator
	public synchronized boolean containsDepartment(int deptId) {
		try {
			this.findDepartment(deptId);
		} catch (NoRecordFoundException e) {
			return false;
		}
		return true;
	}

	/**
	 * find the department number within the collection of this Institution. If
	 * located return the DepartmentPeopleRep object. Null otherwise.
	 * 
	 * @param dptNumber
	 * @return
	 */
	public DepartmentPeopleRep findDepartment(int dptNumber)
			throws NoRecordFoundException {
		Iterator<DepartmentPeopleRep> it = department.iterator();
		DepartmentPeopleRep dpr = null;
		while (it.hasNext()) {
			dpr = it.next();
			if (dpr.getDepartmentId() == dptNumber)
				return dpr;
		}
		throw new NoRecordFoundException("No Department " + dptNumber
				+ " found in Institution " + this.institutionNumber);
	}

	public int getInstitutionNumber() {
		return institutionNumber.intValue();
	}

	public SortedSet<DepartmentPeopleRep> getDepartmentSet() {
		return department;
	}
	
	public int getDepartmentSize() {
		return this.getDepartmentSet().size();
	}

	@Override
	public int compareTo(InstitutionDepartmentRep other) {
		return this.institutionNumber.compareTo(other.institutionNumber);
	}

	@Override
	public int hashCode() {
		final int prime = 31;
		int result = 1;
		result = prime
				* result
				+ ((institutionNumber == null) ? 0 : institutionNumber
						.hashCode());
		return result;
	}

	@Override
	public boolean equals(Object obj) {
		if (this == obj)
			return true;
		if (obj == null)
			return false;
		if (getClass() != obj.getClass())
			return false;
		InstitutionDepartmentRep other = (InstitutionDepartmentRep) obj;
		if (institutionNumber == null) {
			if (other.institutionNumber != null)
				return false;
		} else if (!institutionNumber.equals(other.institutionNumber))
			return false;
		return true;
	}

}
