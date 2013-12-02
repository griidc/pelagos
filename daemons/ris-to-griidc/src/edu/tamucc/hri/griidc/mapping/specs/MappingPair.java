package edu.tamucc.hri.griidc.mapping.specs;

public class MappingPair implements Comparable<MappingPair> {

	private String sourceName = null;
	private String targetName = null;
	public MappingPair(final String sName, final String tName) {
		this.sourceName = sName;
		this.targetName = tName;
	}
	public String getSourceName() {
		return sourceName;
	}
	public String getTargetName() {
		return targetName;
	}
	@Override
	public int compareTo(MappingPair other) {
		int r = this.sourceName.compareTo(other.sourceName);
		if(r == 0) 
			return this.targetName.compareTo(other.targetName);
		return r;
	}
	@Override
	public int hashCode() {
		final int prime = 31;
		int result = 1;
		result = prime * result
				+ ((sourceName == null) ? 0 : sourceName.hashCode());
		result = prime * result
				+ ((targetName == null) ? 0 : targetName.hashCode());
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
		MappingPair other = (MappingPair) obj;
		if (sourceName == null) {
			if (other.sourceName != null)
				return false;
		} else if (!sourceName.equals(other.sourceName))
			return false;
		if (targetName == null) {
			if (other.targetName != null)
				return false;
		} else if (!targetName.equals(other.targetName))
			return false;
		return true;
	}
	@Override
	public String toString() {
		return "MappingPair sourceName=" + sourceName + ", targetName="
				+ targetName ;
	}
	
	
	
}
