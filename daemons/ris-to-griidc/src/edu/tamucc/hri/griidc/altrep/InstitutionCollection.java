package edu.tamucc.hri.griidc.altrep;

import java.util.Arrays;
import java.util.Collection;
import java.util.Collections;
import java.util.Comparator;
import java.util.HashMap;
import java.util.Map;
import java.util.Map.Entry;
import java.util.Set;

import edu.tamucc.hri.griidc.exception.NoRecordFoundException;
import edu.tamucc.hri.rdbms.utils.CompressedString;

/**
 * a way to collect allthe Institutions in one of the databases (GRIIDC RIS)
 * @author jvh
 *
 */
public class InstitutionCollection {

	
	private Map<String, Institution> cacheMap = Collections
			.synchronizedMap(new HashMap<String, Institution>());
	
	private String name = null;  // the name of this collection 
	
	private Institution[]  instutionArray = null;
	private boolean collectionHasChanged = true;
	
	public static boolean Debug = false;

	public InstitutionCollection(String collectionName) {
	  this.name = collectionName;
	}

	public String getName() {
		return name;
	}

	public Institution addInstitution(Institution d) {
		this.collectionHasChanged = true;
		return this.cacheMap.put(d.getCompressedName(),d);
	}
	/**
	 * add a institution by providing the institution name and the id
	 * @param key
	 * @param id
	 * @return
	 */
	public Institution addInstitution(String institutionName, int institutionId) {
		Institution d = new Institution(institutionName,institutionId);
		return this.addInstitution(d);
	}
	
	public boolean hasInstitution(String deparmentName) {
		Institution d =this.getInstitution(deparmentName);
		if(d == null) return false;
		return true;
	}
	
	public Institution getInstitution(String deparmentName) {
		String compressedName =  CompressedString.compressString(deparmentName);
		Institution d =this.cacheMap.get(compressedName);
		return d;
	}
	
	/**
	 * find an Institution identified by targetId.
	 * Search the collection. If one of the Institution has
	 * an id matching the targetId, return the Institution found.
	 * else return null
	 * @param targetId
	 * @return
	 */
	public Institution findInstitution(int targetId) throws NoRecordFoundException {
		if(isDebug()) {
			System.out.println("InstitutionCollection.findInstitution() looking for id  " + targetId);
		}Institution[] ia = this.getInstitutionArray();;
		for(Institution ins : ia) {
			if(isDebug()) {
				System.out.println("\tCompare target " +targetId + " to " + ins.getId());
			}
			if(ins.getId() == targetId)
				return ins;
		}
		throw new NoRecordFoundException("In Institution collection " + this.name + " No matching Instution found in  " + this.name + " for Institution id  " + targetId);
	}
	
	/**
	 * find an Institution identified by targetName.
	 * Search the collection. If one of the Institution has
	 * an name matching the targetName, return the Institution found.
	 * else return null
	 * @param targetName
	 * @return
	 * @throws NoRecordFoundException 
	 */
	public Institution findInstitution(Institution targetInstitution) throws NoRecordFoundException {
		String compressedTarget = targetInstitution.getCompressedName();
		if(isDebug()) {
			System.out.println("InstitutionCollection.findInstitution() looking for " + targetInstitution.toString());
		}
			
		Institution[] ia = getInstitutionArray();
		for(Institution ins : ia) {
			if(isDebug()) {
				System.out.println("\tCompare target " + targetInstitution.getCompressedName() + " to " + ins.getCompressedName());
			}
			if(ins.getCompressedName().equals(compressedTarget)) {
				if(isDebug())  System.out.println("\t<><><> found Institution " + ins.toString());
				return ins;
			}
		}
		if(isDebug())  System.out.println("\t(o)(o) DID not find Institution");
		throw new NoRecordFoundException("In Institution collection " + this.name + " No matching Instution found in  " + this.name + " for Institution " + targetInstitution.getOriginalName() );
	}
	
	public int size() {
		return this.cacheMap.size();
	}

	@Override
	public String toString() {
		return "InstitutionCollection [name=" + name + "] collection size=" + this.cacheMap.size();
	}
	
	public String report() {
		StringBuffer sb = new StringBuffer("\n" + this.toString());
		Institution[] ia = this.getInstitutionArray();
		for(Institution inst : ia) {
			sb.append("\n\tinst: " + inst.getId() + ":" + inst.getOriginalName());
			Department[] da = inst.getDepartmentArray();
			for(Department d: da) {
				sb.append("\n\t\tDept: " + d.getId() + " - " + d.getOriginalName());
			}
		}
		return sb.toString();
	}
	public class InstitutionIdComparator implements Comparator<Institution> {

		@Override
		public int compare(Institution inst1, Institution inst2) {
			return inst1.getIntegerId().compareTo(inst2.getIntegerId());
		}
		
	}
	public Institution[] getInstitutionArray() {
		if(this.instutionArray == null || collectionHasChanged) {
	        instutionArray = new Institution[this.cacheMap.size()];
	        instutionArray = this.cacheMap.values().toArray(instutionArray);
	        Arrays.sort(instutionArray, new InstitutionIdComparator());
	        this.collectionHasChanged = false;
		}
	  return instutionArray;
	}

	public static boolean isDebug() {
		return Debug;
	}

	public static void setDebug(boolean debug) {
		Debug = debug;
	}
	

}
