package edu.tamucc.hri.rdbms.utils;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Iterator;
import java.util.Vector;
import java.util.regex.PatternSyntaxException;

/**
 * This class provides utility in converting the result set from a database
 * Query into a two dimensional array of string.
 * 
 * Each row of the table is represented by a String[] (array of String). The
 * entire table of results then are represented as an array of String[] or
 * String[][].
 * 
 * In order to transmit this representation in the Web Services world, it can be
 * converted to a String[] in which each string in the array represents one row
 * in the table where the column values are delimited by a unique delimiter
 * string which could be a single character.
 * 
 * The first three "rows" (the first three Strings in the two dimensional
 * representation) describe the delimiter, the column names and the column
 * types.
 * 
 * So.... String[0] is the delimiter string i.e. "|" or "Xyzzy" String[1] is
 * colName1|colName2|colName3 ... String[2] is typeOfCol1|typeOfCol2|typeOfCol3
 * ... String[3] starts real data
 * 
 * @author jvh
 * 
 */
public class StringArrayResultSet {

	private String delimiterString = null;
	private String[] columnNames = null;
	private String[] columnTypes = null;

	public static final String DefaultDelimiter = "XyZZy";

	private Vector<String> dataRows = null;

	public StringArrayResultSet() {
		super();
		dataRows = new Vector<String>();
	}

	public StringArrayResultSet(String delimiterString, String[] columnNames,
			String[] columnTypes) {
		this();
		this.delimiterString = delimiterString;
		this.columnNames = columnNames;
		this.columnTypes = columnTypes;

		checkDelmiter();
	}

	public StringArrayResultSet(String[] completeData) {
		this();
		int i = 0;
		this.delimiterString = completeData[i++];
		this.columnNames = StringArrayResultSet.delimitedStringToArray(
				this.delimiterString, completeData[i++]);
		this.columnTypes = StringArrayResultSet.delimitedStringToArray(
				this.delimiterString, completeData[i++]);
		for (; i < completeData.length; i++) {
			dataRows.add(completeData[i]);
		}
	}

	private void checkDelmiter() {
		if (this.delimiterString != null)
			if (this.delimiterString.contains("|")) {
				StringBuffer sb = new StringBuffer();
				char[] chars = this.delimiterString.toCharArray();
				for (int i = 0; i < chars.length; i++) {
					if (chars[i] == '|')
						sb.append(StringArrayResultSet.DefaultDelimiter);
					else
						sb.append(chars[i]);
				}
				this.delimiterString = sb.toString();
			}
	}

	public void setDelimiter(String delimiter) {
		this.delimiterString = delimiter;
		checkDelmiter();
	}

	public static String getDefaultDelimiter() {
		return StringArrayResultSet.DefaultDelimiter;
	}

	public String getDelimiter() {
		return this.delimiterString;
	}

	public void setColumnNames(String[] colNames) {
		this.columnNames = colNames;
	}

	public String[] getColumnNames() {
		return this.columnNames;
	}

	public String[] getColumnTypes() {
		return columnTypes;
	}

	public void setColumnTypes(String[] colTypes) {
		this.columnTypes = colTypes;
	}

	/**
	 * the data is added by taking a String[] in which each element in the array
	 * represents the value from a column in the row.
	 * 
	 * @param oneRow
	 * @return
	 */
	public void addRow(String[] oneRow) {
		this.dataRows.add(arrayToDelimitedString(oneRow));
		return;
	}

	/**
	 * return the delimiter, the column names, column types and the entire data
	 * table. The delimiter String is in table[0]. The column names are in
	 * table[1] delimited by delimter The column type names are in table[2]
	 * delimited by delimiter The data is represented by table[3] ... table[n]
	 * as Strings delmited by delimiter
	 */
	public String[] getAllAsTable() {
		/**
		 * allocate an array big enough for all the data plus the three rows
		 * containing 1. the delimiter string 2. the column name info 3. the
		 * column type info
		 * 
		 * table[0] = delimiter table[1] = column names delimited by delimiter
		 * table[2] = column type names delimited by delimiter table[3...n] =
		 * data values as strings delimited by delimiter
		 */

		int size = this.dataRows.size() + 3;
		String[] table = new String[size];
		int ndx = 0;
		table[ndx++] = this.getDelimiter(); // load the delimiter
		table[ndx++] = this.arrayToDelimitedString(this.getColumnNames()); // load
		// the
		// column
		// names
		table[ndx++] = this.arrayToDelimitedString(this.getColumnTypes()); // load
		// the
		// column
		// types
		Iterator<String> dataRows = this.dataRows.iterator();
		while (dataRows.hasNext()) {
			table[ndx++] = dataRows.next();
		}
		return table;
	}
	
