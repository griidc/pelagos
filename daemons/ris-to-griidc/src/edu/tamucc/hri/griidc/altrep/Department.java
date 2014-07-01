package edu.tamucc.hri.griidc.altrep;

import edu.tamucc.hri.griidc.utils.CompressedString;

/**
 * an alternate minimal representation of Department
 * in the GRIIDC and RIS databases
 * @author jvh
 *
 */
public class Department implements Comparable<Department>  {

	private CompressedString name = null;
	private Integer id = null;
	
	public Department() {
		
	}

	
	/**
	 * @param name
	 * @param id
	 */
	public Department(String sourceName, Integer id) {
		super();
		this.setName(sourceName);;
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
	public int compareTo(Department o) {
		int result = this.name.compareTo(o.name);
		if(result == 0) return this.id.compareTo(o.id);
		return result;
	}
	@Override
	public String toString() {
		return "Department [name=" + name.getOriginalString() + ", id=" + id + "]";
	}
	
}
