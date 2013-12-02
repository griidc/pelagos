package edu.tamucc.hri.griidc.mapping.intake;



public class DbMappingSource extends MappingStructure {
	
	public DbMappingSource() {
		super();
	}
	public DbMappingSource(String tableName, String columnName) {
		super(tableName,columnName);
	}

    
    public String getSourceTableName() { 
    	return this.getTableName();
    }
    public String getSourceColumnName() { 
    	return this.getColumnName();
    }
	@Override
	public boolean isTarget() {
		return false;
	}
	@Override
	public boolean isSource() {
		return true;
	}
}
