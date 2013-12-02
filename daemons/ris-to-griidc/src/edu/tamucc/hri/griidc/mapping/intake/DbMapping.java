package edu.tamucc.hri.griidc.mapping.intake;



public class DbMapping implements Comparable<DbMapping> {

	private DbMappingSource source = null;
	private DbMappingTarget target = null;
	private boolean  key = false;  // indicates that this mapping is (or part of) the key 
	public DbMapping(DbMappingSource source, DbMappingTarget target) {
		super();
		this.source = source;
		this.target = target;
	}
	public DbMapping(String sourceTable, String sourceColumn, String targetTable,  String targetColumn, boolean key) {
		super();
		this.source = new DbMappingSource(sourceTable, sourceColumn);
		this.target = new DbMappingTarget(targetTable, targetColumn);
		this.setKey(key);
	}
	
	public boolean isKey() {
		return key;
	}
	public void setKey(boolean key) {
		this.key = key;
	}
	public DbMappingSource getSource() {
		return source;
	}
	public void setSource(DbMappingSource source) {
		this.source = source;
	}
	public DbMappingTarget getTarget() {
		return target;
	}
	public void setTarget(DbMappingTarget target) {
		this.target = target;
	}
	
	public String getSourceTableName() {
		return this.source.getTableName();
	}
	
	public String getSourceColumnName() {
		return this.source.getColumnName();
	}
	public String getTargetTableName() {
		return this.target.getTableName();
	}
	public String getTargetColumnName() {
		return this.target.getColumnName();
	}
	@Override
	public String toString() {
		return this.source.toString() + "\n" + this.target.toString();
	}
	@Override
	public int compareTo(DbMapping o) {
		int r = this.source.compareTo(o.source);
		if(r != 0)
			return r;
		return this.target.compareTo(o.target);
	}
}

