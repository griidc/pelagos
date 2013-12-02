package edu.tamucc.hri.griidc.mapping.intake;


public class DbMappingTarget extends MappingStructure {
	
	public DbMappingTarget() {
		super();
	}
	public DbMappingTarget(String tableName, String columnName) {
		super(tableName,columnName);
	}

    
    public String getTargetTableName() { 
    	return this.getTableName();
    }
    public String getTargetColumnName() { 
    	return this.getColumnName();
    }
	@Override
	public boolean isTarget() {
		return true;
	}
	@Override
	public boolean isSource() {
		return false;
	}
}
