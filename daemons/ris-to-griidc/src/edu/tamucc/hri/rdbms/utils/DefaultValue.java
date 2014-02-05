package edu.tamucc.hri.rdbms.utils;
/**
 * just a wrapper around a String for type safety
 * @author jvh
 *
 */
public class DefaultValue {

	public static final String NullString = "null@";
	private String rep = null;
	
	public DefaultValue(String val) {
		this.rep = val;
		if(this.rep == null)
			this.rep = NullString;
	}

	public String getDefaultValue() {
		return rep;
	}

	public void setDefaultValue(String defaultValue) {
		this.rep = defaultValue;
	}

	public static String getNullstring() {
		return NullString;
	}

	@Override
	public String toString() {
		return rep;
	}
}
