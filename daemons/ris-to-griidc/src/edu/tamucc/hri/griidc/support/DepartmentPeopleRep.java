package edu.tamucc.hri.griidc.support;

import java.util.Collections;
import java.util.Iterator;
import java.util.SortedSet;
import java.util.TreeSet;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;

public class DepartmentPeopleRep implements Comparable<DepartmentPeopleRep>{

	private Integer departmentNumber = -1;
	private SortedSet<Integer> personSet = Collections.synchronizedSortedSet(new TreeSet<Integer>());
	
	public DepartmentPeopleRep(int deptId) {
		this.departmentNumber = deptId;
	}
	
	public Integer addPerson(int personNum) {
		Integer personNumber = new Integer(personNum);
		this.personSet.add(personNumber);
		return personNumber;
	}

	/**
	 * find the person number within the peopleCollection of this Department.
	 * If located return the DepartmentPeopleRep object.
	 * Null otherwise.
	 * @param dptNumber
	 * @return
	 */
	public int findPerson(int personNum) throws NoRecordFoundException {
		Iterator<Integer>  it = personSet.iterator();
		Integer personNumber = null;
		while(it.hasNext()) {
			personNumber = it.next();
			if(personNumber.intValue() == personNum)
				return personNumber.intValue();
		}
		throw new NoRecordFoundException("No Person " + personNum + " found in Department " + this.departmentNumber);
	}

	public Integer getDepartmentId() {
		return departmentNumber;
	}

	public SortedSet<Integer> getPeople() {
		return personSet;
	}

	public int getPeopleSize() {
		return this.getPeople().size();
	}
	@Override
	public int compareTo(DepartmentPeopleRep other) {
		return this.departmentNumber.compareTo(other.departmentNumber);
	}

	@Override
	public int hashCode() {
		final int prime = 31;
		int result = 1;
		result = prime * result
				+ ((departmentNumber == null) ? 0 : departmentNumber.hashCode());
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
		DepartmentPeopleRep other = (DepartmentPeopleRep) obj;
		if (departmentNumber == null) {
			if (other.departmentNumber != null)
				return false;
		} else if (!departmentNumber.equals(other.departmentNumber))
			return false;
		return true;
	}
}
