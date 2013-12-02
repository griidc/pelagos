package edu.tamucc.hri.griidc.mapping.specs;

public class SourceSet extends TableAndColumnSet {

	private SourceSet(String tName, String[] colNames) {
		super(tName, colNames);
	}
	public static SourceSet getInstance(DbMappingSpecification dms) {
		String tableName = dms.getTableMappingPair().getSourceName();  // SOURCE TABLE NAME
		ColumnMappingPair[] colMapPair = dms.getColumnMappingPairArray();
		String[] columnNames = new String[colMapPair.length];
		int ndx = 0;
		for (ColumnMappingPair cmp : colMapPair) {
			columnNames[ndx++] = cmp.getSourceName();   // SOURCE COL NAME 
		}
		return new SourceSet(tableName, columnNames);
	}
}
