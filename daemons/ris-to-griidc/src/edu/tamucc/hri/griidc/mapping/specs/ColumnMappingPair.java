package edu.tamucc.hri.griidc.mapping.specs;

public class ColumnMappingPair extends MappingPair {

	private boolean keyField = false;
	
	public ColumnMappingPair(String sourceColumnName, String targetColumnName, boolean key) {
		super(sourceColumnName, targetColumnName);
		if(key) this.keyField = true;
	}
	public boolean isKeyField() {
		return keyField;
	}
	public void setKeyField(boolean keyField) {
		this.keyField = keyField;
	}
	@Override
	public String toString() {
		return super.toString() + " [keyField=" + keyField + "]";
	}

	
}
