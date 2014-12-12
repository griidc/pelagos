package edu.tamucc.hri.griidc.altrep;

import java.util.Arrays;
import java.util.Collections;
import java.util.Comparator;
import java.util.HashMap;
import java.util.Map;

import edu.tamucc.hri.griidc.altrep.InstitutionCollection.InstitutionIdComparator;
import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.griidc.utils.CompressedString;

/**
 * an alternate minimal representation of Institution
 * in the GRIIDC and RIS databases
 * @author jvh
 *
 */
public class Institution implements Comparable<Institution> {
	
	private CompressedString name = null;
	private Integer id = null;
	
	private Department[]  departmentArray = null;
	private boolean collectionHasChanged = true;
	
	private Map<String, Department> departmentCacheMap = Collections
				.synchronizedMap(new HashMap<String, Department>());
		
	public Institution() {
	}
	/**
	 * @param name
	 * @param id
	 */
	public Institution(String sourceName, Integer id) {
		super();
		this.setName(sourceName);
		this.setId(id);
	}


	public String getCompressedName() {
		return name.getCompressedString();
	}
	
	public String getOriginalName() {
		return name.getOriginalString();
	}

	public void setName(String name) {
		this.name = new CompressedString(name);
	}

	public int getId() {
		return id.intValue();
	}

	public Integer getIntegerId() {
		return id; 
	}
	public void setId(int id) {
		this.id = Integer.valueOf(id);
	}
	
	@Override
	public int compareTo(Institution o) {
		int result = this.name.compareTo(o.name);
		if(result == 0) return this.id.compareTo(o.id);
		return result;
	}
	
	public Department addDepartment(Department d) {
		this.collectionHasChanged = true;
		return this.departmentCacheMap.put(d.getCompressedName(),d);
	}
	/**
	 * add a department by providing the department name and the id
	 * @param key
	 * @param id
	 * @return
	 */
	public Department addDepartment(String departmentName, int departmentId) {
		Department d = new Department(departmentName,departmentId);
		return this.addDepartment(d);
	}
	
	public boolean hasDepartment(String deparmentName) {
		Department d =this.getDepartment(deparmentName);
		if(d == null) return false;
		return true;
	}
	
	public Department getDepartment(String deparmentName) {
		String compressedName = CompressedString.compressString(deparmentName);
		Department d =this.departmentCacheMap.get(compressedName);
		return d;
	}
	
	/**
	 * find an Department identified by target Department.
	 * Search the collection. If one of the Department has
	 * an name matching the targetName, return the Department found.
	 * else return null
	 * @param targetName
	 * @return
	 * @throws NoRecordFoundException 
	 */
	public Department findDepartment(Department targetDepartment) throws NoRecordFoundException {
		String compressedTarget = targetDepartment.getCompressedName();
		Department[] ia = getDepartmentArray();
		for(Department dept : ia) {
			if(dept.getCompressedName().equals(compressedTarget))
				return dept;
		}
		throw new NoRecordFoundException("No matching Department found in  " + this.name + " for Department " + targetDepartment.getOriginalName() );
	}
	
	public class DepartmentIdComparator implements Comparator<Department> {
		@Override
		public int compare(Department dept1, Department dept2) {
			return dept1.getIntegerId().compareTo(dept2.getIntegerId());
		}
	}
	
	public Department[] getDepartmentArray() {
		if(this.departmentArray == null || collectionHasChanged) {
	        departmentArray = new Department[this.departmentCacheMap.size()];
	        departmentArray = this.departmentCacheMap.values().toArray(departmentArray);
	        Arrays.sort(departmentArray, new DepartmentIdComparator());
	        this.collectionHasChanged = false;
		}
	  return departmentArray;
	}
	@Override
	public String toString() {
		return "Institution [name=" + name.getOriginalString() + ", id=" + id + "compressed Name=" + name.getCompressedString() + "]";
	}
	
	
}
