package edu.tamucc.hri.rdbms.utils;
/**
 * just a wrapper around a String for type safety
 * @author jvh
 *
 */
public class DefaultValue {

	public static final String NullString = "null@";
	private String rep = NullString;
	
	public DefaultValue() {
		
	}
	public DefaultValue(String val) {
		this.rep = val;
		if(this.rep == null)
			this.rep = NullString;
	}

	public String getValue() {
		return rep;
	}

	public void setValue(String defaultValue) {
		this.rep = defaultValue;
	}

	public static String getNullstring() {
		return NullString;
	}
	
	public String getPrettyStringValue() {
		String s = this.getValue();
		int start = s.indexOf('\'');
		start++;
		int end = s.indexOf('\'',start);
		String ts = s.substring(start,end);
		return ts;
	}

	@Override
	public String toString() {
		return rep;
	}
}