	public String[] getTable() {
		return this.getAllAsTable();
	}

	/**
	 * Convert a table as returned by StringArrayResultSet.getTable() to an
	 * array of arrays where the first array t[0] contains one string with the
	 * delimiter value t[1] contains an array of column names t[2] contains an
	 * array of column type names t[3...n] contains the data values
	 * 
	 * @param table
	 * @return
	 * @see StringArrayResultSet.getTable()
	 */
	public static String[][] convertTableToTwoDTable(String[] table) {
		String[][] twoDTable = new String[table.length][];
		int ndx = 0;
		String delimiter = table[ndx];
		twoDTable[ndx] = new String[1];
		twoDTable[ndx][0] = delimiter;
		ndx++;
		twoDTable[ndx] = StringArrayResultSet.delimitedStringToArray(delimiter,
				table[ndx]);
		ndx++;
		twoDTable[ndx] = StringArrayResultSet.delimitedStringToArray(delimiter,
				table[ndx]);
		ndx++;
		twoDTable[ndx] = StringArrayResultSet.delimitedStringToArray(delimiter,
				table[ndx]);
		ndx++;
		for (; ndx < table.length; ndx++) {
			twoDTable[ndx] = StringArrayResultSet.delimitedStringToArray(
					delimiter, table[ndx]);
		}
		return twoDTable;
	}
	/**
	 * Convert a table as returned by StringArrayResultSet.getTable() to an
	 * array of arrays where the first array t[0] contains one string with the
	 * delimiter value t[1] contains an array of column names t[2] contains an
	 * array of column type names t[3...n] contains the data values
	 * 
	 * @param table
	 * @return
	 * @see StringArrayResultSet.getTable()
	 */
	public String[][] getAllAs2DTable(
			String[] table) {

		return convertTableToTwoDTable(this.getAllAsTable());
	}

	public String[][] getData() {
		String[][] dataArray = new String[this.dataRows.size()][];
		Iterator<String> dataRows = this.dataRows.iterator();
		int ndx = 0;
		while (dataRows.hasNext()) {
			dataArray[ndx++] = delimitedStringToArray(this.delimiterString,dataRows.next());
		}
		return dataArray;
	}
	/**
	 * 
	 * @param delimiter
	 * @param delimitedString
	 * @return
	 */
	public static String[] delimitedStringToArray(String delimiter,
			String delimitedString) {
		return delimitedString.split(delimiter);
	}

	/**
	 * Array of strings to be converted put into one string with delimiters.
	 * 
	 * @param sa
	 * @return
	 */
	public String arrayToDelimitedString(String[] sa) {
		StringBuffer sb = new StringBuffer();
		for (int i = 0; i < sa.length; i++) {
			sb.append(sa[i]);
			sb.append(this.getDelimiter());
		}
		return sb.toString();
	}

	/**
	 * add the contents of a ResultSet to the collection. Return the number of
	 * rows added.
	 * 
	 * @param resultSet
	 * @return
	 * @throws SQLException
	 */
	public int addResultSet(ResultSet resultSet) throws SQLException {
		String[] oneRow = new String[this.columnNames.length];
		String value = null;
		int rowsAdded = 0;
		while (resultSet.next()) {
			for (int i = 0; i < this.columnNames.length; i++) {
				value = resultSet.getString(this.columnNames[i]);
				oneRow[i] = value;
				rowsAdded++;
			}
			this.addRow(oneRow);
		}
		return rowsAdded;
	}

	public String toString() {
		//  produce the delimiter
		StringBuffer sb = new StringBuffer("Delimiter: " + this.delimiterString);
		sb.append("\n");
		//  produce the column names with tabs between
		for (int i = 0; i < this.columnNames.length; i++) {
			sb.append(this.columnNames[i]);
			sb.append("\t");
		}
		sb.append("\n");
		//  produce the column types with tabs between
		for (int i = 0; i < this.columnTypes.length; i++) {
			sb.append(this.columnTypes[i]);
			sb.append("\t");
		}
		sb.append("\n");

		//  produce the data rows with tabs between each element in the rows
		for (int i = 0; i < this.dataRows.size(); i++) {
			String s = this.dataRows.elementAt(i);
			String[] data = StringArrayResultSet.delimitedStringToArray(this.delimiterString, s);
			for (int j = 0; j < data.length; j++) {
				sb.append(data[j]);
				sb.append("\t");
			}
			sb.append("\n");
		}
		sb.append("\n");
		return sb.toString();
	}

