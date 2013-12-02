package edu.tamucc.hri.griidc.mapping.specs;

public class TargetSet extends TableAndColumnSet {

	private TargetSet(String tableName, String[] columnNames) {
		super(tableName, columnNames);
	}

	public static TargetSet getInstance(DbMappingSpecification dms) {
		String tableName = dms.getTableMappingPair().getTargetName();  // TARGET TABLE NAME
		ColumnMappingPair[] colMapPair = dms.getColumnMappingPairArray();
		String[] columnNames = new String[colMapPair.length];
		int ndx = 0;
		for (ColumnMappingPair cmp : colMapPair) {
			columnNames[ndx++] = cmp.getTargetName();   // TARGET COL NAME 
		}
		return new TargetSet(tableName, columnNames);
	}
}