	public String[]  tabFormat() {
		StringArrayResultSet sars = this;
		String[] columnNames = sars.getColumnNames();
		String[] colTypeNames = sars.getColumnTypes();
		String[][] sarsData = sars.getData();
		
		StringBuffer sb = new StringBuffer();
		Vector<String> v = new Vector<String>();
		//  produce the column names with tabs between
		for (int i = 0; i < columnNames.length; i++) {
			sb = new StringBuffer();
			sb.append(columnNames[i]);
			sb.append("\t");
			v.add(sb.toString());
		}
		v.add("  ");
		//  produce the column types with tabs between
		for (int i = 0; i < colTypeNames.length; i++) {
			sb = new StringBuffer();
			sb.append(colTypeNames[i]);
			sb.append("\t");
			v.add(sb.toString());
		}
		v.add("  ");

		//  produce the data rows with tabs between each element in the rows
		for (int i = 0; i < sarsData.length; i++) {
			for (int j = 0; j < sarsData[i].length; j++) {
				sb.delete(0, sb.length()-1);
				sb.append(sarsData[i][j]);
				sb.append("\t");
				v.add(sb.toString());
			}
		}
		String[] sa = new String[v.size()];
		return v.toArray(sa);
	}
	public static void main(String[] args) {

		String localDelimiter = "|";
		String[] colNames = { "col1", "col2", "col3" };
		String[] typeNames = { "T1", "T2", "T3" };
		String[][] data = { { "v1a", "v1b", "v1c" }, { "v2a", "v2b", "v2c" },
				{ "v3a", "v3b", "v3c" }, { "v4a", "v4b", "v4c" } };

		StringArrayResultSet sars = new StringArrayResultSet(localDelimiter,
				colNames, typeNames);
		System.out.println("sars: " + sars);
		for (int i = 0; i < data.length; i++) {
			sars.addRow(data[i]);
		}

		String[] delimitedArray = sars.getAllAsTable();

		System.out.println("delimited array:");
		for (int i = 0; i < delimitedArray.length; i++) {
			System.out.println("row: " + i + " " + delimitedArray[i]);
			String s = delimitedArray[i];
			String[] tokens = null;
			try {
				tokens = s.split(delimitedArray[0]);
			} catch (PatternSyntaxException e) {
				// TODO Auto-generated catch block
				System.err.println("ex: description: " + e.getDescription()
						+ ", pattern: " + e.getPattern() + ", index: "
						+ e.getIndex());
			}
			System.out.println("tokens is length: " + tokens.length);
			for (int j = 0; j < tokens.length; j++) {
				System.out.print("  " + tokens[j]);
			}
			System.out.println();
		}
		
		System.out.println("\nStringArrayResultSet object toString()");
		System.out.println(sars);
		
		String[] allOfIt = sars.getAllAsTable();
		StringArrayResultSet sars2 = new StringArrayResultSet(allOfIt);
		
		System.out.println("\nStringArrayResultSet object toString()");
		System.out.println(sars);System.out.println("\nConvert to 2D array");

		String[][] twoDarray = StringArrayResultSet
				.convertTableToTwoDTable(delimitedArray);

		for (int i = 0; i < twoDarray.length; i++) {
			String[] oneRow = twoDarray[i];

			System.out.print("Row: " + i);
			for (int j = 0; j < oneRow.length; j++) {
				System.out.print("\t" + oneRow[j]);
			}
			System.out.println();
		}
		
		System.out.println("\nStringArrayResultSet data from little getters");
		String[] columnNames = sars.getColumnNames();
		String[] colTypeNames = sars.getColumnTypes();
		String[][] sarsData = sars.getData();
		
		System.out.println();
		//  produce the column names with tabs between
		for (int i = 0; i < columnNames.length; i++) {
			System.out.print(columnNames[i]);
			System.out.print("\t");
		}
		System.out.println();
		//  produce the column types with tabs between
		for (int i = 0; i < colTypeNames.length; i++) {
			System.out.print(colTypeNames[i]);
			System.out.print("\t");
		}
		System.out.println();

		//  produce the data rows with tabs between each element in the rows
		for (int i = 0; i < sarsData.length; i++) {
			for (int j = 0; j < sarsData[i].length; j++) {
				System.out.print(sarsData[i][j]);
				System.out.print("\t");
			}
			System.out.println();
		}
		System.out.println();
	}
}
